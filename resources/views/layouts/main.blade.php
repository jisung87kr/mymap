<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
{{--    <link rel="stylesheet" href="{{asset('css/app.css')}}">--}}
{{--    <link rel="stylesheet" href="{{asset('css/font-awesome.min.css')}}">--}}
    <link rel="stylesheet" href="css/app.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous">
{{--    <script type="text/javascript" src="{{asset('js/app.js')}}"></script>--}}
    <script type="text/javascript" src="js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-U1DAWAznBHeqEIlVSCgzq+c9gqGAJn5c/t99JyeKa9xxaYpSvHU5awsuZVVFIhvj" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        @yield('content')
        <footer>
            <hr>
            <div class="text-center py-3 mb-3">
                <a href="mailto:ujsstudio87@gmail.com">ujsstudio87@gmail.com</a>
            </div>
        </footer>
    </div>
</body>
</html>