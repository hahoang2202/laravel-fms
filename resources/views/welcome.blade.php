<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Auctions</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        <link rel="stylesheet" href="{{asset('css/app.css')}}">
        <link rel="icon" href="{{ URL::to('/') }}/favicon.svg">
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    </head>   

    <body>
        
        <div id="app">
                
            <navbar></navbar>

            <router-view></router-view>

        </div>

    </body>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="{{ asset('/js/app.js') }}">
        
        import navbar from './components/navbar.vue';

    </script>

</html>
