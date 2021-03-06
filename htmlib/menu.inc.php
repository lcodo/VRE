
<?php

$currentSection = '';

switch(pathinfo($_SERVER['PHP_SELF'])['filename']){
	case 'index':
	case 'editFile':
	case 'input': 
	case 'output': $currentSection = 'uw';
					 			 $currentSubSection = 'dt';
					 			 break;
	case 'uploadForm': 
	case 'uploadForm2': $currentSection = 'uw';
					 	    $currentSubSection = 'uf';
					 	    break;
	case 'help1': $currentSection = 'he';
					  $currentSubSection = 'h1';
					  break;
	case 'repositoryList': 
	case 'experiment': $currentSection = 're';
					 	   $currentSubSection = 'rl';
					  	   break;

	case 'usrProfile': $currentSection = 'up';
					  	   $currentSubSection = 'mp';
						   break;
	case 'dashboard':  $currentSection = 'ad';
					  	   $currentSubSection = 'ds';
					  	   break;
	case 'adminUsers': $currentSection = 'ad';
					  	   $currentSubSection = 'au';
					  	   break;


}

?>

<!-- BEGIN HEADER & CONTENT DIVIDER -->
            <div class="clearfix"> </div>
            <!-- END HEADER & CONTENT DIVIDER -->
            <!-- BEGIN CONTAINER -->
            <div class="page-container">
                <!-- BEGIN SIDEBAR -->
                <div class="page-sidebar-wrapper">
                    <!-- BEGIN SIDEBAR -->
                    <!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
                    <!-- DOC: Change data-auto-speed="200" to adjust the sub menu slide up/down speed -->
                    <div class="page-sidebar navbar-collapse collapse">
                        <!-- BEGIN SIDEBAR MENU -->
                        <!-- DOC: Apply "page-sidebar-menu-light" class right after "page-sidebar-menu" to enable light sidebar menu style(without borders) -->
                        <!-- DOC: Apply "page-sidebar-menu-hover-submenu" class right after "page-sidebar-menu" to enable hoverable(hover vs accordion) sub menu mode -->
                        <!-- DOC: Apply "page-sidebar-menu-closed" class right after "page-sidebar-menu" to collapse("page-sidebar-closed" class must be applied to the body element) the sidebar sub menu mode -->
                        <!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
                        <!-- DOC: Set data-keep-expand="true" to keep the submenues expanded -->
                        <!-- DOC: Set data-auto-speed="200" to adjust the sub menu slide up/down speed -->
                        <ul class="page-sidebar-menu  page-header-fixed " data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200" style="padding-top: 20px">
                            <!-- DOC: To remove the sidebar toggler from the sidebar you just need to completely remove the below "sidebar-toggler-wrapper" LI element -->
                            <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
                            <li class="sidebar-toggler-wrapper hide">
                                <div class="sidebar-toggler">
                                    <span></span>
                                </div>
                            </li>
                            <!-- END SIDEBAR TOGGLER BUTTON -->

                            <li class="nav-item  <?php if($currentSection == 'uw') { ?>active open<?php } ?>">
                                <a href="javascript:;" class="nav-link nav-toggle">
                                    <i class="icon-home"></i>
                                    <span class="title">User Workspace</span>
                                    <?php if($currentSection == 'uw') { ?><span class="selected"></span><?php } ?>
                                    <span class="arrow <?php if($currentSection == 'uw') { ?>open<?php } ?>"></span>
                                </a>
                                <ul class="sub-menu">
                                    <li class="nav-item <?php if($currentSubSection == 'dt') { ?>active open<?php } ?>">
                                        <a href="/workspace/" class="nav-link ">
                                            <span class="title">User Data</span>
                                        </a>
                                    </li>
                                    <li class="nav-item <?php if($currentSubSection == 'uf') { ?>active open<?php } ?>">
                                        <a href="/handlefiles/uploadForm.php" class="nav-link ">
                                            <span class="title">Upload Files</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item  <?php if($currentSection == 're') { ?>active open<?php } ?>">
                                <a href="javascript:;" class="nav-link nav-toggle">
                                    <i class="icon-notebook"></i>
                                    <span class="title">Repository</span>
									<?php if($currentSection == 're') { ?><span class="selected"></span><?php } ?>
                                    <span class="arrow <?php if($currentSection == 're') { ?>open<?php } ?>"></span>
                                </a>
                                <ul class="sub-menu">
                                    <li class="nav-item  <?php if($currentSubSection == 'rl') { ?>active open<?php } ?>">
                                        <a href="/repository/repositoryList.php" class="nav-link ">
                                            <span class="title">List of Experiments</span>
                                        </a>
                                    </li>                                    
                                </ul>
                            </li>
                            <li class="nav-item  ">
                                <a href="javascript:;" class="nav-link nav-toggle">
                                    <i class="icon-link"></i>
                                    <span class="title">External Links</span>
                                    <span class="arrow"></span>
                                </a>
                                <ul class="sub-menu">
                                    <li class="nav-item  ">
                                        <a href="http://www.multiscalegenomics.eu/MuGVRE/modules/BigNASimMuG/" target="_blank" class="nav-link ">
                                            <span class="title">BigNASim</span>
                                        </a>
                                    </li>
									<li class="nav-item  ">
                                        <a href="http://mmb.irbbarcelona.org/NucleosomeDynamics/" target="_blank" class="nav-link ">
                                            <span class="title">Nucleosome Dynamics</span>
                                        </a>
                                    </li>
                                    <li class="nav-item  ">
                                        <a href="http://www.multiscalegenomics.eu/MuGVRE/flexibility-browser/" target="_blank" class="nav-link ">
                                            <span class="title">Flexibility Browser</span>
                                        </a>
                                    </li>
                                    <li class="nav-item  ">
                                        <a href="http://sgt.cnag.cat/3dg/tadkit/" target="_blank" class="nav-link ">
                                            <span class="title">TADKit 3D genome visualizer</span>
                                        </a>
                                    </li>
                                    <li class="nav-item  ">
                                        <a href="http://www.multiscalegenomics.eu/MuGVRE/modules/ConnectivityBrowser/" target="_blank" class="nav-link ">
                                            <span class="title">MuG Information Network</span>
                                        </a>
                                    </li>
                                </ul>
							</li>
							<li class="nav-item">
                                <a href="javascript:;" class="nav-link nav-toggle">
                                    <i class="icon-users"></i>
                                    <span class="title">Forum</span>
                                    <span class="arrow"></span>
                                </a>
                                <ul class="sub-menu">
                                    <li class="nav-item">
                                        <a href="/forum/" target="_blank"  class="nav-link ">
                                            <span class="title">Go to Forum</span>
                                        </a>
									</li>
								</ul>
							</li>
							<li class="nav-item  <?php if($currentSection == 'he') { ?>active open<?php } ?>">
                                <a href="javascript:;" class="nav-link nav-toggle">
                                    <i class="icon-question"></i>
                                    <span class="title">Help</span>
									<?php if($currentSection == 'he') { ?><span class="selected"></span><?php } ?>
                                    <span class="arrow <?php if($currentSection == 'he') { ?>open<?php } ?>"></span>
                                </a>
                                <ul class="sub-menu">
                                    <li class="nav-item  <?php if($currentSubSection == 'h1') { ?>active open<?php } ?>">
                                        <a href="/help/help1.php" class="nav-link ">
                                            <span class="title">Help Page 1</span>
                                        </a>
                                    </li>
                                    <li class="nav-item  ">
                                        <a href="javascript:;" class="nav-link ">
                                            <span class="title">Help Page 2</span>
                                        </a>
                                    </li>
                                    <li class="nav-item  ">
                                        <a href="javascript:;" class="nav-link ">
                                            <span class="title">Help Page 3</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
							<?php if(allowedRoles($_SESSION['User']['Type'], $GLOBALS['NO_GUEST'])){ ?>
                            <li class="nav-item  <?php if($currentSection == 'up') { ?>active open<?php } ?>">
                                <a href="javascript:;" class="nav-link nav-toggle">
                                    <i class="icon-user"></i>
                                    <span class="title">User</span>
									<?php if($currentSection == 'up') { ?><span class="selected"></span><?php } ?>
                                    <span class="arrow <?php if($currentSection == 'up') { ?>open<?php } ?>"></span>
                                </a>
                                <ul class="sub-menu">
                                    <li class="nav-item  <?php if($currentSubSection == 'mp') { ?>active open<?php } ?>">
                                        <a href="/user/usrProfile.php" class="nav-link ">
                                            <span class="title">My Profile</span>
                                        </a>
									</li>
								</ul>
                            </li>
							<?php } ?>
							<?php if(allowedRoles($_SESSION['User']['Type'], $GLOBALS['ADMIN'])){ ?>
                            <li class="nav-item  <?php if($currentSection == 'ad') { ?>active open<?php } ?>">
                                <a href="javascript:;" class="nav-link nav-toggle">
                                    <i class="icon-settings"></i>
                                    <span class="title">Admin</span>
									<?php if($currentSection == 'up') { ?><span class="selected"></span><?php } ?>
                                    <span class="arrow <?php if($currentSection == 'ad') { ?>open<?php } ?>"></span>
                                </a>
                                <ul class="sub-menu">
                                    <li class="nav-item  <?php if($currentSubSection == 'ds') { ?>active open<?php } ?>">
                                        <a href="/admin/dashboard.php" class="nav-link ">
                                            <span class="title">Dashboard</span>
                                        </a>
									</li>
                                    <li class="nav-item  <?php if($currentSubSection == 'au') { ?>active open<?php } ?>">
                                        <a href="/admin/adminUsers.php" class="nav-link ">
                                            <span class="title">Users Administration</span>
                                        </a>
                                    </li>
								</ul>
                            </li>
							<?php } ?>

                        </ul>
                        <!-- END SIDEBAR MENU -->
                        <!-- END SIDEBAR MENU -->
                    </div>
                    <!-- END SIDEBAR -->
                </div>
                <!-- END SIDEBAR -->
