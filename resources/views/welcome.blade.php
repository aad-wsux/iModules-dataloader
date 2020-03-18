<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>iModules email API</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }
            .content {
                text-align: center;
            }

            .title {
                font-size: 60px;
            }
            .btn {
                margin:20px;
                cursor: pointer;
                display: inline-block;
                font-weight: 400;
                text-align: center;
                white-space: nowrap;
                vertical-align: middle;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                border: 1px solid transparent;
                padding: .375rem .75rem;
                font-size: 1rem;
                line-height: 1.5;
                border-radius: .25rem;
                transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
            }
            .btn-primary {
                color: #fff;
                background-color: #007bff;
                border-color: #007bff;
            }
            input{
                margin-right:20px;
            }
            .alert {
                position: relative;
                padding: .75rem 1.25rem;
                margin-bottom: 1rem;
                border: 1px solid transparent;
                border-radius: .25rem;
            }
            .alert-danger {
                color: #721c24;
                background-color: #f8d7da;
                border-color: #f5c6cb;
            }

        </style>
        <!-- jquery-ui -->
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
            <!-- scripts -->
            <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
            <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="title">
                    Request iModules email data
                </div>

                <div >
                    <form method="POST" action="/consume-email-api/messages">
                    {{ csrf_field() }}
                    <p>Start Date: <input type="text" class="date" id="start_date" name="start_date" required> Epoch: <input type="text"  id="start" name="start" readonly></p>
                    <p>End Date:   <input type="text" class="date" id="end_date" name="end_date"  required> Epoch: <input type="text" id="end" name="end" readonly></p>
                        <button class="btn btn-primary" type="submit">Get iModules email data</button>
                    </div>
                    @if(count($errors))
                        <div class="form-group">
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{$error}}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                    </form>
                    @if(Session::has('error'))
                    <div class="alert alert-danger">
                        {!! session()->get('error') !!} 
                    </div>
                    @endif
                </div> 
            </div>
        </div>
<script>
    // Date picker
    $( function() {
        $( "#start_date" ).datepicker({
            altField:"#start",
            altFormat:"@"
        });
        $( "#end_date" ).datepicker({
            altField:"#end",
            altFormat:"@"
        });

    } );
</script>
    </body>
</html>
