<?php

require "../phplib/genlibraries.php";
redirectOutside();
?>

<?php
  require "../htmlib/header.inc.php";

// compute data disk space

$usedDisk             = getUsedDiskSpace();
$diskLimit            = $_SESSION['User']['diskQuota']; // getDiskLimit();
$usedDiskPerc         = sprintf('%d', ($usedDisk / $diskLimit) * 100);

if ($usedDisk < $disklimit) {
	$_SESSION['accionsAllowed'] = "enabled";
} else {
	$_SESSION['accionsAllowed'] = "disabled";
	$usedDiskPerc=100;
}

//update workpace content (job and files)

$files = getFilesToDisplay(array('_id'=> $_SESSION['User']['dataDir']) );
$files = addTreeTableNodesToFiles($files);

?>

<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white page-container-bg-solid">
  <div class="page-wrapper">

  <?php
   require "../htmlib/top.inc.php"; 
   require "../htmlib/menu.inc.php";
  ?>

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
				  <span>Data Table</span>
			      </li>
			    </ul>
			</div>
			<!-- END PAGE BAR -->
			<!-- BEGIN PAGE TITLE-->
			<h1 class="page-title"> User Workspace
			    <small>manage data through the data table</small>
			</h1>
			<!-- END PAGE TITLE-->
			<!-- END PAGE HEADER-->

			<div class="row">
			    <div class="col-md-12">
				<p style="margin-top:0;">
				If you want apply a Tool to a file, please select it from the dropdown menu on the Tools column. If you need to apply a Tool
				to more than one file, check the selected files and they will be loaded in the <i>Run Tools</i> list at the bottom of the table.
				</p>
				<?php if($_SESSION['User']['Type'] == 100) { ?>
					<div class="alert alert-warning">
						Your request for a premium user account is being processed. In the meantime, you can use the platform as a common user.
					</div>	
				<?php }else if($_SESSION['User']['Type'] == 3) { ?>
					<div class="alert alert-info">
						As a guest user you have reduced functionalities in the platform.
					</div>
				<?php }

				if(isset($_SESSION['errorData'])) { ?>
					<div class="alert alert-warning">
					<?php foreach($_SESSION['errorData'] as $subTitle=>$txts){
						print "$subTitle<br/>";
						foreach($txts as $txt){
							print "<div style=\"margin-left:20px;\">$txt</div>";
						}
					}
					unset($_SESSION['errorData']);
					?>
					</div>

				<?php } ?>
				<!-- BEGIN EXAMPLE TABLE PORTLET -->
				<div class="portlet">
				    <div class="portlet-body">

					<div id="loading-datatable"><div id="loading-spinner">LOADING</div></div>

					<form name="gesdir" action="/workspace/workspace.php" method="post" enctype="multipart/form-data">
					    <input type="hidden" name="op"     value=""/>
					    <input type="hidden" name="userId" value="<?php echo $_SESSION['userId'];?>"/>

					    <?php
					    //print showFolder( array('_id'=> $_SESSION['User']['dataDir']) );
					    print printTable($files);
		   			    ?>


					</form>
					<!--<button class="btn green" type="submit" id="btn-run-files" style="margin-top:20px;" >Run Selected Files</button>-->
				    </div>
				</div>
				<!-- END EXAMPLE TABLE PORTLET-->


			    </div>
			</div>

			<div class="row">
			    <div class="col-md-12 col-sm-12">
				<div class="portlet light bordered">
				    <div class="portlet-title">
					<div class="caption">
					    <i class="icon-share font-dark hide"></i>
					    <span class="caption-subject font-dark bold uppercase">Run Tools</span>
					</div>
					<div class="actions" style="display:none!important;" id="btn-av-tools">
					    <div class="btn-group">
												<a class="btn btn-sm green" href="javascript:;" data-toggle="dropdown">
							<i class="fa fa-wrench"></i> Available Tools
						    <i class="fa fa-angle-down"></i>
						</a>	
						<ul class="dropdown-menu pull-right" role="menu">
						    <li>
								<a href="javascript:;" id="pdna-docking"><i class="fa fa-puzzle-piece"></i> Protein-DNA docking</a>
							</li>
							<li>
						    	<a href="javascript:;" id="nucleosome-dynamics"><i class="fa fa-motorcycle"></i> Nucleosome Dynamics</a>
						    </li>
						 </u>
					    </div>
					</div>
				    </div>
				    <div class="portlet-body">
					<div class="" data-always-visible="1" data-rail-visible="0">
					    <ul class="feeds" id="list-files-run-tools"></ul>
											<div id="desc-run-tools">In order to run the tools on the files, please select them clicking on the checkboxes from the table above.</div>
					</div>
					<div class="scroller-footer">
											<a class="btn btn-sm red pull-right display-hide" id="btn-rmv-all"  href="javascript:;">
					       	<i class="fa fa-trash"></i> Remove all files
					    </a>
					</div>
				    </div>
				</div>
			    </div>
			</div>
