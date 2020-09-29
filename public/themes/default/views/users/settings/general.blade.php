<!-- main-section -->
<!-- <div class="main-content"> -->
<style>
    input[type=checkbox]{
        height: 0;
        width: 0;
        visibility: hidden;
    }

    .toggle > label {
        cursor: pointer;
        text-indent: -9999px;
        width: 60px !important;
        height: 33px !important;;
        background: grey;
        display: block;
        border-radius: 100px;
        position: relative;
    }

    .toggle > label:after {
        content: '';
        position: absolute;
        top: 5px;
        left: 5px;
        width: 23px !important;
        height: 23px !important;
        background: #fff;
        border-radius: 90px;
        transition: 0.3s;
    }

    input:checked +  label {
        background: #bada55;
    }

    input:checked +  label:after {
        left: calc(100% - 5px);
        transform: translateX(-100%);
    }

     label:active:after {
        width: 130px;
    }
</style>
	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<div class="post-filters">
					{!! Theme::partial('usermenu-settings') !!}
				</div>
			</div>
			<div class="col-md-8">
				<div class="panel panel-default">
				
					<div class="panel-heading no-bg panel-settings">
					@include('flash::message')
						<h3 class="panel-title">
							{{ trans('common.general_settings') }}
						</h3>
					</div>
					
					<div class="panel-body nopadding">
						<div class="fans-form">
							<form method="POST" action="{{ url('/'.$username.'/settings/general/') }}">
								{{ csrf_field() }}
								
								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group required {{ $errors->has('username') ? ' has-error' : '' }}">
											{{ Form::label('username', trans('common.username')) }}
											{{ Form::text('new_username', Auth::user()->username, ['class' => 'form-control', 'placeholder' => trans('common.username')]) }}
											@if ($errors->has('username'))
											<span class="help-block">
												{{ $errors->first('username') }}
											</span>
											@endif
											<small class="text-muted"><a href="{{ url($username) }}">{{ url('/') }}/{{$username}}</a></small>
										</fieldset>
									</div>
									<div class="col-md-6">
										<fieldset class="form-group required {{ $errors->has('name') ? ' has-error' : '' }}">
											{{ Form::label('name', trans('common.fullname')) }}
											{{ Form::text('name', Auth::user()->name, ['class' => 'form-control', 'placeholder' => trans('common.fullname')]) }}
											@if ($errors->has('name'))
												<span class="help-block">
												{{ $errors->first('name') }}
											</span>
											@endif
										</fieldset>
									</div>
								</div>

								<div class="row">
									<div class="col-md-2">
										{{ Form::label('language', trans('common.language')) }}
									</div>
									<div class="dropdown col-md-4">
										<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
										<span class="user-name">
											@if(Auth::user()->language != null)
												<?php $key = Auth::user()->language; ?>
											@else
												<?php $key = App\Setting::get('language'); ?>
											@endif
											@if($key == 'gr')
												<span class="flag-icon flag-icon-gr"></span>
											@elseif($key == 'en')
												<span class="flag-icon flag-icon-us"></span>
											@elseif($key == 'zh')
												<span class="flag-icon flag-icon-cn"></span>
											@else
												<span class="flag-icon flag-icon-{{ $key }}"></span>
											@endif

                                        </span> <i class="fa fa-angle-down" aria-hidden="true"></i></a>
										<ul class="dropdown-menu">
											@foreach( Config::get('app.locales') as $key => $value)
												<li class=""><a href="#" class="switch-language" data-language="{{ $key }}">
														@if($key == 'gr')
															<span class="flag-icon flag-icon-gr"></span>
														@elseif($key == 'en')
															<span class="flag-icon flag-icon-us"></span>
														@elseif($key == 'zh')
															<span class="flag-icon flag-icon-cn"></span>
														@else
															<span class="flag-icon flag-icon-{{ $key }}"></span>
														@endif

														{{ $value }}</a></li>
											@endforeach
										</ul>
									</div>
                                    <div class="col-md-6">
                                        <fieldset class="form-group {{ $errors->has('country') ? ' has-error' : '' }}">
                                            {{ Form::label('country', trans('common.country')) }}
                                            {{ Form::text('country', Auth::user()->country, ['class' => 'form-control', 'placeholder' => trans('common.country')]) }}
                                            @if ($errors->has('country'))
                                                <span class="help-block">
												{{ $errors->first('country') }}
											</span>
                                            @endif
                                        </fieldset>
                                    </div>
								</div>
                                
								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group required {{ $errors->has('email') ? ' has-error' : '' }}">
											{{ Form::label('email', trans('auth.email_address')) }}
											{{ Form::email('email', Auth::user()->email, ['class' => 'form-control', 'placeholder' => trans('auth.email_address')]) }}
											@if ($errors->has('email'))
											<span class="help-block">
												{{ $errors->first('email') }}
											</span>
											@endif
										</fieldset>
									</div>
									<div class="col-md-6">
										<fieldset class="form-group required">
											{{ Form::label('gender', trans('common.gender')) }}
											{{ Form::select('gender', array('male' => trans('common.male'), 'female' => trans('common.female'), 'other' => trans('common.none')), Auth::user()->gender, array('class' => 'form-control')) }}
										</fieldset>
									</div>
{{--									<div class="col-md-6">--}}
{{--										<fieldset class="form-group required {{ $errors->has('password') ? ' has-error' : '' }}">--}}
{{--											{{ Form::label('password', trans('auth.password')) }}--}}
{{--											{{ Form::password('password', ['class' => 'form-control', 'id' => 'password', 'placeholder'=> trans('auth.password')]) }}--}}
{{--										</fieldset>--}}
{{--									</div>--}}
								</div>

								{{--Subscribe price--}}
								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group {{ $errors->has('subscribe_price') ? ' has-error' : '' }}">
											{{ Form::label('subscribe_price', trans('auth.subscribe_price')) }}
											{{ Form::text('subscribe_price', Auth::user()->price, ['class' => 'form-control', 'placeholder' => trans('auth.subscribe_price')]) }}
											@if ($errors->has('subscribe_price'))
												<span class="help-block">
												{{ $errors->first('subscribe_price') }}
											</span>
											@endif
										</fieldset>
									</div>
								</div>
								{{--End of Subscribe price--}}

									@if(Setting::get('custom_option1') != NULL || Setting::get('custom_option2') != NULL)
										<div class="row">
											@if(Setting::get('custom_option1') != NULL)
											<div class="col-md-6">
												<fieldset class="form-group">
													{{ Form::label('custom_option1', Setting::get('custom_option1')) }}
													{{ Form::text('custom_option1', Auth::user()->custom_option1, ['class' => 'form-control']) }}
												</fieldset>
											</div>
											@endif

											@if(Setting::get('custom_option2') != NULL)
											<div class="col-md-6">
												<fieldset class="form-group">
													{{ Form::label('custom_option2', Setting::get('custom_option2')) }}
													{{ Form::text('custom_option2', Auth::user()->custom_option2, ['class' => 'form-control']) }}
												</fieldset>
											</div>
											@endif
										</div>
									@endif

									@if(Setting::get('custom_option3') != NULL || Setting::get('custom_option4') != NULL)
										<div class="row">
											@if(Setting::get('custom_option3') != NULL)
											<div class="col-md-6">
												<fieldset class="form-group">
													{{ Form::label('custom_option3', Setting::get('custom_option3')) }}
													{{ Form::text('custom_option3', Auth::user()->custom_option3, ['class' => 'form-control']) }}
												</fieldset>
											</div>
											@endif

											@if(Setting::get('custom_option4') != NULL)
											<div class="col-md-6">
												<fieldset class="form-group">
													{{ Form::label('custom_option4', Setting::get('custom_option4')) }}
													{{ Form::text('custom_option4', Auth::user()->custom_option4, ['class' => 'form-control']) }}
												</fieldset>
											</div>
											@endif
										</div>
									@endif


									<div class="pull-right">
										{{ Form::submit(trans('common.save_changes'), ['class' => 'btn btn-success']) }}
									</div>
									<div class="clearfix"></div>
								</form>
							</div><!-- /fans-form -->
						</div>
					</div>
					<!-- End of first panel -->

					<div class="panel panel-default">
						<div class="panel-heading no-bg panel-settings">
							<h3 class="panel-title">
								{{ trans('common.update_password') }}
							</h3>
						</div>
						<div class="panel-body nopadding">
							<div class="fans-form">
								<form method="POST" action="{{ url('/'.Auth::user()->username.'/settings/password/') }}">
									{{ csrf_field() }}

									<div class="row">
										<div class="col-md-6">
											<fieldset class="form-group {{ $errors->has('current_password') ? ' has-error' : '' }}">
												{{ Form::label('current_password', trans('common.current_password')) }}
												<input type="password" class="form-control" id="current_password" name="current_password" value="{{ old('current_password') }}" placeholder= "{{ trans('messages.enter_old_password') }}">

												@if ($errors->has('current_password'))
												<span class="help-block">
													{{ $errors->first('current_password') }}
												</span>
												@endif
											</fieldset>
										</div>
										<div class="col-md-6">
											<fieldset class="form-group {{ $errors->has('new_password') ? ' has-error' : '' }}">
												{{ Form::label('new_password', trans('common.new_password')) }}
												<input type="password" class="form-control" id="new_password" name="new_password" value="{{ old('new_password') }}" placeholder="{{ trans('messages.enter_new_password') }}">

												@if($errors->has('new_password'))
												<span class="help-block">
													{{ $errors->first('new_password') }}
												</span>
												@endif
											</fieldset>
										</div>
									</div>

									<div class="pull-right">
										{{ Form::submit(trans('common.save_password'), ['class' => 'btn btn-success']) }}
									</div>
									<div class="clearfix"></div>
								</form>
							</div><!-- /fans-form -->
						</div>
					</div>
					<!-- End of second panel -->

                <div class="panel panel-default">
                    <div class="panel-heading no-bg panel-settings">
                        <h3 class="panel-title">
                            {{ trans('common.water_mark_settings') }}
                        </h3>
                    </div>
                    <div class="panel-body nopadding">
                        <div class="fans-form">
                            <form method="POST" action="{{ url('/'.Auth::user()->username.'/settings/save-watermark-settings') }}" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                        
                                <div class="row">
                                    <div class="col-md-12">
                                        <fieldset class="form-group toggle {{ $errors->has('watermark_text') ? ' has-error' : '' }}">
                                        <input type="checkbox" id="watermark" name="watermark" class="form-control"
                                               {{ \Illuminate\Support\Facades\Auth::user()->settings()->watermark == 1 ? 'checked' : '' }} value="{{ \Illuminate\Support\Facades\Auth::user()->settings()->watermark == 1 ? 1 : 0 }}"/><label for="watermark">watermark</label>
                                        </fieldset>
                                    </div>
									<div class="watermark_settings">
										<div class="col-md-6">
											<fieldset class="form-group watermark_text {{ $errors->has('watermark_text') ? ' has-error' : '' }}">
												{{ Form::label('watermark_text', trans('common.watermark_text')) }}
												<input type="text" class="form-control" id="watermark_text" name="watermark_text" placeholder= "{{ trans('common.watermark_text') }}" value="{{ \Illuminate\Support\Facades\Auth::user()->settings()->watermark_text }}">

												@if ($errors->has('watermark_text'))
													<span class="help-block">
														{{ $errors->first('watermark_text') }}
													</span>
												@endif
											</fieldset>
										</div>
										<div class="col-md-6">
											<fieldset class="form-group watermark_file {{ $errors->has('watermark_file') ? ' has-error' : '' }}">
												{{ Form::label('watermark_file', trans('common.watermark_file')) }}
												@if(isset(Auth::user()->settings()->watermark_file_id))
													<a href="{{ $waterMarkUrl }}" download="{{\Illuminate\Support\Facades\Auth::user()->watermark_file_id}}">
														{{ trans('common.existing_file') }}</a>
												@endif
												<input type="file" class="form-control" id="watermark_file" name="watermark_file" placeholder= "{{ trans('common.watermark_file') }}">

												@if ($errors->has('watermark_file'))
													<span class="help-block">
														{{ $errors->first('watermark_file') }}
													</span>
												@endif
												@if ($errors->any())
													<span class="help-block" style="color: red;">
													@foreach ($errors->all(':message') as $input_error)
															{{ $input_error }}
														@endforeach
													</span>
												@endif
											</fieldset>
										</div>
										<div class="col-md-6">
											<fieldset class="form-group {{ $errors->has('watermark_font_size') ? ' has-error' : '' }}">
												{{ Form::label('watermark_font_size', trans('common.watermark_font_size')) }}
												<input type="number" class="form-control" id="watermark_font_size" min="1" name="watermark_font_size" value="{{ \Illuminate\Support\Facades\Auth::user()->settings()->watermark_font_size }}" placeholder= "{{ trans('common.watermark_font_size') }}">

												@if ($errors->has('watermark_font_size'))
													<span class="help-block">
														{{ $errors->first('watermark_font_size') }}
													</span>
												@endif
											</fieldset>
										</div>
										<div class="col-md-6">
											<fieldset class="form-group {{ $errors->has('watermark_position') ? ' has-error' : '' }}">
												{{ Form::label('watermark_position', trans('common.watermark_position')) }}
												{{ Form::select('watermark_position', get_image_insert_location(), Auth::user()->settings()->watermark_position, array('class' => 'form-control')) }}
												@if ($errors->has('watermark_position'))
													<span class="help-block">
														{{ $errors->first('watermark_position') }}
													</span>
												@endif
											</fieldset>
										</div>
										<div class="col-md-6">
											<fieldset class="form-group {{ $errors->has('watermark_font_color') ? ' has-error' : '' }}">
												{{ Form::label('watermark_font_color', trans('common.watermark_font_color')) }}
												<input type="text" class="form-control" id="watermark_font_color" name="watermark_font_color" value="{{ \Illuminate\Support\Facades\Auth::user()->settings()->watermark_font_color }}" placeholder= "{{ '#'.trans('common.color_code') }}">

												@if ($errors->has('watermark_font_color'))
													<span class="help-block">
														{{ $errors->first('watermark_font_color') }}
													</span>
												@endif
											</fieldset>
										</div>
									</div>
                                </div>

                                <div class="pull-right">
                                    {{ Form::submit(trans('common.save_changes'), ['class' => 'btn btn-success']) }}
                                </div>
                                <div class="clearfix"></div>
                            </form>
                        </div><!-- /fans-form -->
                    </div>
                </div>

            </div>
			</div><!-- /row -->
		</div>
	<!-- </div> --><!-- /main-content -->
<script>
    window.checkCheckboxStatus = function () {
        if ($('#watermark').is(':checked') == true) {
            $('#watermark').val(1);
            $('.watermark_settings').css('display', '');
        } else {
            $('#watermark').val(0);
            $('.watermark_settings').css('display', 'none');
        }
    };
    $(document).ready(function () {
        checkCheckboxStatus();
        $('#watermark').change(function () {
            checkCheckboxStatus();
        });
    });
</script>
