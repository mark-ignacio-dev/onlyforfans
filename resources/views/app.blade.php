<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- %MARK 20210504.a -->

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <!-- %NOTE: this is the *generated* CSS file -->
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">

    <link rel="prefetch" as="image" href="/images/logos/allfans-logo-154x33.png" />

    <!-- Static Data -->
    <script>
        const myUserId = '{{ Auth::user()->id }}';
    </script>
    <script>
        const paymentsDisabled = {{ Config::get('transactions.disableAll', 0) }};
    </script>

    {{-- Routing --}}
    @routes()

</head>
<body>
    <div id="app">
        <main-navbar></main-navbar>
        <div class="container-fluid pt-3">
            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    @include('vendorjs')
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
