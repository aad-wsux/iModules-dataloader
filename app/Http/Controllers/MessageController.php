<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Jobs\ConsumeMessages;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{

     public function getMessages(Request $request)
     {
        // validate incoming request        
        $validator = Validator::make($request->all(), [
         'start_date' => 'required',
         'end_date' => 'required | after:start_date'
         ]);
         if ($validator->fails()) {
            return \Redirect::to('/')->withErrors($validator);
         }
         //Check if there is a queue job
         $count = \DB::table('iModules_jobs')->count();
         if($count != 0) {
            //return to form
            return \Redirect::to('/')->with('error', 'A job is currently running.  Please wait for it to finish before starting another request.');
         }
        // defer the processing of request        
        ConsumeMessages::dispatch($request->start, $request->end);

        return \Redirect::to('success');
     }

}
