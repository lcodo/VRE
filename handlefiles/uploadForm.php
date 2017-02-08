<?php

require "../phplib/genlibraries.php";
redirectOutside();

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
                                  <span>User Workspace</span>
                                  <i class="fa fa-circle"></i>
                              </li>
                              <li>
                                  <span>Upload Files</span>
                              </li>
                            </ul>
                        </div>
                        <!-- END PAGE BAR -->
                        <!-- BEGIN PAGE TITLE-->
                        <h1 class="page-title"> Upload Files
                            <small>upload files to your data table</small>
                        </h1>
                        <!-- END PAGE TITLE-->
                        <!-- END PAGE HEADER-->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mt-element-step">
                                    <div class="row step-line">
                                        <div class="mt-step-desc">
                                          Select from your local computer the data to be analysed and visualized by MuG server. 
										  You can upload multiple files to your workspace just drag and dropping them over the area below.
										  A complete list of the available analyses and their input specifications can be found on the <a href="help1.php">help section</a>. 
										  Complementary data useful for visualizing the results on the integrated genome browser can also be uploaded here. 
										  Please, take into account that large files can take some time to be uploaded depending on the size and the network connection. 
										  <a href="#">Contact us</a> in case of transfer errors.
                                        </div>

										<?php require "../htmlib/stepsup.inc.php"; ?>	
										
                                    </div>
                                </div>
								
								<div class="alert alert-danger display-hide alert-error-uploading">
                                  					Error, you tried to upload a wrong file.
								</div>

								<form action="/applib/processData.php" class="dropzone dropzone-file-area" id="my-dropzone" style="/*width: 500px;*/ font-size:24px; font-weight:600; margin: 50px 0;">
                                </form>

								<form class="down-form" action="javascript:;" method="post">
								<div class="alert alert-danger display-hide">
                                  Error downloading file, please, try again.
								</div>
                                <div class="form-group">
									<label>Download from web by URL</label>

										<div class="input-icon">
                                        	<i class="fa fa-download font-green"></i>
                                        	<input type="url" class="form-control" name="url"  placeholder="http://public/path/to/file"> 
										</div>
									<button class="btn green" type="submit" id="btn-down-remote" style="margin-top:20px;">Download</button>

									<div class="progress-bar-down progress display-hide" style="margin-top:20px;">
                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                    	    <span class="sr-only"> 20% Complete </span>
                                        </div>
                                    </div>	

                                </div>

								</form>

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
