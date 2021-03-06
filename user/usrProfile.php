<?php

require "../phplib/genlibraries.php";
if(!allowedRoles($_SESSION['User']['Type'], $GLOBALS['NO_GUEST'])) redirectInside(); 
redirectOutside();

$countries = array();
foreach (array_values(iterator_to_array($GLOBALS['countriesCol']->find(array(),array('country'=>1))->sort(array('country'=>1)))) as $v)
	$countries[$v['_id']] = $v['country'];

?>

<?php require "../htmlib/header.inc.php"; ?>

<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white page-container-bg-solid">
  <div class="page-wrapper">

  <?php require "../htmlib/top.inc.php"; ?>
  <?php require "../htmlib/menu.inc.php"; ?>

<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <!-- BEGIN PAGE BAR -->
        <div class="page-bar">
            <ul class="page-breadcrumb">
              <li>
                  <span>User</span>
                  <i class="fa fa-circle"></i>
              </li>
              <li>
                  <span>My Profile</span>
              </li>
            </ul>
        </div>
        <!-- END PAGE BAR -->
        <!-- BEGIN PAGE TITLE-->
        <h1 class="page-title"> User Profile
            <small>user account page</small>
        </h1>
        <!-- END PAGE TITLE-->
        <!-- END PAGE HEADER-->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN PROFILE SIDEBAR -->
                <div class="profile-sidebar">
                    <!-- PORTLET MAIN -->
                    <div class="portlet light profile-sidebar-portlet ">
                        <!-- SIDEBAR USERPIC -->
                        <div class="profile-userpic">
							<img alt="" class="img-responsive<?php echo $dispClassAv1; ?>" src="<?php echo $avatarImg; ?>" />
							<div class="img-circle<?php echo $dispClassAv2; ?>" id="avatar-usr-profile" style="background-color:<?php echo $avatarColors[$bgColorAvatar]; ?>"><?php echo $firstLetterName.$firstLetterSurname; ?></div>
						</div>
                        <!-- END SIDEBAR USERPIC -->
                        <!-- SIDEBAR USER TITLE -->
                        <div class="profile-usertitle">
                            <div class="profile-usertitle-name"> <?php echo $_SESSION['User']['Name'].' '.$_SESSION['User']['Surname']; ?> </div>
							<div class="profile-usertitle-job"> <?php echo $_SESSION['User']['Inst'] ?> </div>
							<div class="profile-usertitle-lastlogin"> Last login: <strong><?php echo returnHumanDateDashboard($_SESSION['lastUserLogin']); ?></strong> </div>
                        </div>
                        <!-- END SIDEBAR USER TITLE -->
                        <!-- SIDEBAR BUTTONS -->
                        <!--<div class="profile-userbuttons">
                            <button type="button" class="btn btn-circle green btn-sm">Follow</button>
                            <button type="button" class="btn btn-circle red btn-sm">Message</button>
                        </div>-->
                        <!-- END SIDEBAR BUTTONS -->
                        <!-- SIDEBAR MENU -->
                        <div class="profile-usermenu">
                            <!--<ul class="nav">
                                <li>
                                    <a href="page_user_profile_1.html">
                                        <i class="icon-home"></i> Overview </a>
                                </li>
                                <li class="active">
                                    <a href="page_user_profile_1_account.html">
                                        <i class="icon-settings"></i> Account Settings </a>
                                </li>
                                <li>
                                    <a href="page_user_profile_1_help.html">
                                        <i class="icon-info"></i> Help </a>
                                </li>
                            </ul>-->
                        </div>
                        <!-- END MENU -->
                    </div>
                    <!-- END PORTLET MAIN -->

                </div>
                <!-- END BEGIN PROFILE SIDEBAR -->
                <!-- BEGIN PROFILE CONTENT -->
                <div class="profile-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="portlet light ">
                                <div class="portlet-title tabbable-line">
                                    <div class="caption caption-md">
                                        <i class="icon-globe theme-font hide"></i>
                                        <span class="caption-subject font-blue-madison bold uppercase">Profile Account</span>
                                    </div>
                                    <ul class="nav nav-tabs">
                                        <li class="active">
                                            <a href="#tab_1_1" data-toggle="tab">Personal Info</a>
                                        </li>
                                        <li>
                                            <a href="#tab_1_2" data-toggle="tab">Change Avatar</a>
                                        </li>
                                        <li>
                                            <a href="#tab_1_3" data-toggle="tab">Change Password</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="portlet-body">
                                    <div class="tab-content">
                                        <!-- PERSONAL INFO TAB -->
										<div class="tab-pane active" id="tab_1_1">
											<div class="alert alert-danger display-hide" id="err-chg-prf">
 				                            	Something was wrong. Please try again.
											</div>	
											<div class="alert alert-info display-hide" id="succ-chg-prf">
 				                            	Your personal info has been updated.
											</div>	
                                            <form role="form" action="javascript:void(0);" id="form-change-profile">
                                                <div class="form-group">
                                                    <label class="control-label">Name</label>
													<input name="Name" type="text" value="<?php echo $_SESSION['User']['Name']; ?>" class="form-control" id="name-usr-profile"  /> 
												</div>
                                                <div class="form-group">
                                                    <label class="control-label">Surname</label>
                                                    <input name="Surname" type="text" value="<?php echo $_SESSION['User']['Surname']; ?>" class="form-control" id="surname-usr-profile" /> </div>
                                                <div class="form-group">
                                                    <label class="control-label">Institution</label>
                                                    <input name="Inst" type="text" value="<?php echo $_SESSION['User']['Inst']; ?>" class="form-control" /> </div>
                                                <div class="form-group">
                                                    <label class="control-label">Country</label>
                                                    <select name="Country" class="form-control">
                                                         <?php
														 $selCountry = '';
														 foreach($countries as $key => $value):
															if($_SESSION['User']['Country'] == $key) $selCountry = ' selected';
															else $selCountry = '';
															echo '<option value="'.$key.'"'.$selCountry.'>'.$value.'</option>';
														 endforeach;
														 ?>
                                                    </select>
                                                </div>
                                                <div class="margiv-top-10">
													<button type="submit" id="submit-changes" class="btn green">Save Changes</button>
													<button type="reset" class="btn default">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                        <!-- END PERSONAL INFO TAB -->
                                        <!-- CHANGE AVATAR TAB -->
                                        <div class="tab-pane" id="tab_1_2">
                                            <p> Select an image from your computer. Max size: 1MB. 	
											<?php if($avatarExists == 1) { ?> If you want to remove your profile picture, please click <i>Remove</i> button and then <i>Submit</i>. <?php } ?>
											</p>
											<div class="alert alert-danger display-hide" id="err-chg-av">
 				                            	Error auploading avatar.
											</div>	
											<div class="alert alert-info display-hide" id="succ-chg-av">
 				                            	Image successfully uploaded.
											</div>	

                                            <form action="javscript:void(0);" role="form" id="form-chg-img" enctype="multipart/form-data">
                                                <div class="form-group">
                                                    <div class="fileinput fileinput-<?php if($avatarExists == 1) echo 'exists'; else echo 'new'; ?>" data-provides="fileinput">
                                                        <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                                                            <img src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=select+image" alt="" /> 
														</div>
                                                        <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 150px; height: auto;"> 
															<?php if($avatarExists == 1) { ?>
															<img src="<?php echo $avatarImg; ?>" alt="" />
															<?php } ?>
														</div>
                                                        <div>
                                                            <span class="btn default btn-file">
                                                                <span class="fileinput-new"> Select image </span>
                                                                <span class="fileinput-exists"> Change </span>
                                                                <input type="file" name="imageprofile" id="imageprofile"> </span>
                                                            <a href="javascript:;" class="btn default fileinput-exists" data-dismiss="fileinput"> Remove </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="margin-top-10">
                                                    <a href="javascript:;" class="btn green" id="submit-img"> Submit </a>
                                                </div>
                                            </form>
                                        </div>
                                        <!-- END CHANGE AVATAR TAB -->
                                        <!-- CHANGE PASSWORD TAB -->
										<div class="tab-pane" id="tab_1_3">
											<div class="alert alert-danger display-hide" id="err-chg-pwd">
 				                            	Something was wrong. Please try again.
											</div>	
											<div class="alert alert-danger display-hide" id="err-chg-pwd2">
 				                            	Current password incorrect. Please try again.
											</div>
											<div class="alert alert-info display-hide" id="succ-chg-pwd">
 				                            	Your password has been changed.
											</div>	
                                            <form action="javascript:void(0);" id="form-change-pwd"> 
                                                <div class="form-group">
                                                    <label class="control-label">Current Password</label>
                                                    <input type="password" name="oldpass" class="form-control" /> </div>
                                                <div class="form-group">
                                                    <label class="control-label">New Password</label>
                                                    <input type="password" name="pass1" class="form-control" id="new-password"  /> </div>
                                                <div class="form-group">
                                                    <label class="control-label">Re-type New Password</label>
                                                    <input type="password" name="pass2" class="form-control" /> </div>
												<div class="margin-top-10">
													<button type="submit" id="submit-pwd" class="btn green">Change Password</button>
													<button type="reset" class="btn default">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                        <!-- END CHANGE PASSWORD TAB -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END PROFILE CONTENT -->
            </div>
        </div>
    </div>
    <!-- END CONTENT BODY -->
</div>
<!-- END CONTENT -->

<?php 

require "../htmlib/footer.inc.php"; 
require "../htmlib/js.inc.php";

?>
