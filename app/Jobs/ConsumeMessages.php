<?php
/** 
 * Created By:       Lin Xue, Cornell University, lx58@cornell.edu
 * Created Date:     December 2019
 * Purpose of App:   Laravel app to consume iModules email api v2 data and store them in database.
 * Notes:            Dependent on PHP, Laravel, GuzzleHttp, Supervisor
 * License:          Mozilla Public License 2.0
*/
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Http\Request;
use Illuminate\Support\Str;  
use App\Message;
use App\Link;
use App\Bounce;
use App\Recipient;
use App\Click;

use Mail;
use App\Mail\CompletedNotification;

use Exception;
use Log;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use App\Mail\ExceptionOccured;

class ConsumeMessages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 345600;  //96 hours
    public $tries = 1;
    protected $start;
    protected $end;
    protected $token;
    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }
        protected function getToken()
        {
            try{
                $client = new \GuzzleHttp\Client();
                $response = $client->request('POST', 'https://cornelluniversity.imodules.com/apiservices/authenticate', [
                    'form_params' => [
                        'client_id' => env('client_id', ''),
                        'client_secret' => env('client_secret', ''),
                        'grant_type' => 'email_api_auth_key',
                        ]
                ]);
                $response = $response->getBody()->getContents();
                $data = json_decode($response, true);
                $token = 'Bearer '.$data['access_token'];
                return $token;
            }
            catch(\Exception $e) {
                $this->failed($e);
            }
        }
        public function getLinks($messageId, $token)
        {
            gc_collect_cycles(); // Call garbage collector to free up memory
            try{
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', 'https://emapi.imodules.com/v2/messages/'. $messageId .'/links', [
                    'headers' => [
                        'Authorization' => $token,
                        'Accept'     => 'application/json',
                    ],
                    'query' => [
                        'startAt' => 0,
                        'maxResults' => 1000,
                    ]
                ]);
                if ($response->getStatusCode() === 200) {    
                    $response = $response->getBody()->getContents();
                    $data = json_decode($response);
                    //dd($data);
                    foreach ($data as $onelink)
                    {
                        try{
                            $link = new Link();
                            $link->id = $onelink->id;
                            $link->url = Str::limit($onelink->url, 900);
                            $link->name = $onelink->name;
                            $link->message_id = $messageId;
                            $link->save();
                        }
                        catch(\Exception $e) {
                            $this->failed($e);
                            continue;
                        }
                    } // end of foreach
                } // end of if 200
                else{
                    \Log::error("getLinks Response: ".$response->getStatusCode());
                }
            }
            catch(\Exception $e) {
                $this->failed($e);
            }
       }
   
       public function getBounces($messageId, $token)
        {
            gc_collect_cycles(); // Call garbage collector to free up memory
            try{
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', 'https://emapi.imodules.com/v2/messages/'. $messageId .'/bounces', [
                    'headers' => [
                        'Authorization' => $token,
                        'Accept'     => 'application/json',
                    ],
                    'query' => [
                        'startAt' => 0,
                        'maxResults' => 1000,
                    ]
                ]);
                if ($response->getStatusCode() === 200) {  
                    $response = $response->getBody()->getContents();
                    $data = json_decode($response)->{'data'};
                    $total = json_decode($response)->{'total'};
                    //dd($data);
                    if (isset($total)) {
                            foreach ($data as $onebounce)
                            {   
                                try{
                                    $bounce = new Bounce();
                                    $bounce->id = $onebounce->id;
                                    $bounce->type = $onebounce->type;
                                    $bounce->reason = Str::limit($onebounce->reason, 1100);
                                    $bounce->recipient_id = $onebounce->recipientId;
                                    $bounce->timestamp = $onebounce->timestamp;
                                    $bounce->date_added = $onebounce->dateAdded;
                                    $bounce->message_id = $messageId;
                                    $bounce->save();
                                }
                                catch(\Exception $e) {
                                    $this->failed($e);
                                    continue;
                                }
                            } // end of foreach
                        }
                } // end of if 200
                else{
                    \Log::error("getBounces Response: ".$response->getStatusCode());
                }
            }
            catch(\Exception $e) {
                $this->failed($e);
            }
       }
   
       public function getRecipients($messageId, $token, $head)
        {
            gc_collect_cycles(); // Call garbage collector to free up memory
            \Log::info("getRecipients memory usage: ".memory_get_usage()); 
            try{
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', 'https://emapi.imodules.com/v2/messages/'. $messageId .'/recipients', [
                    'headers' => [
                        'Authorization' => $token,
                        'Accept'     => 'application/json',
                    ],
                    'query' => [
                        'startAt' => $head,
                        'maxResults' => 1000,
                    ]
                ]);

                if ($response->getStatusCode() === 200) { 
                    $response = $response->getBody()->getContents();
                    $data = json_decode($response)->{'data'};
                    $total = json_decode($response)->{'total'};
                    $startAt = json_decode($response)->{'startAt'};
                    foreach ($data as $onerecipient)
                    {
                            if(!empty($onerecipient->emailAddress)){
                                try{
                                    $recipient = Recipient::firstOrCreate(
                                        ['id'=> $onerecipient->id ],
                                        ['email_address' => $onerecipient->emailAddress,
                                        'first_name' => $onerecipient->firstName,
                                        'last_name' => $onerecipient->lastName,
                                        'class_year' => $onerecipient->classYear,
                                        'member_id' => $onerecipient->memberId,
                                        'constituent_id' => $onerecipient->constituentId,
                                        'date_added' => $onerecipient->dateAdded,
                                        'last_updated' => $onerecipient->lastUpdated,
                                        'message_id' => $messageId
                                        ]
                                    );  //Find a record with primary key id. If you can't find one, create a new entry
                                }
                                catch(\Exception $e) {
                                    $this->failed($e);
                                    continue;
                                }
                            }
                            else{
                                \Log::error("getRecipients empty email: ".$onerecipient->emailAddress);
                            }
                        } //end of foreach 
                        \Log::info("getRecipients total: ".$total);   
                        \Log::info("getRecipients startAt: ".$startAt);     
                    // Using while loop rather than recursive because Recipients table is too big.  When sent count is over 170,000, recursive function max out the 256M memory.           
                    while (!empty($total) && ($total - $startAt) > 1000) {
                        $head = $startAt + 1000;
                        gc_collect_cycles(); // Call garbage collector to free up memory
                        \Log::info("getRecipients memory usage: ".memory_get_usage()); 
                        try{
                            $client = new \GuzzleHttp\Client();
                            $response = $client->request('GET', 'https://emapi.imodules.com/v2/messages/'. $messageId .'/recipients', [
                                'headers' => [
                                    'Authorization' => $token,
                                    'Accept'     => 'application/json',
                                ],
                                'query' => [
                                    'startAt' => $head,
                                    'maxResults' => 1000,
                                ]
                            ]);
                            if ($response->getStatusCode() === 200) { 
                                $response = $response->getBody()->getContents();
                                $data = json_decode($response)->{'data'};
                                $startAt = json_decode($response)->{'startAt'};
                                \Log::info("getRecipients startAt: ".$startAt);  
                                foreach ($data as $onerecipient)
                                {
                                        if(!empty($onerecipient->emailAddress)){
                                            try{
                                                $recipient = Recipient::firstOrCreate(
                                                    ['id'=> $onerecipient->id ],
                                                    ['email_address' => $onerecipient->emailAddress,
                                                    'first_name' => $onerecipient->firstName,
                                                    'last_name' => $onerecipient->lastName,
                                                    'class_year' => $onerecipient->classYear,
                                                    'member_id' => $onerecipient->memberId,
                                                    'constituent_id' => $onerecipient->constituentId,
                                                    'date_added' => $onerecipient->dateAdded,
                                                    'last_updated' => $onerecipient->lastUpdated,
                                                    'message_id' => $messageId
                                                    ]
                                                );  //Find a record with primary key id. If you can't find one, create a new entry
                                            }
                                            catch(\Exception $e) {
                                                $this->failed($e);
                                                continue;
                                            }
                                        }
                                        else{
                                            \Log::error("getRecipients empty email: ".$onerecipient->emailAddress);
                                        }
                                    } //end of foreach 
                                } // end of if inside try
                                else{
                                    \Log::error("getRecipients Response: ".$response->getStatusCode());
                                }
                            } // end of try
                            catch(\Exception $e) {
                                $this->failed($e);
                            }
                        } // end of while
                    } // end of if 200
                else{
                    \Log::error("getRecipients Response: ".$response->getStatusCode());
                }
            }
            catch(\Exception $e) {
                $this->failed($e);
            }
       }

       //Recursive function, could be rewritten to while loop like the getRecipients() if needed. 
       public function getClicks($messageId, $token, $head)
        {
            gc_collect_cycles(); // Call garbage collector to free up memory
            \Log::info("getClicks memory usage: ".memory_get_usage()); 
            try{
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', 'https://emapi.imodules.com/v2/messages/'. $messageId .'/clicks', [
                    'headers' => [
                        'Authorization' => $token,
                        'Accept'     => 'application/json',
                    ],
                    'query' => [
                        'startAt' => $head,
                        'maxResults' => 1000,
                    ]
                ]);
                if ($response->getStatusCode() === 200) { 
                    $response = $response->getBody()->getContents();
                    $data = json_decode($response)->{'data'};
                    foreach ($data as $oneclick)
                    {   try{
                                $click = new click();
                                $click->id = $oneclick->id;
                                $click->recipient_id = $oneclick->recipientId;
                                $click->user_agent = Str::limit($oneclick->userAgent, 450);
                                $click->ip_address = $oneclick->ipAddress;
                                $click->link_id = $oneclick->linkId;
                                $click->timestamp = $oneclick->timestamp;
                                $click->date_added = $oneclick->dateAdded;
                                $click->message_id = $messageId;
                                $click->save();
                            }
                            catch(\Exception $e) {
                                $this->failed($e);
                                continue;
                            }
                    }
                    $total = json_decode($response)->{'total'};
                    $startAt = json_decode($response)->{'startAt'};     
                    \Log::info("getClicks total: ".$total);   
                    \Log::info("getClicks startAt: ".$startAt);         
                    if (!empty($total) && ($total - $startAt) > 1000) {
                        $head = $startAt + 1000;
                        $this->getClicks($messageId, $token, $head);  
                    }
                    else{
                        \Log::info("getClicks finished for message ".$messageId);  
                        return;
                    }
                } // end of if 200
                else{
                    \Log::error("getClicks Response: ".$response->getStatusCode());
                }
                
            }
            catch(\Exception $e) {
                $this->failed($e);
            }
       }


       public function failed(Exception $e)
        {
            \Log::error("Catch exception: ". $e);
        }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \DB::connection()->disableQueryLog();   //disable querylog to prevent memory leak
        // ini_set('memory_limit','256M');      //increase runtime memory to 256M
        $start = $this->start;
        $end = $this->end;
        //Truncate all data tables
        \DB::table('imodules_messages')->delete();
        \DB::table('imodules_links')->delete();
        \DB::table('imodules_bounces')->delete();
        \DB::table('imodules_recipients')->delete();
        \DB::table('imodules_clicks')->delete();      
        $token = $this->getToken();
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://emapi.imodules.com/v2/messages', [
            'headers' => [
                'Authorization' => $token,
                'Accept'     => 'application/json',
            ],
            'query' => [
                'fromTimestamp' => $start,
                'toTimestamp' => $end,
                'startAt' => 0,
                'maxResults' => 1000,
            ]
        ]);
        if ($response->getStatusCode() === 200) { 
            $response = $response->getBody()->getContents();
            $data = json_decode($response)->{'data'};
            //dd($data);
            foreach ($data as $email)
            {
                try{
                    //print_r($email->emailName); 
                    \Log::info("Email id: ".$email->id); 
                    \Log::info("Email name: ".$email->emailName);  
                    if (!empty($email->emailName) && !empty($email->subjectLine)) {
                        $message = new Message();
                        $message->id = $email->id;
                        $message->subcommunity_id = $email->subCommunityId;
                        $message->email_name = $email->emailName;
                        $message->from_name = $email->fromName;
                        $message->from_address = $email->fromAddress;
                        $message->subject_line = Str::limit($email->subjectLine,250);
                        $message->pre_header = Str::limit($email->preHeader,250);
                        $message->category_name = $email->categoryName;
                        $message->sent_count = $email->sentCount;
                        $message->scheduled_date_timestamp = $email->scheduledDateTimestamp;
                        $message->actual_send_timestamp = $email->actualSendTimestamp;
                        $message->date_added = $email->dateAdded;
                        $message->save();
                        try {
                            $token = $this->getToken(); // token expires in 24 hours, hense refreshing it before each time-consuming job.
                            if (!empty($token)) {
                                $this->getLinks($email->id, $token);
                                $this->getBounces($email->id, $token);
                                $this->getRecipients($email->id, $token, 0);
                                $this->getClicks($email->id, $token, 0);
                            }
                        }
                        catch(\Exception $e) {
                            $this->failed($e);
                            continue;
                        }
                    }
                }
                catch(\Exception $e) {
                    $this->failed($e);
                    continue;
                }
            } // end of foreach
        } // end of if 200  
        else {
            // Fail without being attempted any further
            throw new Exception (' Fail to get a valid response from iModules API.');
        }
    }
}

