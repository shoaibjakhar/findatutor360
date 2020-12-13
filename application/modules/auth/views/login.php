
<style>

.login-user-details {
    display: -webkit-inline-box;
}

.login-user-details li {
    text-align: center;
    font-size: 12px;
    margin: 2px;
}

.login-user-details li a.positive {
    border: 1px solid #9c969c;
    background: #9c969c;
    color: #fff;
}

.login-user-details li a {
     padding: 5px 5px; 
     display: block; 
     text-decoration: none; 
     border-radius: 3px; 
     cursor: pointer; 
}

.list-unstyled {
    /*padding-left: 20px;*/
    /*margin: 10px;*/
    list-style: none;
}
</style>
<!-- Login/Register Panel -->
<div class="login-register">
	<div class="container">
		<div class="row row-margin">
			<!-- Sign in section -->
			<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
				<div class="sign-block signin-left">
					<h2><span><?php echo get_languageword('Sign In');?></span> <?php echo get_languageword('With Your Account');?></h2>
					
					<span class="error"><?php echo $message;?></span>
					<?php 
					$attrs = array(
					'name' => 'token_form',
					'id' => 'token_form',
					'class' => 'form-signin  comment-form',
					);
					echo form_open(current_uri(), $attrs);?>
						<div class="input-group ">
							<label><?php echo get_languageword('email');?><?php echo required_symbol();?></label>
							<?php 
							$attributes = array(
							'name'	=> 'identity',
							'id'	=> 'identity',
							'value'	=> $this->form_validation->set_value('identity'),
							'placeholder'=> get_languageword('email'),
							'class' => 'form-control',
							'type' => 'email',
							);
							echo form_input($attributes);?>							
						</div>
						<div class="input-group ">
							<label><?php echo get_languageword('Password');?><?php echo required_symbol();?></label>
							<?php 
							$attributes = array(
							'name'	=> 'password',
							'id'	=> 'password',
							'value'	=> $this->form_validation->set_value('password'),
							'placeholder'=> get_languageword('password'),
							'class' => 'form-control',
							);
							echo form_password($attributes);?>
						</div>
						<div class="check">
							<a href="<?php echo URL_AUTH_FORGOT_PASSWORD;?>" class="forgot-pass"> <?php echo get_languageword('Forgot your password?');?></a>
						</div>


						<div class="input-group ">
							<ul class="login-user-details list-unstyled">
	                          <li onclick="setCredentials('admin')" class=""><a class="positive" href="javascript:void(0);">Admin</a></li>
	                          <li onclick="setCredentials('tutor')" class=""><a class="positive" href="javascript:void(0);">Tutor</a></li>
	                          <li onclick="setCredentials('student')" class=""><a class="positive" href="javascript:void(0);">Student</a></li>
	                          <li onclick="setCredentials('institute')" class=""><a class="positive" href="javascript:void(0);">Institute</a></li>
	                        </ul>
						</div>
						<div class="input-group ">
							<ul class="login-user-details list-unstyled">
	                          	<li onclick="setCredentials('institute_tutor')" class=""><a class="positive" href="javascript:void(0);">Institute Tutor</a></li>
	                          	<li><a class="positive" href="<?php echo SITEURL;?>Documentation/" target="_blank">Documentation</a></li>
	                        </ul>
						</div>

						<!-- <button class="btn-link-dark signin-btn center-block" type="submit" name="btnLogin"><?php echo get_languageword('Sign In');?></button> -->

						<button class="btn-link-dark center-block" type="submit" name="btnLogin"><?php echo get_languageword('Sign In');?></button>
						</form>


						
						<!--social logins start-->
						<!--div class="login-with-social">
			                <?php 
			                if ($this->config->item('site_settings')->facebook_login_enable=="yes") {
							//fb login
							$authUrl =  $this->facebook->login_url();
							//echo $authUrl;
							?>
						 	<a href="<?php echo $authUrl;?>"><img src="<?php echo URL_FRONT_IMAGES;?>fb-image.png" alt="Login through Facebook" class="sc-icn fb-img"></a>
						   <?php } ?>

						   <?php 
			                if ($this->config->item('site_settings')->google_login_enable=="yes") {?>
 							<a href="<?php echo $google_login_url;?>"><img src="<?php echo URL_FRONT_IMAGES;?>google-login.png" alt="Login through Google" class="sc-icn gl-img pull-right"></a> 
 						<?php } ?>
		                </div-->

		                <?php 
		                $fb_login = $this->config->item('site_settings')->facebook_login_enable;
		                $ggl_login = $this->config->item('site_settings')->google_login_enable;

		                if ($fb_login=="yes" || $ggl_login=="yes") {
		                ?>
		                <div class="slogin-form">
							<div class="or-seperator"><i>or</i></div>
							<h2><span><?php echo get_languageword('Log In');?></span> <?php echo get_languageword('With');?></h2>

							<?php if ($ggl_login=="yes") { ?>
							<a href="<?php echo $google_login_url;?>"><button class="loginBtn loginBtn--google"><strong><?php echo get_languageword('google');?></strong></button></a>
							<?php } ?>

							<?php if ($fb_login=="yes") { 
								$authUrl =  $this->facebook->login_url();
								?>
							<a href="<?php echo $authUrl;?>" title="Facebook Login"><button class="loginBtn loginBtn--facebook"><strong><?php echo get_languageword('facebook');?></strong></button></a>
							<?php } ?>

							<br><br>
							<div class="forgot-pass">Note: Only for Students</div>
						</div>
						<?php } ?>

		                <!--social logins end-->


				</div>
			</div>
			<!-- Sign up section -->
			<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
				<span class="error"><?php echo (isset($message_create)) ? $message_create : '';?></span>
				<div class="sign-block">
					<h2><span><?php echo get_languageword('Register');?> </span> <?php echo get_languageword('With Tutors');?></h2>
					<?php echo form_open(current_uri(), array('class' => 'form-signup comment-form', 'name' => 'token_form', 'id' => 'token_form'));?>

						
						<div class="input-group ">
							<label><?php echo get_languageword('Register As');?><?php echo required_symbol();?></label>
							<div class="dark-picker dark-picker-bright">
							<?php 
							$opt_groups = array('' => get_languageword('Please select group'));
							if(!empty($groups))
							{
								foreach($groups as $g)
								{
									$opt_groups[$g->id] = ucwords($g->name);
								}
							}
							echo form_dropdown('user_belongs_group', $opt_groups, $this->form_validation->set_value('user_belongs_group'), 'id="u_group" class="select-picker" onchange="toggle_name();"');?>	
						</div>
						</div>

						<div class="input-group ">
							<label id="lbl_fname"><?php echo get_languageword('First Name');?><?php echo required_symbol();?></label>
							<?php 
							$attributes = array(
							'name'	=> 'first_name',
							'id'	=> 'first_name',
							'value'	=> $this->form_validation->set_value('first_name'),
							'placeholder'=>  get_languageword('first_name'),
							'class' => 'form-control',
							);
							echo form_input($attributes);?>	
						</div>

						<div class="input-group " id="div_lname">
							<label><?php echo get_languageword('Last Name');?></label>
								<?php 
							$attributes = array(
							'name'	=> 'last_name',
							'id'	=> 'last_name',
							'value'	=> $this->form_validation->set_value('last_name'),
							'placeholder'=>  get_languageword('last_name'),
							'class' => 'form-control',
							);
							echo form_input($attributes);?>
						</div>

						<div class="input-group ">
							<label><?php echo get_languageword('Email');?><?php echo required_symbol();?></label>
							<?php 
						$attributes = array(
						'name'	=> 'identity',
						'id'	=> 'company_identity',
						'value'	=> $this->form_validation->set_value('identity'),
						'placeholder'=>  get_languageword('email'),
						'class' => 'form-control',
						);
						echo form_input($attributes);?>
						</div>
						<div class="input-group ">
							<label><?php echo get_languageword('Password');?> <?php echo required_symbol();?>(<?php echo get_languageword('must be at least');?> <?php echo $this->config->item('min_password_length', 'ion_auth');?> <?php echo get_languageword('characters');?>):</label>
							<?php 
							$attributes = array(
							'name'	=> 'password',
							'id'	=> 'company_password',
							'value'	=> $this->form_validation->set_value('password'),
							'placeholder'=>  get_languageword('password'),
							'class' => 'form-control',
							);
							echo form_password($attributes);?>
						</div>
						
						<div class="input-group ">
							<label><?php echo get_languageword('Confirm Password');?><?php echo required_symbol();?></label>
							<?php 
						$attributes = array(
						'name'	=> 'password_confirm',
						'id'	=> 'password_confirm',
						'value'	=> $this->form_validation->set_value('password_confirm'),
						'placeholder'=>  get_languageword('confirm_password'),
						'class' => 'form-control',
						);
						echo form_password($attributes);?>
						</div>
						
						
						<div class="input-group ">
							<label><?php echo get_languageword('pin_code');?></label>
							<?php 
							$attributes = array(
							'name'	=> 'pin_code',
							'id'	=> 'pin_code',
							'value'	=> $this->form_validation->set_value('pin_code'),
							'placeholder'=>  get_languageword('pin_code'),
							'class' => 'form-control',
							);
							echo form_input($attributes);?>
						</div>

						
						<div class="input-group ">
							<label><?php echo get_languageword('Phone Number');?><?php echo required_symbol();?></label>
							<div class="row">
	    						<div class="col-sm-6 pad-right0">
	    							<div class="dark-picker dark-picker-bright">
									<?php 
										$val = set_value('phone_code');
										echo form_dropdown('phone_code', $country_opts, $val, 'id="phone_code" class="select-picker"');?>
									</div>
								</div>
								<div class="col-sm-6">
									<?php 
									$attributes = array(
									'name'	=> 'phone',
									'id'	=> 'phone',
									'value'	=> $this->form_validation->set_value('phone'),
									'placeholder'=>  get_languageword('phone'),
									'class' => 'form-control',
									);
									echo form_input($attributes);?>
								</div>
							</div>
						</div>
						
						<button class="btn-link-dark  center-block" type="submit" name="create"><?php echo get_languageword('Create an Account');?></button>
					</form>
				</div>
			</div>

		</div>
	</div>
