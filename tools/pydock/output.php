<?php

require "../../phplib/genlibraries.php";
redirectOutside();

//$dirName = $_REQUEST['project'];
$dirName = basename(getAttr_fromGSFileId($_REQUEST['project'],'path'));

$path = '/files/'.$_SESSION['User']['id'].'/'.$dirName;

?>

<?php require "../../htmlib/header.inc.php"; ?>

<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white page-container-bg-solid">
  <div class="page-wrapper">

  <?php require "../../htmlib/top.inc.php"; ?>
  <?php require "../../htmlib/menu.inc.php"; ?>

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
                                  <span>Tools</span>
                                  <i class="fa fa-circle"></i>
                              </li>
                              <li>
                                  <span>Protein-DNA Docking</span>
                              </li>
                            </ul>
                        </div>
                        <!-- END PAGE BAR -->
                        <!-- BEGIN PAGE TITLE-->
                        <h1 class="page-title"> Results
                            <small>Protein-DNA Docking</small>
                        </h1>
                        <!-- END PAGE TITLE-->
                        <!-- END PAGE HEADER-->
                        <div class="row">
                  			    <div class="col-md-12">
                        				<p style="margin-top:0;">
										The compressed results file includes the PDB structures predicted by pyDockSAXS and their corresponding CRYSOL fit curves. Please, refer to the help section for further details.
                                </p>
							</div>
							<div class="col-md-12">
								<div class="note note-info">
								<h4>Download (compressed tar.gz file) <a href="../..<?php echo $path.'/'.$dirName.'.tgz'; ?>"><i class="fa fa-download" style=" margin-left:10px;"></i></a></h4>
								</div>
							</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                                <div class="portlet light portlet-fit bordered">
                                  <div class="portlet-title">
                                    <div class="caption">
                                        <i class="icon-share font-red-sunglo hide"></i>
                                        <span class="caption-subject font-dark bold uppercase">TABLE OF RESULTS</span>
									</div>
									<!--<div class="actions">
										<div class="btn-group">
											<a class="btn btn-sm green dropdown-toggle" href="javascript:;" data-toggle="dropdown"> Actions
												<i class="fa fa-angle-down"></i>
											</a>
											<ul class="dropdown-menu pull-right">
												<li>
													<a href="javascript:;">
														<i class="fa fa-file-pdf-o"></i> Download as a PDF </a>
												</li>
											</ul>
										</div>
									</div>-->
                                  </div>
									<div class="portlet-body">
										<div id="loading-datatable"><div id="loading-spinner">LOADING</div></div>

										<?php
                                        $row = 1;
                                        if (($handle = fopen("../..".$path."/result.csv", "r")) !== FALSE) {
                                            $htmlval = '<table class="table table-striped " id="tablePDNAD">';
											while ((($data = fgetcsv($handle, 1000, ",")) !== FALSE)/* && ($row <= 51)*/) {
                                                $num = count($data);
												if($row == 1) {
													$htmlval = $htmlval."<thead>";
													$col = 'th';
												}else{ 
													if($row == 2) $htmlval = $htmlval."<tbody>";
													$col = 'td';
												}
												$htmlval = $htmlval."<tr>";
                                                for ($c=0; $c < $num; $c++) {
                                                    if($data[$c] != "" || trim($data[$c]) != " "){
                                                        $htmlval = $htmlval. '<'.$col.'>'.$data[$c].'</'.$col.'>';
                                                    }else{
                                                        $htmlval = $htmlval. '<'.$col.'>&nbsp;</'.$col.'>';
                                                    }
												}
												if($row == 1) $htmlval = $htmlval."</tr></thead>";
												else $htmlval = $htmlval."</tr>";
                                                $row++;
                                            }
                                            $htmlval = $htmlval."</tbody></table>";
											fclose($handle);
                                        }else {
                                          $htmlval = "Error reading data file";
                                        }

										echo $htmlval;
										?>

					</div>
                                </div>
                                <!-- END EXAMPLE TABLE PORTLET-->
                            </div>
                            <div class="col-md-6">
                                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                                <div class="portlet light portlet-fit bordered">
                                  <div class="portlet-title">
                                    <div class="caption">
                                        <i class="icon-share font-red-sunglo hide"></i>
                                        <span class="caption-subject font-dark bold uppercase">3D VISUALIZATION (TOP 1 MODEL)</span>
                                    </div>
                                  </div>
                                    <div class="portlet-body">
										<div class="row">
											<div class="col-md-12">
                                            <script>
                                                document.addEventListener( "DOMContentLoaded", function(){
                                                    stage1 = new NGL.Stage( "viewport1", {backgroundColor:"#94A0B2"} );
													stage1.loadFile( "<?php echo $path; ?>/top_1.pdb", { defaultRepresentation: false } ).then( function( o ){

                                                      o.addRepresentation( "cartoon", {
                                                        color: "chainindex", aspectRatio: 4, scale: 1
                                                      } );
                                                      o.addRepresentation( "base", {
                                                        sele: "*", color: "resname"
                                                      } );
                                                      o.addRepresentation( "ball+stick", {
                                                        sele: "hetero and not(water or ion)", scale: 3, aspectRatio: 1.5
                                                      } );
                                                      o.centerView(!1);

                                                    } );
                                                } );
                                                function handleResize(){ if(typeof stage1 != 'undefined') stage1.handleResize(); }
                                                window.addEventListener( "resize", handleResize, false );
                                            </script>
                                            <div id="viewport1" style="width:100%; height:500px;"></div>
                                         </div>

										</div>
                                    </div>
                                </div>
                                <!-- END EXAMPLE TABLE PORTLET-->
                            </div>
                        </div>
                    </div>
                    <!-- END CONTENT BODY -->
                </div>
                <!-- END CONTENT -->

<?php 

require "../../htmlib/footer.inc.php"; 
require "../../htmlib/js.inc.php";

?>
