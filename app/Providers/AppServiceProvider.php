<?php
/** 
 * Created By:       Lin Xue, Cornell University, lx58@cornell.edu
 * Created Date:     December 2019
 * Purpose of App:   Laravel app to consume iModules email api v2 data and store them in database.
 * Notes:            Dependent on PHP, Laravel, GuzzleHttp, Supervisor
 * License:          Mozilla Public License 2.0
*/

namespace App\Providers;

use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;
use Mail;
use App\Mail\CompletedNotification;
use Exception;
use Log;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use App\Mail\ExceptionOccured;
use Illuminate\Support\Arr;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Queue::before(function (JobProcessing $event) {
            \Log::info('Job Start: ' . $event->job->resolveName());
            \Log::info('Job Id: ' . $event->job->getJobId());
            \Log::info('Job raw body: ' . $event->job->getRawBody());
        });

        Queue::after(function (JobProcessed $event) {
            // $event->connectionName
            // $event->job
            // $event->job->payload()
            //Send a notification when all is completed
            try {        
                $content = ['message' => "Your iModules email API request is finished!  Job Id: ".$event->job->getJobId() ];
                $requester_email = Arr::get($_SERVER, 'HTTP_NETID', 'lx58').'@cornell.edu';
                \Mail::to($requester_email)->send(new CompletedNotification( $content ));     
                \Log::notice('Job done: ' . $event->job->getJobId());
            } catch (\Exception $ex) {
                dd($ex);
            }
        });

        Queue::failing(function (JobFailed $event) {
            // $event->connectionName
            // $event->job
            // $event->exception
            // Send user notification of failure, etc...
            try {

                $e = FlattenException::create($event->exception);
        
                $handler = new SymfonyExceptionHandler();
        
                $html = $handler->getHtml($e);
                
                $requester_email = Arr::get($_SERVER, 'HTTP_NETID', 'lx58').'@cornell.edu';
                
                \Mail::to($requester_email)->send(new ExceptionOccured($html));

                \Log::error('Job failed: ' . $event->job->resolveName() . $event->job->getJobId() . '(' . $event->exception->getMessage() . ')');
            } catch (\Exception $ex) {
                dd($ex);
            }
        });
    }
}
