<style>
    @media screen and (max-width: 1024px) {
        .navbar-mobile .dropdown.message .dropdown-menu {
            left: 35%;
            position: absolute;
            transform: translateX(-50%);
            min-width: 320px;
        }

        .navbar-mobile .notification-list > li > a .count {
            top: 0px;
            right: -15px;
        }
    }
    
    .mobile-search {
        display: none;
    }
    
    @media screen and (max-width: 1024px) {
        .mobile-search {
            position: absolute;
            top: 50px;
            left: 50%;
            display: none;
            transform: translateX(-50%);
            border-radius: 22px;
            padding: 0 5px;
            background: #fff;
            box-shadow: 8px 6px 15px rgba(0, 0, 0, .5);
            transition: 0.3s;
            max-width: 100%;
        }

        .mobile-search .form-left .selectize-control {
            max-width: 300px;
        }

        .mobile-search .form-left .selectize-dropdown {
            min-width: 300px;
        }

        .mobile-search .form-left .selectize-input {
            max-width: 100%;
            width: calc(100% - 10px);
        }

        .mobile-search .form-left .selectize-dropdown-content {
            max-width: 100% !important;
        }

        .mobile-search form {
            margin: 0;
            padding: 10px;
        }
    }
    
    @media screen and (max-width: 576px) {
        .user-image.fans {
            padding-right: 5px !important;
            padding-left: 0;
        }

        .notification-list > li {
            padding-left: 8px;
        }

        .notification-list > li:first-child {
            padding-left: 22px;
        }

        .dropdown.message {
            padding-right: 15px;
        }
    }
</style>
<!-- %VIEW themes/default/partials/header -->
@if(Auth::guest())
    <nav class="navbar fans navbar-default no-bg guest-nav">
        <div class="container">
@else
@php
  $username = Auth::user()->username;
