<!DOCTYPE html>
<html lang="en">
    <!-- %VIEW: public/themes/default/layout/guest.blade.php -->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf_token" content="{!! csrf_token() !!}"/>
        <meta name="csrf-token" content="{!! csrf_token() !!}"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width, height=device-height, target-densitydpi=device-dpi" />
        <meta property="og:image" content="{{ url('setting/logo.jpg') }}" />
        <meta property="og:title" content="{{ Setting::get('site_title') }}" />
        <meta property="og:type" content="Social Network" />
        <meta name="keywords" content="{{ Setting::get('meta_keywords') }}">
        <meta name="description" content="{{ Setting::get('meta_description') }}">
        <link rel="icon" type="image/x-icon" href="{{asset('images/favicon.ico')}}">

        <title>{{ Setting::get('site_title') }}</title>
        
        <link href="{{ Theme::asset()->url('css/flag-icon.css') }}" rel="stylesheet">
        <link href="{{ Theme::asset()->url('css/custom.css') }}" rel="stylesheet">
        
        {{-- {!! Theme::asset()->styles() !!} --}}
        <link href="{{ asset('/themes/default/assets/css/style.0e3cfe20bb993fe353da4e0c3fa3b356.css') }}" rel="stylesheet">

        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
            <script src="https://js.stripe.com/v3/"></script>
        <![endif]-->
        <script type="text/javascript">
        function SP_source() {
          return "{{ url('/') }}/";
        }
        var base_url = "{{ url('/') }}/";
        var theme_url = "{!! Theme::asset()->url('') !!}";
        </script>
        {!! Theme::asset()->scripts() !!}
        @if(Setting::get('google_analytics') != NULL)
            {!! Setting::get('google_analytics') !!}
        @endif
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet"> 
    </head>
    <body @if(Setting::get('enable_rtl') == 'on') class="direction-rtl" @endif>
        <div class="">
            {!! Theme::partial('guest-header') !!}    
        </div>
        
        {!! Theme::content() !!}
        
        {!! Theme::partial('footer') !!}

        {!! Theme::asset()->container('footer')->scripts() !!}
    </body>
</html>