</div>
<!-- Login/Register Panel -->

<script src="<?php echo URL_FRONT_JS;?>jquery.js"></script>
<script>
$(function () {
	toggle_name();
});
function toggle_name()
{
	grp_type = $('#u_group option:selected').val();

	if(grp_type == 4) {

		$('#lbl_fname').html('<?php echo get_languageword("Institute Name").required_symbol();?>');
		$('#first_name').attr('placeholder', "<?php echo get_languageword('Institute Name'); ?>");
		$('#div_lname').slideUp();

	} else {

		$('#lbl_fname').html('<?php echo get_languageword("First Name").required_symbol();?>');
		$('#first_name').attr('placeholder', "<?php echo get_languageword('First Name'); ?>");
		$('#div_lname').slideDown();
	}
}


function setCredentials(userType) {

  username = 'micheljohn930@gmail.com';
  password = 'password';

  if (userType=='admin') {
    username = 'admin@admin.com';
    password = '123456789';
  }
  else if (userType=='tutor') {
    username = 'bellaadwerd123@gmail.com';
    password = 'password';
  }
  else if (userType=='institute') {
    username = 'bebe34@gma.com';
    password = 'password';

  } else if (userType=='institute_tutor') {
    username = 'blimi33@gma.com';
    password = 'password';
  }

  $('#identity').val(username);
  $('#password').val(password);
}
</script>


