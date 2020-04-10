# iModules email dataloader
This is a Laravel web application to consume [iModules email api v2](https://support.imodules.com/hc/en-us/articles/228929707-Email-Metric-API) data and store them in database.
While iModules Admin UI provide some statistical graphs and charts, it didn't provide us all the details we would like.  Nor can you choose any time range to export the report.
For example, although the admin panel displays the bounce rate, we would like to know the email addresses that are bounced and the reasons of the bounces.  We can remove the invalid emails from our mail list.  Another major advantage of the app is to match the recipients with the clicks which will shed insights to our future digital marketing efforts.  With the iModules email data loader app, one can download the entire database of a certain time range and using SQL, Tableau or other data analysis/visualization tool to generate custom report of marketing emails.


The index page (welcome.blade.php) takes start and end times and will fetch all email data within the time range from iModules email API v2.  We use Laravel queue and jobs because it takes a while to process one month's data if you have a large alumni mail list like us (e.g. it takes 22 hours to process data from 336 emails that were sent in a month time range).  The form submission triggers the job, when the queue is completed, a notification email will be sent to the requestor. We get the reuestor's email address via the environment varaibles. 

Enter the start and end date to submit a job. The epoch time input field is read only and is auto-filled when the start and end time is entered.
![The index page](https://raw.githubusercontent.com/aad-wsux/iModules-dataloader/images/index-screenshot.png)

If no other job is currently running, the user sees the success page right after form submission, indicating that the Laravel job is triggered.
![The success page](https://raw.githubusercontent.com/aad-wsux/iModules-dataloader/images/success-screenshot.png)

Otherwise, an alert message will prompt on the screen indicating the job failed to initiate because there is an existing job.  This is a safety mechanism to ensure that a new job will not be triggered until the previous request has been processed as each new job wipes the data tables clean.
![The alert message](https://raw.githubusercontent.com/aad-wsux/iModules-dataloader/images/alert-screenshot.png)


## Requirements
- PHP > 7.1
- HTTP server with PHP support (e.g.: Apache, Ngix, Caddy)
- [Laravel](https://laravel.com/docs/master)
- [Composer](https://getcomposer.org)
- [GuzzleHttp](http://docs.guzzlephp.org/en/stable/)
- [Supervisor](http://supervisord.org/) 
- A relatinoal database (e.g.: Oracle, MySQL)

## Getting Started
Clone the project repository by running the command below if you use SSH
```

git clone git@github.com:aad-wsux/iModules_dataloader.git

```

If you use https, use this instead
```

git clone https://github.com/aad-wsux/iModules_dataloader.git

```
cd into the project directory and run:
```

composer install

```
Duplicate .env.example and rename it .env

Then run:
```

php artisan key:generate

```

## Metrics available
- Bounce: A non-unique record of every bounce response generated from a recipient's mail server which includes the unique recipient id, type, reason, time stamp of bounce action, date added (times tamp of database write), and a unique id for the action.
- Click: A non-unique record of every click which includes the unique recipient id, time stamp of click action, date added (time stamp of database write), unique link id, ip address, user agent string, and unique click id.
- Link: A unique record of every hyperlink included within an email sent via Encompass which includes a unique id, name, and url. 
- Message: A record of an email sent through Encompass which includes subscription category, email name, from address, from name, pre-header, sent count, subject line, sub community id, scheduled date, actual send time, date added (time stamp of database write), and a unique message id. Recurring emails have unique message records for each send.
- Recipient: A record of a person with a bounce or deliver action for a given message - less any duplicates.

(Source: https://support.imodules.com/hc/en-us/articles/228929707-Email-Metric-API)

## Data tables

There are seven tables in total.  Two of which are for Laravel jobs, the other five contain iModules email data.  
We empty all five data tables at the beginning of each new job so that the tables only contain data related to the selected time range.  This is for the convenience of both data analysis and database maintenance.  The IMODULES_CLICKS and IMODULES_RECIPIENTS tables can grow big very quickly.

- IMODULES_JOBS: Laravel job that is running.
- IMODULES_FAILED_JOBS:  Failed laravel jobs.
- IMODULES_MESSAGES: contains all the email id, email, sent count, etc of all the emails within the search rate range.
- IMODULES_LINKS: all email links within the search rate range.
- IMODULES_BOUNCES:  all bounce data within the search rate range.
- IMODULES_CLICKS: all click data within the search rate range.
- IMODULES_RECIPIENTS: all click data within the search rate range. 

You can join all tables by email id (The field name is ID in IMODULES_MESSAGES table and MESSAGE_ID in all other tables).   
You can link recipients with bounce and click data by RECIPIENT_ID field (or ID field in the RECIPIENTS table).

## Database Migrations
Be sure to fill in your database details in your .env file before running the migrations:
```

php artisan migrate

```
Once the database is settup and migrations are up, run
```

php artisan serve

```
and visit http://localhost:8000/ to see the application in action.

## Database config
We used Oracle database, if you use MySql, don't forget to change the default database type in /config/database.php.
```

'default' => env('DB_CONNECTION', 'oracle')

```

## Dependencies needed for Oracle connection
### Oracle Instant Client and oci8
How to install OCI8 on Ubuntu 18.04 and PHP 7.2
From <https://gist.github.com/Yukibashiri/cebaeaccbe531665a5704b1b34a3498e> 

### Oracle Driver package
yajra/laravel-oic8 (https://github.com/yajra/laravel-oci8)
```

composer require yajra/laravel-oci8

```
Once Composer has installed or updated your packages you need to register Laravel-OCI8. Open up config/app.php and find the providers key and add:
```

Yajra\Oci8\Oci8ServiceProvider::class,

        /*
         * Package Service Providers...
         */
        Yajra\Oci8\Oci8ServiceProvider::class,
        Yajra\Datatables\DatatablesServiceProvider::class

```
## Laravel Queues
The web form takes in a request and dispatch a queued job to keep running on the server and send user a notification when the job is finished.

We used database tables (iMoudles_jobs, iModules_failed_jobs) to handle the queue.
If you wish to use other options such as "beanstalkd", "sqs", "redis", change the queue config in /config/queue.php.
```

'default' => env('QUEUE_CONNECTION', 'database'),

```

### Laravel form validation
The application can only process one job at a time.  Since the data tables grow big very quickly, at the beginning of each job, the database tables will be emptied.
We use Laravel form validation to prevent any new job being initiated while a job is currently in the queue.
The validation code is in /app/Http/Controllers/MessageController.php 

### Queue finished or failed notification and logging
Queue start and end events are logged.
An email notification will be sent to the requester when a job is finished or failed.
The code is in app/Providers/AppServiceProvider.php
Two email templates are in app/Mail.

### Timeout
It takes about 24 hours to fetch a month worth of iModules email data using this application. 
We set our app to run maximum of 96 hours.
You may adjust the timeout in /app/Jobs/ConsumeMessage.php.
```

public $timeout = 345600;  //96 hours

```

## Supervisor
### Install and config
here is how to install and config supervisord on centos 7 to run Laravel queues permanently:

1. 
```
easy_install supervisor

```
2. 
```

yum install supervisor

```
3. vim /etc/supervisord.conf edit section program as following:

```

[program:laravel-worker]
command=php /path/to/app.com/artisan queue:work 
process_name=%(program_name)s_%(process_num)02d
numprocs=8 
priority=999 
autostart=true
autorestart=true  
startsecs=1
startretries=3
user=apache
redirect_stderr=true
stdout_logfile=/path/to/log/worker.log

```
4. to autorun at start.
```

systemctl enable supervisord

```
5. to restart the service.
```

systemctl restart supervisord

```
6. to check worker status.
```

supervisorctl status

```
7. to stop all workers.
```

supervisorctl stop all

```

## Restart queue worker
If queue job with database drivers doesn't populate table jobs, you need to restart queue worker on the server.
Since queue workers are long-lived processes, they will not pick up changes to your code without being restarted. So, the simplest way to deploy an application using queue workers is to restart the workers during your deployment process. You may gracefully restart all of the workers by issuing the queue:restart command:
```

php artisan queue:restart

```

## License
Mozilla Public License 2.0 (MPL-2.0)
https://opensource.org/licenses/MPL-2.0
