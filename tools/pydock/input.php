<?php

require "../../phplib/genlibraries.php";
redirectOutside();

// check inputs
if (!isset($_REQUEST['fn']) && !isset($_REQUEST['rerunDir'])){
	$_SESSION['errorData']['Error'][]="Please, before running PyDock, select two files of format PDB for running this tool";
	redirect('/workspace/');
}

$rerunParams  = Array();
$inPaths = Array();

if ($_REQUEST['rerunDir']){
	$dirMeta = $GLOBALS['filesMetaCol']->findOne(array('_id' => $_REQUEST['rerunDir'])); 
	if (!is_array($dirMeta['inPaths']) && !isset($dirMeta['raw_params'])){
		$_SESSION['errorData']['Error'][]="Cannot rerun job ".$_REQUEST['rerunDir'].". Some folder metadata is missing.";
		redirect('/workspace/');
	}
	$inPaths = $dirMeta['inPaths'];
	$rerunParams = $dirMeta['raw_params'];
	$_REQUEST['fn']= array_map("getGSFileId_fromPath",$dirMeta['inPaths']);
}else{
	if (!is_array($_REQUEST['fn']))
		$_REQUEST['fn'][]=$_REQUEST['fn'];

	foreach($_REQUEST['fn'] as $fn){
		array_push($inPaths,getAttr_fromGSFileId($fn,'path'));
	}
}

if(count($_REQUEST['fn'])!=2){
	$_SESSION['errorData']['Error'][]="Please, select two files of format PDB for running this tool";
	redirect('/workspace/');
}

// set default values
$def = Array(
    'pyDock'=> Array(
	'receptor'   => $_REQUEST['fn'][0],
	'ligand'     => $_REQUEST['fn'][1],
	'models'     => 54,
	'scoring'    => "PyDockDNA",
	'description'=> "",
	'project'    => ""
    )
);

// default project dir
$dirNum="000";
$reObj = new MongoRegex("/^".$_SESSION['User']['id']."\\/run\d\d\d$/i");
$prevs  = $GLOBALS['filesCol']->find(array('path' => $reObj, 'owner' => $_SESSION['User']['id']));
if ($prevs->count() > 0){
        $prevs->sort(array('_id' => -1));
        $prevs->next();
        $previous = $prevs->current();
        if (preg_match('/(\d+)$/',$previous["path"],$m) ){
            $dirNum= sprintf("%03d",$m[1]+1);
        }
}
$dirName="run".$dirNum;
$prevs  = $GLOBALS['filesCol']->find(array('path' => $GLOBALS['dataDir']."/".$_SESSION['User']['dataDir']."/$dirName", 'owner' => $_SESSION['User']['id']));
if ($prevs->count() > 0){
    $dirName="run".rand(100, 999);
}
$def['pyDock']['project']= $dirName;

