<?php

require "phplib/genlibraries.php";
checkIfSessionUser(basename($_SERVER['PHP_SELF']));

$countries = array();
$countries[''] = 'Country';
foreach (array_values(iterator_to_array($GLOBALS['countriesCol']->find(array(),array('country'=>1))->sort(array('country'=>1)))) as $v)
	$countries[$v['_id']] = $v['country'];

?>

<?php require "htmlib/header.inc.php"; ?>

<body class=" login">
        <!-- BEGIN LOGO -->
        <div class="logo">
            <a href="/">
                <img src="/assets/pages/img/logo-big.png" alt="" /> </a>
        </div>
        <!-- END LOGO -->
        <!-- BEGIN LOGIN -->
        <div class="content">
            <!-- BEGIN LOGIN FORM -->
            <form class="login-form" action="/applib/login.php" method="post">
                <h3 class="form-title font-green">Sign In</h3>
				<div class="alert alert-danger display-hide" id="err-msg-login">
                    <button class="close" data-close="alert"></button>
                    <span> User not existing or login incorrect. </span>
                </div>
                <div class="alert alert-danger display-hide" id="err-mail-pwd">
                    <button class="close" data-close="alert"></button>
                    <span> Enter your email and password. </span>
                </div>
                <div class="form-group">
                    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                    <label class="control-label visible-ie8 visible-ie9">Email</label>
                    <input class="form-control form-control-solid placeholder-no-fix" type="text" autocomplete="off" placeholder="Email" name="usermail" /> </div>
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Password</label>
                    <input class="form-control form-control-solid placeholder-no-fix" type="password" autocomplete="off" placeholder="Password" name="password" /> </div>
                <div class="form-actions">
                    <button type="submit" id="login-button"  class="btn green uppercase">Login</button>
                    <!--<label class="rememberme check mt-checkbox mt-checkbox-outline">
                        <input type="checkbox" name="remember" value="1" />Remember
                        <span></span>
                    </label>-->
                    <a href="javascript:;" id="forget-password" class="forget-password">Forgot Password?</a>
                </div>
            	<!--<div class="create-account login-guest">
                    <p>
						<a href="applib/signinguest.php" class="uppercase">Login as a guest</a>
						<span class="tooltip-mt-radio"><i class="icon-question tooltips font-white" data-container="body" data-placement="right" data-original-title="As a guest user you have reduced functionalities in the platform. If you want complete proficiency, please click below to Sign Up."></i></span>
                    </p>
                </div>
                <div class="create-account">
                    <p>
                        <a href="javascript:;" id="register-btn" class="uppercase">Create an account</a>
                    </p>
                </div>-->
            </form>
            <!-- END LOGIN FORM -->
            <!-- BEGIN FORGOT PASSWORD FORM -->
            <form class="forget-form" action="javascript:void(0);" method="post">
                <h3 class="font-green">Forgot Password?</h3>
				<p> Enter your Email address below to reset your password. </p>
				<div class="alert alert-danger display-hide" id="err-snd-email">
                    <button class="close" data-close="alert"></button>
                    <span> There was a problem sending the email, please try again. </span>
                </div>
                <div class="alert alert-danger display-hide" id="err-mail-prvd">
                    <button class="close" data-close="alert"></button>
                    <span> The email provided doesn't exist in our Data Base. </span>
                </div>
                <div class="alert alert-success display-hide" id="succ-snd-email">
                    <button class="close" data-close="alert"></button>
                    <span> Message successfully sent. Please check your email. </span>
                </div>
                <div class="form-group">
                    <input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="Email" name="emailf" /> </div>
                <div class="form-actions">
                    <button type="button" id="back-btn" class="btn green btn-outline">Back</button>
                    <button type="submit" id="forgot-submit-btn" class="btn btn-success uppercase pull-right">Submit</button>
                </div>
            </form>
            <!-- END FORGOT PASSWORD FORM -->
            <!-- BEGIN REGISTRATION FORM -->
            <form class="register-form" action="/applib/login.php" method="post">
                <h3 class="font-green">Sign Up</h3>
		<div class="alert alert-danger display-hide" id="err-msg-signup">
                    <button class="close" data-close="alert"></button>
                    <span> User already registered, try with another email. </span>
                </div>
                <p class="hint"> Enter your personal details below: </p>
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Name</label>
                    <input class="form-control placeholder-no-fix" type="text" placeholder="Name" name="Name" /> </div>
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Surname</label>
                    <input class="form-control placeholder-no-fix" type="text" placeholder="Surname" name="Surname" /> </div>
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Institution</label>
                    <input class="form-control placeholder-no-fix" type="text" placeholder="Institution" name="Inst" /> </div>
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Country</label>
                    <select name="Country" class="form-control">
                    <?php
					foreach($countries as $key => $value):
						echo '<option value="'.$key.'">'.$value.'</option>';
					endforeach;
					?>
                    </select>
                </div>
                <div class="form-group">
                    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                    <label class="control-label visible-ie8 visible-ie9">Email</label>
                    <input class="form-control placeholder-no-fix" type="text" placeholder="Email" name="Email" /> </div>
                <p class="hint"> Please enter your password (twice): </p>
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Password</label>
                    <input class="form-control placeholder-no-fix" type="password" autocomplete="off" id="register_password" placeholder="Password" name="pass1" /> </div>
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Re-type Your Password</label>
                    <input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="Re-type Your Password" name="pass2" /> </div>
                <p class="hint"> Type of user: </p>
                <div class="form-group">
                    <div class="mt-radio-list radio-signin">
                        <div class="mt-radio-container">
                          <label class="mt-radio mt-radio-outline"> Common user
                            <input type="radio" value="2" name="Type" checked="" />
                            <span></span>
                          </label>
                          <span class="tooltip-mt-radio"><i class="icon-question tooltips" data-container="body" data-placement="right" data-original-title="Ordinary user description: lorem ipsum dolor sit amet consectetuer"></i></span>
                        </div>
                        <div class="mt-radio-container">
                          <label class="mt-radio mt-radio-outline"> Premium user
                            <input type="radio" value="1" name="Type" />
                            <span></span>
                          </label>
                          <span class="tooltip-mt-radio"><i class="icon-question tooltips" data-container="body" data-placement="right" data-original-title="Premium user description: lorem ipsum dolor sit amet consectetuer"></i></span>
                        </div>
                    </div>
                </div>
				<input type="hidden" id="hidden-usermail-field" name="usermail" value="" />
				<input type="hidden" id="hidden-password-field" name="password" value="" />
                <div class="form-actions">
                    <button type="button" id="register-back-btn" class="btn green btn-outline">Back</button>
                    <button type="submit" id="register-submit-btn" class="btn btn-success uppercase pull-right">Submit</button>
                </div>
            </form>
            <!-- END REGISTRATION FORM -->
        </div>

<?php

require "htmlib/footer-login.inc.php";
require "htmlib/js.inc.php";

?>

