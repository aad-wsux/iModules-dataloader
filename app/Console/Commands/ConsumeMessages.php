<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Http\Request;
use Illuminate\Support\Str;  
use App\Message;
use App\Link;
use App\Bounce;
use App\Recipient;
use App\Click;
use App\Mail\CompletedNotification;

class ConsumeMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consume:messages {start} {end}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will fetch data from iModules API, store them into our crvas database, then email the requester.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

    }
    protected function getToken()
        {
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
    
            //dd($token);
            return $token;
        }
        public function getLinks($messageId, $token)
        {
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
   
           $response = $response->getBody()->getContents();
           $data = json_decode($response);
           //dd($data);
           foreach ($data as $onelink)
           {
               $link = new Link();
               $link->id = $onelink->id;
               $link->url = Str::limit($onelink->url, 900);
               $link->name = $onelink->name;
               $link->message_id = $messageId;
               $link->save();
           }
       }
   
       public function getBounces($messageId, $token, $head)
        {
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
   
           $response = $response->getBody()->getContents();
           $data = json_decode($response)->{'data'};
           //dd($data);
           $total = json_decode($response)->{'total'};
           $startAt = json_decode($response)->{'startAt'};
           $remainer = $total - $startAt;
           foreach ($data as $onebounce)
           {
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
           if ($remainer > 1000) {
               $head = $startAt + 1000;
               $this->getBounces($messageId, $token, $head);
           }
           else{
               return;
           }
       }
   
       public function getRecipients($messageId, $token, $head)
        {
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
   
           $response = $response->getBody()->getContents();
           $data = json_decode($response)->{'data'};
           $total = json_decode($response)->{'total'};
           $startAt = json_decode($response)->{'startAt'};
           $remainer = $total - $startAt;
           foreach ($data as $onerecipient)
           {
               $recipient = new Recipient();
               $recipient->id = $onerecipient->id;
               $recipient->email_address = $onerecipient->emailAddress;
               $recipient->first_name = $onerecipient->firstName;
               $recipient->last_name = $onerecipient->lastName;
               $recipient->class_year = $onerecipient->classYear;
               $recipient->member_id = $onerecipient->memberId;
               $recipient->constituent_id = $onerecipient->constituentId;
               $recipient->date_added = $onerecipient->dateAdded;
               $recipient->last_updated = $onerecipient->lastUpdated;
               $recipient->message_id = $messageId;
               $recipient->save();
           }
           if ($remainer > 1000) {
               $head = $startAt + 1000;
               $this->getRecipients($messageId, $token, $head);
           }
           else{
               return;
           }
       }
   
       public function getClicks($messageId, $token, $head)
        {
            //Print out the number.
           // echo $head, '<br>';
           // echo $messageId, '<br>';
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
   
           $response = $response->getBody()->getContents();
           $data = json_decode($response)->{'data'};
           //dd($data);
           foreach ($data as $oneclick)
           {
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
           $total = json_decode($response)->{'total'};
           $startAt = json_decode($response)->{'startAt'};
           $remainer = $total - $startAt;
           //echo $remainer, '<br>';
           if ($remainer > 1000) {
               $head = $startAt + 1000;
               $this->getClicks($messageId, $token, $head);
           }
           else{
               return;
           }
       }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //Get Parameters
        $start = $this->arguments('start')['start'];
        $end = $this->arguments('end')['end'];
        $token = $this->getToken();
        //dd($token);
        //$request->all();
        //dd($request->start);
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://emapi.imodules.com/v2/messages', [
            'headers' => [
                'Authorization' => $token,
                'Accept'     => 'application/json',
            ],
            'query' => [
                'fromTimestamp' => $start,
                'toTimestamp' => $end,
                // 'fromTimestamp' => 1569090749000,
                // 'toTimestamp' => 1569267158000,
                //'toTimestamp' => 1571682749000,
                'startAt' => 0,
                'maxResults' => 1000,
            ]
        ]);

        $response = $response->getBody()->getContents();
        $data = json_decode($response)->{'data'};
        //dd($data);
        //Truncate all data tables
        \DB::table('imodules_messages')->truncate();
        \DB::table('imodules_links')->truncate();
        \DB::table('imodules_bounces')->truncate();
        \DB::table('imodules_recipients')->truncate();
        \DB::table('imodules_clicks')->truncate();

        foreach ($data as $email)
        {
            //print_r($email->emailName); 
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
            $this->getLinks($email->id, $token);
            $this->getBounces($email->id, $token, 0);
            $this->getRecipients($email->id, $token, 0);
            $this->getClicks($email->id, $token, 0);
        }
        //Send a notification when all is completed
        $data = ['message' => 'Your iModules email API request is finished!'];
        \Mail::to('lx58@cornell.edu')->send(new completedNotification( $data ));

    }
}