@endphp
                <nav class="navbar fans navbar-default no-bg" id="navbar-right" v-cloak>
                    <div class="container-fluid">
                        @endif
                        <div class="navbar-header">
                            <div class="navbar-toggle navbar-mobile">
                                <ul class="list-inline notification-list">
                                    <li class="" style="display: inline-block;">
                                        <a href="{{ url('/') }}"><svg viewBox="0 0 16 16" class="bi bi-house-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em">
                                                <path fill-rule="evenodd" d="M8 3.293l6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293l6-6zm5-.793V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z"></path>
                                                <path fill-rule="evenodd" d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z"></path>
                                            </svg><span class="small-screen"></span></a>
                                    </li>
                                    <li class="" style="display: inline-block;">
                                        <a href="{{ route('explore-posts') }}"><svg viewBox="0 0 16 16" class="bi bi-person-bounding-box" fill="currentColor" xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em">
                                                <path fill-rule="evenodd" d="M1.5 1a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-1 0v-3A1.5 1.5 0 0 1 1.5 0h3a.5.5 0 0 1 0 1h-3zM11 .5a.5.5 0 0 1 .5-.5h3A1.5 1.5 0 0 1 16 1.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 1-.5-.5zM.5 11a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 1 0 1h-3A1.5 1.5 0 0 1 0 14.5v-3a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a.5.5 0 0 1 0-1h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 1 .5-.5z"></path>
                                                <path fill-rule="evenodd" d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"></path>
                                            </svg><span class="small-screen"></span></a>
                                    </li>
                                    <li class="" style="display: inline-block;">
                                        <a href="{{ url('/mylists') }}"><svg viewBox="0 0 16 16" class="bi bi-person-lines-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em">
                                              <path fill-rule="evenodd" d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm7 1.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5zm0-3a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5zm2 9a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
                                            </svg><span class="small-screen"></span></a></a>
                                    </li>
                                    <li class="dropdown message notification" style="display: inline-block;">
                                        <a href="#" data-toggle="dropdown" @click.prevent="showNotifications" class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
                                            <svg viewBox="0 0 16 16" class="bi bi-app-indicator" fill="currentColor" xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em">
                                                <path fill-rule="evenodd" d="M5.5 2A3.5 3.5 0 0 0 2 5.5v5A3.5 3.5 0 0 0 5.5 14h5a3.5 3.5 0 0 0 3.5-3.5V8a.5.5 0 0 1 1 0v2.5a4.5 4.5 0 0 1-4.5 4.5h-5A4.5 4.5 0 0 1 1 10.5v-5A4.5 4.5 0 0 1 5.5 1H8a.5.5 0 0 1 0 1H5.5z"></path>
                                                <path d="M16 3a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"></path>
                                            </svg>
                                            @if(Auth::user()->notifications()->where('seen',0)->count() > 0)
                                                <span class="count hidden">{{ Auth::user()->notifications()->where('seen',0)->count() }}</span>
                                                <span class="count" v-if="unreadNotifications > 0" >@{{ unreadNotifications }}</span>
                                            @endif
                                        </a>
                                        <div class="dropdown-menu">
                                            <div class="dropdown-menu-header">
                                                <span class="side-left">{{ trans('common.notifications') }}</span>
                                                <a v-if="unreadNotifications > 0" class="side-right" href="#" @click.prevent="markNotificationsRead" >{{ trans('messages.mark_all_read') }}</a>
                                                <div class="clearfix"></div>
                                            </div>
                                            @if(Auth::user()->notifications()->count() > 0)
                                                <ul class="list-unstyled dropdown-messages-list scrollable" data-type="notifications">
                                                    <li class="inbox-message"  v-bind:class="[ !notification.seen ? 'active' : '' ]" v-for="notification in notifications.data">
                                                        <a href="{{ url(Auth::user()->username.'/notification/') }}/@{{ notification.id }}">
                                                            <div class="media">
                                                                <div class="media-left">
                                                                    <img class="media-object img-icon" v-bind:src="notification.notified_from.avatar.filepath" alt="images">
                                                                </div>
                                                                <div class="media-body">
                                                                    <h4 class="media-heading">
                                                                        <span class="notification-text"> @{{ notification.description }} </span>
                                                                        <span class="message-time">
                        															<span class="notification-type"><i class="fa fa-user" aria-hidden="true"></i></span>
                        															<time class="timeago" datetime="@{{ notification.created_at }}+00:00" title="@{{ notification.created_at }}">
                        																@{{ notification.created_at }}
                        															</time>
                        														</span>
                                                                    </h4>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                    <li v-if="notificationsLoading" class="dropdown-loading">
                                                        <i class="fa fa-spin fa-spinner"></i>
                                                    </li>
                                                </ul>
                                            @else
                                                <div class="no-messages">
                                                    <i class="fa fa-bell-slash-o" aria-hidden="true"></i>
                                                    <p>{{ trans('messages.no_notifications') }}</p>
                                                </div>
                                            @endif
                                            <div class="dropdown-menu-footer"><br>
                                                <a href="{{ url('allnotifications') }}">{{ trans('common.see_all') }}</a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dropdown message" style="display: inline-block;">
                                        <a href="#" data-toggle="dropdown" @click="showConversations" class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
                                            <svg viewBox="0 0 16 16" class="bi bi-chat-dots-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em">
                                                <path fill-rule="evenodd" d="M16 8c0 3.866-3.582 7-8 7a9.06 9.06 0 0 1-2.347-.306c-.584.296-1.925.864-4.181 1.234-.2.032-.352-.176-.273-.362.354-.836.674-1.95.77-2.966C.744 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7zM5 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"></path>
                                            </svg>
                                            <span class="count" v-if="unreadConversations" >@{{ unreadConversations }}</span>
                                        </a>
                                        <div class="dropdown-menu">
                                            <div class="dropdown-menu-header">
                                                <span class="side-left">{{ trans('common.messages') }}</span>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div class="no-messages hidden">
                                                <i class="fa fa-commenting-o" aria-hidden="true"></i>
                                                <p>{{ trans('messages.no_messages') }}</p>
                                            </div>
                                            <ul class="list-unstyled dropdown-messages-list scrollable" data-type="messages">
                                                <li class="inbox-message" v-for="conversation in conversations.data">
                                                    <a href="#" onclick="chatBoxes.sendMessage(@{{ conversation.user.id }})">
                                                        <div class="media">
                                                            <div class="media-left">
                                                                <img class="media-object img-icon" v-bind:src="conversation.user.avatar.filepath" alt="images">
                                                            </div>
                                                            <div class="media-body">
                                                                <h4 class="media-heading">
                                                                    <span class="message-heading">@{{ conversation.user.name }}</span>
                                                                    <span class="online-status hidden"></span>
                                                                    <time class="timeago message-time" datetime="@{{ conversation.lastMessage.created_at }}" title="@{{ conversation.lastMessage.created_at }}">
                                                                        @{{ conversation.lastMessage.created_at }}
                                                                </h4>
                                                                <p class="message-text">
                                                                    @{{ conversation.lastMessage.body }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li v-if="conversationsLoading" class="dropdown-loading">
                                                    <i class="fa fa-spin fa-spinner"></i>
                                                </li>
                                            </ul>
                                            <div class="dropdown-menu-footer">
                                                <a href="{{ url('messages') }}">{{ trans('common.see_all') }}</a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="" style="display: inline-block;">
                                        <a href="#" class="search-btn">
                                            <svg width="1.5em" height="1.5em" viewBox="0 0 16 16" class="bi bi-search" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M10.442 10.442a1 1 0 0 1 1.415 0l3.85 3.85a1 1 0 0 1-1.414 1.415l-3.85-3.85a1 1 0 0 1 0-1.415z"/>
                                                <path fill-rule="evenodd" d="M6.5 12a5.5 5.5 0 1 0 0-11 5.5 5.5 0 0 0 0 11zM13 6.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z"/>
                                            </svg>
                                            <span class="small-screen"></span></a>
                                        <div class="mobile-search">
                                            <form class="navbar-form navbar-left form-left" role="search">
                                                <div class="input-group no-margin">
                                					<span class="input-group-btn">
                                						<button class="btn btn-default" type="button"><i style="color:#848f96;" class="fa fa-search"></i></button>
                                					</span>
                                                    <input type="text" id="mobile-navbar-search" data-url="{{ URL::to('api/v1/search') }}" class="form-control" placeholder="{{ trans('messages.search_placeholder') }}">
                                                </div>
                                            </form>
                                        </div>
                                    </li>
                                    <li class="dropdown user-image fans MARK-A" style="display: inline-block;">
                                        <a href="{{ url(Auth::user()->username) }}" class="dropdown-toggle no-padding" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                          <img src="{{ Auth::user()->avatar->filepath }}" alt="{{ Auth::user()->name }}" class="img-radius img-30" title="{{ Auth::user()->name }}">
                                        </a>
                                        <ul class="dropdown-menu">
                                            @if(Auth::user()->hasRole('admin'))
                                                <li class="{{ Request::segment(1) == 'admin' ? 'active' : '' }}"><a href="{{ url('admin') }}"><i class="fa fa-user-secret" aria-hidden="true"></i>{{ trans('common.admin') }}</a></li>
                                            @endif
                                            <li class="{{ (Request::segment(1) == Auth::user()->username && Request::segment(2) == '') ? 'active' : '' }}"><a href="{{ url(Auth::user()->username) }}"><i class="fa fa-user" aria-hidden="true"></i>{{ trans('common.my_profile') }}</a></li>
                                            <li class="{{ Request::segment(3) == 'general' ? 'active' : '' }}"><a href="{{ url('/'.Auth::user()->username.'/settings/general') }}"><i class="fa fa-cog" aria-hidden="true"></i>{{ trans('common.settings') }}</a></li>
                                            <li class=""><a href="{{ url('/mylists') }}"><i class="fa fa-list" aria-hidden="true"></i>{{ trans('common.lists') }}</a></li>
                                            <li class=""><a href="{{ url('/'.Auth::user()->username.'/saved') }}"><i class="fa fa-bookmark" aria-hidden="true"></i>{{ trans('common.saved_post') }}</a></li>
                                            <li class=""><a href="{{ url(Auth::user()->username.'/settings/addbank') }}"><i class="fa fa-university" aria-hidden="true"></i>{{ trans('common.add_bank') }}</a></li>
                                            <li class=""><a href="{{ url(Auth::user()->username.'/settings/earnings') }}"><i class="fa fa-dollar" aria-hidden="true"></i>{{ trans('common.earnings') }}</a></li>
                                            <li class=""><a href="{{ url(Auth::user()->username.'/settings/addpayment') }}"><i class="fa fa-credit-card" aria-hidden="true"></i>{{ trans('common.add_payment') }}</a></li>
                                            <li class=""><a href="{{ route('vault.dashboard', $username) }}"><i class="fa fa-lock" aria-hidden="true"></i>Vault</a></li>
                                            @if (Auth::user()->is_bank_set)
                                                <li class=""><a href="{{ url(Auth::user()->payment->dashboard_url) }}"><i class="fa fa-credit-card" aria-hidden="true"></i>{{ trans('common.dashboard') }}</a></li>
                                            @endif
                                            <li class=""><a href="{{ url('/'.Auth::user()->username.'/settings/affliates') }}"><i class="fa fa-retweet" aria-hidden="true"></i>{{ trans('common.referrals') }}</a></li>
                                            <li class=""><a href="{{ url('/faq') }}"><i class="fa fa-question" aria-hidden="true"></i>{{ trans('common.help_faq') }}</a></li>
                                            <li class=""><a href="{{ url('/support') }}"><i class="fa fa-envelope" aria-hidden="true"></i>{{ trans('common.support') }}</a></li>
                                            <li>
                                                <form action="{{ url('/logout') }}" method="post">
                                                    {!! csrf_field() !!}
                                                    <button type="submit" class="btn-logout" style="margin-bottom: 0px;"><i class="fa fa-sign-out" aria-hidden="true"></i>{{ trans('common.logout') }}</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                            <a class="navbar-brand fans" href="{{ url('/') }}" style="padding-top:20px;">
                                {{ Setting::get('site_title') }}
                                {{--<img class="fans-logo" src="{{ asset('images/logo.png') }}" alt="{{ Setting::get('site_name') }}" title="{{ Setting::get('site_name') }}">--}}
                            </a>
                        </div>
                        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-4">
                            <form class="navbar-form navbar-left form-left" role="search">
                                <div class="input-group no-margin">
            					<span class="input-group-btn">
            						<button class="btn btn-default" type="button"><i style="color:#848f96;" class="fa fa-search"></i></button>
            					</span>
                                    <input type="text" id="navbar-search" data-url="{{ URL::to('api/v1/search') }}" class="form-control" placeholder="{{ trans('messages.search_placeholder') }}">
                                </div>
                            </form>

                            @if (Auth::guest())
                                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                                    <form method="POST" class="login-form navbar-form navbar-right" action="{{ url('/login') }}">
                                        {{ csrf_field() }}
                                        <fieldset class="form-group mail-form {{ $errors->has('email') ? ' has-error' : '' }}">
                                            {{ Form::text('email', NULL, ['class' => 'form-control', 'id' => 'email', 'placeholder'=> trans('auth.enter_email_or_username')]) }}
                                        </fieldset>
                                        <fieldset class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                            {{ Form::password('password', ['class' => 'form-control', 'id' => 'password', 'placeholder'=> trans('auth.password')]) }}
                                            <a href="{{ url('/password/reset') }}" class="forgot-password">Forgot your password</a>
                                        </fieldset>
                                        {{ Form::button( trans('common.signin') , ['type' => 'submit','class' => 'btn btn-success btn-submit']) }}
                                    </form>
                                </div>
                            @else
                                <ul class="nav navbar-nav navbar-right" id="navbar-right" v-cloak>
                                    {{--                                <li class="{!! (Request::segment(1)=='' ? 'active' : '') !!}"><a href="{{ url('/') }}">Home</a></li>--}}
                                    @if(Setting::get('enable_browse') == 'on')
                                        {{--                                <li class="{!! (Request::segment(1)=='browse' ? 'active' : '') !!}"><a href="{{ url('/browse') }}" style="margin-right:30px;">Explore</a></li>--}}
                                    @endif

                                    <li>
                                        <ul class="list-inline notification-list">
                                            <li class="">
                                                <a href="{{ url('/') }}">
                                                    <svg data-toggle="tooltip" data-placement="bottom" title="Home" viewBox="0 0 16 16" class="bi bi-house-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em">
                                                        <path fill-rule="evenodd" d="M8 3.293l6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293l6-6zm5-.793V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z"></path>
                                                        <path fill-rule="evenodd" d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z"></path>
                                                    </svg>
                                                </a>
                                            </li>
                                            <li class="">
                                                <a href="{{ route('explore-posts') }}">
                                                    <svg data-toggle="tooltip" data-placement="bottom" title="Explore" viewBox="0 0 16 16" class="bi bi-person-bounding-box" fill="currentColor" xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em">
                                                        <path fill-rule="evenodd" d="M1.5 1a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-1 0v-3A1.5 1.5 0 0 1 1.5 0h3a.5.5 0 0 1 0 1h-3zM11 .5a.5.5 0 0 1 .5-.5h3A1.5 1.5 0 0 1 16 1.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 1-.5-.5zM.5 11a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 1 0 1h-3A1.5 1.5 0 0 1 0 14.5v-3a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a.5.5 0 0 1 0-1h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 1 .5-.5z"></path>
                                                        <path fill-rule="evenodd" d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"></path>
                                                    </svg></a>
                                            </li>
                                            <li class="" style="display: inline-block;">
                                                <a href="{{ url('/mylists') }}">
                                                    <svg data-toggle="tooltip" data-placement="bottom" title="Fans" width="1.5em" height="1.5em" viewBox="0 0 16 16" class="bi bi-person-lines-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                      <path fill-rule="evenodd" d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm7 1.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5zm0-3a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5zm2 9a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
                                                    </svg></a>
                                            </li>
                                            <li class="dropdown message notification">
                                                <a href="#" data-toggle="dropdown" @click.prevent="showNotifications" class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
                                                    <svg data-toggle="tooltip" data-placement="bottom" title="Notifications" viewBox="0 0 16 16" class="bi bi-app-indicator" fill="currentColor" xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em">
                                                        <path fill-rule="evenodd" d="M5.5 2A3.5 3.5 0 0 0 2 5.5v5A3.5 3.5 0 0 0 5.5 14h5a3.5 3.5 0 0 0 3.5-3.5V8a.5.5 0 0 1 1 0v2.5a4.5 4.5 0 0 1-4.5 4.5h-5A4.5 4.5 0 0 1 1 10.5v-5A4.5 4.5 0 0 1 5.5 1H8a.5.5 0 0 1 0 1H5.5z"></path>
                                                        <path d="M16 3a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"></path>
                                                    </svg>
                                                    @if(Auth::user()->notifications()->where('seen',0)->count() > 0)
                                                        <span class="count hidden">{{ Auth::user()->notifications()->where('seen',0)->count() }}</span>
                                                        <span class="count" v-if="unreadNotifications > 0" >@{{ unreadNotifications }}</span>
                                                    @endif
                                                </a>
                                                <div class="dropdown-menu">
                                                    <div class="dropdown-menu-header">
                                                        <span class="side-left">{{ trans('common.notifications') }}</span>
                                                        <a v-if="unreadNotifications > 0" class="side-right" href="#" @click.prevent="markNotificationsRead" >{{ trans('messages.mark_all_read') }}</a>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                    @if(Auth::user()->notifications()->count() > 0)
                                                        <ul class="list-unstyled dropdown-messages-list scrollable" data-type="notifications">
                                                            <li class="inbox-message"  v-bind:class="[ !notification.seen ? 'active' : '' ]" v-for="notification in notifications.data">
                                                                <a href="{{ url(Auth::user()->username.'/notification/') }}/@{{ notification.id }}">
                                                                    <div class="media">
                                                                        <div class="media-left">
                                                                            <img class="media-object img-icon" v-bind:src="notification.notified_from.avatar" alt="images">
                                                                        </div>
                                                                        <div class="media-body">
                                                                            <h4 class="media-heading">
                                                                                <span class="notification-text"> @{{ notification.description }} </span>
                                                                                <span class="message-time">
                        															<span class="notification-type"><i class="fa fa-user" aria-hidden="true"></i></span>
                        															<time class="timeago" datetime="@{{ notification.created_at }}+00:00" title="@{{ notification.created_at }}">
                        																@{{ notification.created_at }}
                        															</time>
                        														</span>
                                                                            </h4>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                            <li v-if="notificationsLoading" class="dropdown-loading">
                                                                <i class="fa fa-spin fa-spinner"></i>
                                                            </li>
                                                        </ul>
                                                    @else
                                                        <div class="no-messages">
                                                            <i class="fa fa-bell-slash-o" aria-hidden="true"></i>
                                                            <p>{{ trans('messages.no_notifications') }}</p>
                                                        </div>
                                                    @endif
                                                    <div class="dropdown-menu-footer"><br>
                                                        <a href="{{ url('allnotifications') }}">{{ trans('common.see_all') }}</a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="dropdown message largescreen-message">
                                                <a href="#" data-toggle="dropdown" @click="showConversations" class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
                                                    <svg data-toggle="tooltip" data-placement="bottom" title="Messages" viewBox="0 0 16 16" class="bi bi-chat-dots-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em">
                                                        <path fill-rule="evenodd" d="M16 8c0 3.866-3.582 7-8 7a9.06 9.06 0 0 1-2.347-.306c-.584.296-1.925.864-4.181 1.234-.2.032-.352-.176-.273-.362.354-.836.674-1.95.77-2.966C.744 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7zM5 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"></path>
                                                    </svg>
                                                    <span class="count" v-if="unreadConversations" >@{{ unreadConversations }}</span>
                                                </a>
                                                <div class="dropdown-menu">
                                                    <div class="dropdown-menu-header">
                                                        <span class="side-left">{{ trans('common.messages') }}</span>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                    <div class="no-messages hidden">
                                                        <i class="fa fa-commenting-o" aria-hidden="true"></i>
                                                        <p>{{ trans('messages.no_messages') }}</p>
                                                    </div>
                                                    <ul class="list-unstyled dropdown-messages-list scrollable" data-type="messages">
                                                        <li class="inbox-message" v-for="conversation in conversations.data">
                                                            <a href="#" onclick="chatBoxes.sendMessage(@{{ conversation.user.id }})">
                                                                <div class="media">
                                                                    <div class="media-left">
                                                                        <img class="media-object img-icon" v-bind:src="conversation.user.avatar" alt="images">
                                                                    </div>
                                                                    <div class="media-body">
                                                                        <h4 class="media-heading">
                                                                            <span class="message-heading">@{{ conversation.user.name }}</span>
                                                                            <span class="online-status hidden"></span>
                                                                            <time class="timeago message-time" datetime="@{{ conversation.lastMessage.created_at }}" title="@{{ conversation.lastMessage.created_at }}">
                                                                                @{{ conversation.lastMessage.created_at }}
                                                                        </h4>
                                                                        <p class="message-text">
                                                                            @{{ conversation.lastMessage.body }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <li v-if="conversationsLoading" class="dropdown-loading">
                                                            <i class="fa fa-spin fa-spinner"></i>
                                                        </li>
                                                    </ul>
                                                    <div class="dropdown-menu-footer">
                                                        <a href="{{ url('messages') }}">{{ trans('common.see_all') }}</a>
                                                    </div>
                                                </div>
                                            </li>
                                            {{--                                           <li class="smallscreen-message">--}}
                                            {{--                                               <a href="{{ url('messages') }}">--}}
                                            {{--                                                   <i class="fa fa-comments" aria-hidden="true">--}}
                                            {{--                                                       <span class="count" v-if="unreadConversations" >@{{ unreadConversations }}</span>--}}
                                            {{--                                                   </i>--}}
                                            {{--                                                   <span class="small-screen">{{ trans('common.messages') }}</span>--}}
                                            {{--                                               </a>--}}
                                            {{--                                           </li>--}}
                                            {{--                                           <li class="chat-list-toggle">--}}
                                            {{--                                               <a href="#"><i class="fa fa-users" aria-hidden="true"></i><span class="small-screen">chat-list</span></a>--}}
                                            {{--                                           </li>--}}
                                        </ul>
                                    </li>
                                    <li class="dropdown user-image fans MARK-B">
                                        <a href="{{ url(Auth::user()->username) }}" class="dropdown-toggle no-padding" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                          <img src="{{ Auth::user()->avatar->filepath }}" alt="{{ Auth::user()->name }}" class="img-radius img-30" title="{{ Auth::user()->name }}">
                                            <span class="user-name">{{ Auth::user()->name }}</span><i class="fa fa-angle-down" aria-hidden="true"></i></a>
                                        <ul class="dropdown-menu">
                                            @if(Auth::user()->hasRole('admin'))
                                                <li class="{{ Request::segment(1) == 'admin' ? 'active' : '' }}"><a href="{{ url('admin') }}"><i class="fa fa-user-secret" aria-hidden="true"></i>{{ trans('common.admin') }}</a></li>
                                            @endif
                                            <li class="{{ (Request::segment(1) == Auth::user()->username && Request::segment(2) == '') ? 'active' : '' }}">
                                                <a href="{{ url(Auth::user()->username) }}"><i class="fa fa-user" aria-hidden="true"></i>{{ trans('common.my_profile') }}</a>
                                            </li>
                                            <li class="{{ Request::segment(3) == 'general' ? 'active' : '' }}">
                                                <a href="{{ url('/'.Auth::user()->username.'/settings/general') }}"><i class="fa fa-cog" aria-hidden="true"></i>{{ trans('common.settings') }}</a>
                                            </li>
                                            <li class="">
                                                <a href="{{ url('/mylists') }}"><i class="fa fa-list" aria-hidden="true"></i>{{ trans('common.lists') }}</a>
                                            </li>
                                            <li class="">
                                                <a href="{{ url('/'.Auth::user()->username.'/saved') }}"><i class="fa fa-bookmark" aria-hidden="true"></i>{{ trans('common.saved_post') }}</a>
                                            </li>
                                            <li class="">
                                                <a href="{{ url(Auth::user()->username.'/settings/addbank') }}"><i class="fa fa-university" aria-hidden="true"></i>{{ trans('common.add_bank') }}</a>
                                            </li>
                                            <li class="">
                                                <a href="{{ url(Auth::user()->username.'/settings/earnings') }}"><i class="fa fa-dollar" aria-hidden="true"></i>{{ trans('common.earnings') }}</a>
                                            </li>
                                            <li class="">
                                                <a href="{{ url(Auth::user()->username.'/settings/addpayment') }}"><i class="fa fa-credit-card" aria-hidden="true"></i>{{ trans('common.add_payment') }}</a>
                                            </li>
                                            <li class=""><a href="{{ route('vault.dashboard', $username) }}"><i class="fa fa-lock" aria-hidden="true"></i>Vault</a></li>
                                            @if (Auth::user()->is_bank_set)
                                                <li class="">
                                                    <a href="{{ url(Auth::user()->payment->dashboard_url) }}"><i class="fa fa-credit-card" aria-hidden="true"></i>{{ trans('common.dashboard') }}</a>
                                                </li>
                                            @endif
                                            <li class="">
                                                <a href="{{ url('/'.Auth::user()->username.'/settings/affliates') }}"><i class="fa fa-retweet" aria-hidden="true"></i>{{ trans('common.referrals') }}</a>
                                            </li>
                                            <li class="">
                                                <a href="{{ url('/faq') }}"><i class="fa fa-question" aria-hidden="true"></i>{{ trans('common.help_faq') }}</a>
                                            </li>
                                            <li class="">
                                                <a href="{{ url('/support') }}"><i class="fa fa-envelope" aria-hidden="true"></i>{{ trans('common.support') }}</a>
                                            </li>
                                            <li>
                                                <form action="{{ url('/logout') }}" method="post">
                                                    {!! csrf_field() !!}
                                                    <button type="submit" class="btn-logout" style="margin-bottom: 0px;"><i class="fa fa-sign-out" aria-hidden="true"></i>{{ trans('common.logout') }}</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            @endif
                        </div>
                    </div>
                </nav>
                <script src="{{ asset('themes/default/assets/js/notifications.js') }}"></script>