<?php
                                if(isset($_SESSION['errorData'])) { ?>
                                        <div class="alert alert-warning">
                                        <?php foreach($_SESSION['errorData'] as $subTitle=>$txts){
                                                print "$subTitle<br/>";
                                                foreach($txts as $txt){
                                                        print "<div style=\"margin-left:20px;\">$txt</div>";
                                                }
                                        }
                                        unset($_SESSION['errorData']);
                                        ?>
                                        </div>

                                <?php } ?>


						<!-- SUMMARY AND DISK QUOTA ROW -->


			<div class="row">
			    <div class="col-lg-6 col-xs-12 col-sm-12">
				<div class="portlet light bordered">
				    <div class="portlet-title">
					<div class="caption">
					    <i class="icon-share font-dark hide"></i>
					    <span class="caption-subject font-dark bold uppercase">SUMMARY</span>
					</div>
				    </div>
				    <div class="portlet-body">
					<div class="scroller" style="height: 204px;" data-always-visible="1" data-rail-visible="0">
					    <ul class="feeds">
						<li>
						    <div class="col1">
							<div class="cont">
							    <div class="cont-col1">
							      <div class="label label-sm label-danger">
								  <i class="fa fa-database"></i>
							      </div>
							    </div>
							    <div class="cont-col2">
								<div class="desc text-danger"> You are about to running out your disk space. </div>
							    </div>
							</div>
						    </div>
						    <div class="col2">
							<div class="date"> Just now </div>
						    </div>
						</li>
												<li>
						    <div class="col1">
							<div class="cont">
							    <div class="cont-col1">
							      <div class="label label-sm label-warning">
								  <i class="fa fa-history"></i>
							      </div>
							    </div>
							    <div class="cont-col2">
								<div class="desc"> The file <span class="text-warning">Something.bam</span> is currently running. </div>
							    </div>
							</div>
						    </div>
						    <div class="col2">
							<div class="date"> Just now </div>
						    </div>
						</li>

						<li>
						    <a href="javascript:;" class="text-danger">
							<div class="col1">
							    <div class="cont">
								<div class="cont-col1">
								  <div class="label label-sm label-danger">
								      <i class="fa fa-exclamation-circle"></i>
								  </div>
								</div>
								<div class="cont-col2">
								    <div class="desc"> You must fill in the metadata of the file Something.txt. </div>
								</div>
							    </div>
							</div>
							<div class="col2">
							    <div class="date"> 20 mins </div>
							</div>
						    </a>
						</li>
						<li>
						    <div class="col1">
							<div class="cont">
							    <div class="cont-col1">
							      <div class="label label-sm label-info">
								  <i class="fa fa-check"></i>
							      </div>
							    </div>
							    <div class="cont-col2">
								<div class="desc"> The file <span class="text-info">Something.bam</span> has finished processing. </div>
							    </div>
							</div>
						    </div>
						    <div class="col2">
							<div class="date"> 24 mins </div>
						    </div>
						</li>
						<li>
						    <div class="col1">
							<div class="cont">
							    <div class="cont-col1">
							      <div class="label label-sm label-warning">
								  <i class="fa fa-history"></i>
							      </div>
							    </div>
							    <div class="cont-col2">
								<div class="desc"> The file <span class="text-warning">Something.bam</span> is currently running. </div>
							    </div>
							</div>
						    </div>
						    <div class="col2">
							<div class="date"> Just now </div>
						    </div>
						</li>
						<li>
						    <a href="javascript:;" class="text-danger">
							<div class="col1">
							    <div class="cont">
								<div class="cont-col1">
								  <div class="label label-sm label-danger">
								      <i class="fa fa-exclamation-circle"></i>
								  </div>
								</div>
								<div class="cont-col2">
								    <div class="desc"> You must fill in the metadata of the file Something.txt. </div>
								</div>
							    </div>
							</div>
							<div class="col2">
							    <div class="date"> 20 mins </div>
							</div>
						    </a>
						</li>
						<li>
						    <div class="col1">
							<div class="cont">
							    <div class="cont-col1">
							      <div class="label label-sm label-info">
								  <i class="fa fa-check"></i>
							      </div>
							    </div>
							    <div class="cont-col2">
								<div class="desc"> The file <span class="text-info">Something.bam</span> has finished processing. </div>
							    </div>
							</div>
						    </div>
						    <div class="col2">
							<div class="date"> 24 mins </div>
						    </div>
						</li>
						<li>
						    <div class="col1">
							<div class="cont">
							    <div class="cont-col1">
							      <div class="label label-sm label-warning">
								  <i class="fa fa-history"></i>
							      </div>
							    </div>
							    <div class="cont-col2">
								<div class="desc"> The file <span class="text-warning">Something.bam</span> is currently running. </div>
							    </div>
							</div>
						    </div>
						    <div class="col2">
							<div class="date"> Just now </div>
						    </div>
						</li>
						<li>
						    <a href="javascript:;" class="text-danger">
							<div class="col1">
							    <div class="cont">
								<div class="cont-col1">
								  <div class="label label-sm label-danger">
								      <i class="fa fa-exclamation-circle"></i>
								  </div>
								</div>
								<div class="cont-col2">
								    <div class="desc"> You must fill in the metadata of the file Something.txt. </div>
								</div>
							    </div>
							</div>
							<div class="col2">
							    <div class="date"> 20 mins </div>
							</div>
						    </a>
						</li>
						<li>
						    <div class="col1">
							<div class="cont">
							    <div class="cont-col1">
							      <div class="label label-sm label-info">
								  <i class="fa fa-check"></i>
							      </div>
							    </div>
							    <div class="cont-col2">
								<div class="desc"> The file <span class="text-info">Something.bam</span> has finished processing. </div>
							    </div>
							</div>
						    </div>
						    <div class="col2">
							<div class="date"> 24 mins </div>
						    </div>
						</li>
						<li>
						    <div class="col1">
							<div class="cont">
							    <div class="cont-col1">
							      <div class="label label-sm label-warning">
								  <i class="fa fa-history"></i>
							      </div>
							    </div>
							    <div class="cont-col2">
								<div class="desc"> The file <span class="text-warning">Something.bam</span> is currently running. </div>
							    </div>
							</div>
						    </div>
						    <div class="col2">
							<div class="date"> Just now </div>
						    </div>
						</li>
						<li>
						    <a href="javascript:;" class="text-danger">
							<div class="col1">
							    <div class="cont">
								<div class="cont-col1">
								  <div class="label label-sm label-danger">
								      <i class="fa fa-exclamation-circle"></i>
								  </div>
								</div>
								<div class="cont-col2">
								    <div class="desc"> You must fill in the metadata of the file Something.txt. </div>
								</div>
							    </div>
							</div>
							<div class="col2">
							    <div class="date"> 20 mins </div>
							</div>
						    </a>
						</li>
						<li>
						    <div class="col1">
							<div class="cont">
							    <div class="cont-col1">
							      <div class="label label-sm label-info">
								  <i class="fa fa-check"></i>
							      </div>
							    </div>
							    <div class="cont-col2">
								<div class="desc"> The file <span class="text-info">Something.bam</span> has finished processing. </div>
							    </div>
							</div>
						    </div>
						    <div class="col2">
							<div class="date"> 24 mins </div>
						    </div>
						</li>
					    </ul>
					</div>

				    </div>
				</div>
			    </div>
			    <div class="col-lg-6 col-xs-12 col-sm-12">
				<div class="portlet light tasks-widget bordered">
				    <div class="portlet-title">
					<div class="caption">
					    <i class="icon-share font-dark hide"></i>
					    <span class="caption-subject font-dark bold uppercase">DISK USE</span>
					</div>

				    </div>
				    <div class="portlet-body">
					
					<div id="loading-knob"><div id="loading-spinner">LOADING</div></div>

					<div id="disk-home">
					  	<input class="knob" data-fgColor="#006b8f" data-bgColor="#eeeeee" readonly value="<?php echo $usedDiskPerc;?>" />

				       	<div id="info-disk-home">
					 		You are using <strong><?php echo formatSize($usedDisk);?></strong> from your <strong><?php echo formatSize($diskLimit);?></strong> of disk quota.
					  	</div>
					</div>

				    </div>
				</div>
			    </div>
			</div>
						<!-- END SUMMARY AND DISK QUOTA ROW -->					

		    </div>
		    <!-- END CONTENT BODY -->
		</div>
		<!-- END CONTENT -->

				<div class="modal fade bs-modal-sm" id="myModal1" tabindex="-1" role="basic" aria-hidden="true">
		    <div class="modal-dialog">
			<div class="modal-content">
			    <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">Warning!</h4>
			    </div>
			    <div class="modal-body"> You have more than one file selected. If you go ahead, this tool will just be applied to the selected file. </div>
			    <div class="modal-footer">
				<button type="button" class="btn dark btn-outline" data-dismiss="modal">Cancel</button>
								<button type="button" class="btn green btn-modal-ok">Accept</button>
			    </div>
			</div>
			<!-- /.modal-content -->
		    </div>
		    <!-- /.modal-dialog -->
		</div>

		<div class="modal fade bs-modal" id="modalNGL" tabindex="-1" role="basic" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                <h4 class="modal-title"></h4>
                            </div>
                            <div class="modal-body">
                              <div id="viewport" style="width:100%; height:500px;background:#ddd;"></div>
                             </div>
                            <div class="modal-footer">
                                <button type="button" class="btn dark btn-outline" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
				</div>

		<div class="modal fade bs-modal" id="modalDelete" tabindex="-1" role="basic" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                <h4 class="modal-title">Warning!</h4>
                            </div>
                            <div class="modal-body">Are you sure you want to delete the selected file?
                             </div>
                            <div class="modal-footer">
                                <button type="button" class="btn dark btn-outline" data-dismiss="modal">Cancel</button>
								<button type="button" class="btn red btn-modal-del">Delete</button>
                            </div>
                        </div>
                    </div>
				</div>

		<div class="modal fade bs-modal" id="modalAnalysis" tabindex="-1" role="basic" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                <h4 class="modal-title">Execution Summary</h4>
                            </div>
							<div class="modal-body table-responsive"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn dark btn-outline" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>


				


<?php 

require "../htmlib/footer.inc.php"; 
require "../htmlib/js.inc.php";

?>