if (count($rerunParams)){
	$def_tmp=array_merge($def,$rerunParams);
	$def = $def_tmp;
}


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
                        <h1 class="page-title"> Protein-DNA Docking
                        </h1>
                        <!-- END PAGE TITLE-->
                        <!-- END PAGE HEADER-->
                        <div class="row">
							<div class="col-md-12">
							<?php if(isset($_SESSION['errorData'])) { ?>
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

                              <!-- BEGIN PORTLET 0: INPUTS -->
                              <div class="portlet box blue-oleo">
                                  <div class="portlet-title">
                                      <div class="caption">
                                        <div style="float:left;margin-right:20px;"> <i class="fa fa-sign-in" ></i> Inputs</div>
                                      </div>
                                  </div>
                                  <div class="portlet-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="feeds" id="list-files-run-tools">
                                            <li class="tool-12 tool-list-item">
                                              <div class="col1">
                                                <div class="cont">
                                                  <div class="cont-col1">
                                                    <div class="label label-sm label-info">
                                                      <i class="fa fa-file"></i>
                                                    </div>
                                                  </div>
                                                  <div class="cont-col2">
													<div class="desc">
														<?php $p = explode("/", $inPaths[0]); ?>
														<span class="text-info" style="font-weight:bold;"><?php echo $p[1]; ?>  /</span> <?php echo $p[2]; ?>
													</div>
												  </div>
												</div>
											  </div>
											</li>
											<li style="background:none;">
												<div>
													Please select:&nbsp;&nbsp;&nbsp; 
													<input type="checkbox" checked class="make-switch switch-block" id="switch-pdna1" 
													data-size="normal" data-on-text="Receptor" data-off-text="Ligand" data-on-color="info" 
													data-off-color="info" data-label-text="" />
												</div>
											</li>
                                          </ul>
                                      </div>
                                      <div class="col-md-6">
                                          <ul class="feeds" id="list-files-run-tools">
                                          <li class="tool-12 tool-list-item">
                                            <div class="col1">
                                              <div class="cont">
                                                <div class="cont-col1">
                                                  <div class="label label-sm label-info">
                                                    <i class="fa fa-file"></i>
                                                  </div>
                                                </div>
                                                <div class="cont-col2">
												  <div class="desc">
													<?php $p = explode("/", $inPaths[1]); ?> 
													<span class="text-info" style="font-weight:bold;"><?php echo $p[1]; ?>  /</span> <?php echo $p[2]; ?>
												  </div>
                                                </div>
                                              </div>
                                            </div>
										  </li>
										  <li style="background:none;">
												<div>
													Please select:&nbsp;&nbsp;&nbsp; 
													<input type="checkbox" class="make-switch switch-block" id="switch-pdna2" 
													data-size="normal" data-on-text="Receptor" data-off-text="Ligand" data-on-color="info" 
													data-off-color="info" data-label-text="" />
												</div>
											</li>
                                        </ul>
                                    </div>
                                    </div>
                                    <div class="row"><div class="col-md-12">&nbsp;</div></div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <script>
                                                document.addEventListener( "DOMContentLoaded", function(){
                                                    stage0 = new NGL.Stage( "viewport0", {backgroundColor:"#94A0B2"} );
													stage0.loadFile( "/files/<?php echo $inPaths[0]; ?>", { defaultRepresentation: false } ).then( function( o ){

                                                      o.addRepresentation( "cartoon", {
                                                        color: "chainname", aspectRatio: 4, scale: 1
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
                                                function handleResize(){ if(typeof stage0 != 'undefined') stage0.handleResize(); }
                                                window.addEventListener( "resize", handleResize, false );
											</script>
											<div id="viewport0" style="width:100%; height:500px;"></div>
                                          </div>
                                          <div class="col-md-6">
                                            <script>
                                                document.addEventListener( "DOMContentLoaded", function(){
                                                    stage1 = new NGL.Stage( "viewport1", {backgroundColor:"#94A0B2"} );
                                                    stage1.loadFile( "/files/<?php echo $inPaths[1]; ?>", { defaultRepresentation: false } ).then( function( o ){

                                                      o.addRepresentation( "cartoon", {
                                                        color: "chainname", aspectRatio: 4, scale: 1
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
                              <!-- END PORTLET 0: INPUTS -->
							  <form action="#" class="horizontal-form" id="pdna-docking">
							  <input type="hidden" name="tool" value="pydock" />
							  <input type="hidden" id="file-input1" name="fn[]" value="<?php echo $_REQUEST['fn'][0]; ?>" />
							  <input type="hidden" id="file-input2" name="fn[]" value="<?php echo $_REQUEST['fn'][1]; ?>" />
							  <input type="hidden" id="param-receptor" name="params[pyDock][receptor]" value="<?php echo $_REQUEST['fn'][0]; ?>" />
							  <input type="hidden" id="param-ligand" name="params[pyDock][ligand]" value="<?php echo $_REQUEST['fn'][1]; ?>" />
						  
                              <!-- BEGIN PORTLET 1: ANALYZES -->
                              <div class="portlet box blue-oleo">
                                  <div class="portlet-title">
                                      <div class="caption">
                                        <div style="float:left;margin-right:20px;"> <i class="fa fa-check-square-o" ></i> Analyzes</div>
                                      </div>
                                  </div>
                                  <div class="portlet-body form">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Project name</label>
                                                    <input type="text" name="project" id="dirName" class="form-control" value="<?php echo $dirName;?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Description</label>
                                                    <textarea id="description" name="description" class="form-control" style="height:120px;" placeholder="Write a short description here..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                  </div>
                              </div>
                              <!-- END PORTLET 1: ANALYZES -->
                              <!-- BEGIN PORTLET 2: NUCLER -->
                              <div class="portlet box blue form-block-header" id="form-block-header1">
                                  <div class="portlet-title">
                                      <div class="caption">
                                        PyDockDNA options
                                      </div>
                                  </div>
                                  <div class="portlet-body form form-block" id="form-block1">
                                      <div class="form-body">
                                          <div class="row">
                                            <!--<div class="col-md-12">
                                              Description
                                            </div>-->
                                          </div>
                                          <h4>&nbsp;</h4>
                                          <div class="row">
                                              <div class="col-md-6">
												  <div class="form-group">
                                                      <label class="control-label">Structures to Model <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>The number of structures in PDB format to be modeled by the method.</p>"></i></label>
                                                      <select class="form-control form-field-enabled valid" name="params[pyDock][models]" aria-invalid="false">
                                                          <option value="1" selected="">1</option>
                                                          <option value="5">5</option>
                                                          <option value="10">10</option>
                                                          <option value="50">50</option>
                                                      </select>
                                                  </div>
                                              </div>
                                              <div class="col-md-6">
												<div class="form-group">
                                                    <label class="control-label">Scoring <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Available energetic scoring functions.</p>"></i></label>
                                                    <select class="form-control form-field-enabled valid" name="params[pyDock][scoring]" aria-invalid="false">
                                                        <option value="PyDockDNA" selected="">PyDock DNA</option>
                                                    </select>
                                                </div>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <!-- END PORTLET 2: NUCLER -->
                              <div class="alert alert-danger err-nd display-hide">
                                  <strong>Error!</strong> You forgot to fill out some mandatory fields, please check them before submit the form.
                              </div>

                              <div class="alert alert-warning warn-nd display-hide">
                                  <strong>Warning!</strong> At least one analysis should be selected.
                              </div>

                              <div class="form-actions">
                                  <button type="submit" class="btn blue" style="float:right;">
                                      <i class="fa fa-check"></i> Compute</button>
                              </div>
                              </form>
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
