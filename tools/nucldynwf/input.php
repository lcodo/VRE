<?php

require "../../phplib/genlibraries.php";
redirectOutside();

// check inputs
if (!isset($_REQUEST['fn'])){
	$_SESSION['errorData']['Error'][]="Please, before running Nucleosome Dynamics, select at least one file of format BAM for running this tool";
	redirect('/workspace/');
}

$rerunParams  = Array();
$inPaths = Array();

if (!is_array($_REQUEST['fn']))
	$_REQUEST['fn'][]=$_REQUEST['fn'];

foreach($_REQUEST['fn'] as $fn){
	array_push($inPaths,getAttr_fromGSFileId($fn,'path'));
}


if(count($_REQUEST['fn']) == 0){
	$_SESSION['errorData']['Error'][]="Please, select at least one file of format BAM for running this tool";
	redirect('/workspace/');
}


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
                                  <span>Nucleosome Dynamics</span>
                              </li>
                            </ul>
                        </div>
                        <!-- END PAGE BAR -->
                        <!-- BEGIN PAGE TITLE-->
                        <h1 class="page-title"> Nucleosome Dynamics
                        </h1>
                        <!-- END PAGE TITLE-->
                        <!-- END PAGE HEADER-->
                        <div class="row">
                            <div class="col-md-12">
                              <!-- BEGIN PORTLET 0: INPUTS -->
                              <div class="portlet box blue-oleo">
                                  <div class="portlet-title">
                                      <div class="caption">
                                        <div style="float:left;margin-right:20px;"> <i class="fa fa-sign-in" ></i> Inputs</div>
                                      </div>
                                  </div>
                                  <div class="portlet-body">
									<ul class="feeds" id="list-files-run-tools">
									<?php foreach ($inPaths as $path) { ?>
									<li class="tool-122 tool-list-item">
                                        <div class="col1">
                                          <div class="cont">
                                            <div class="cont-col1">
                                              <div class="label label-sm label-info">
                                                <i class="fa fa-file"></i>
                                              </div>
                                            </div>
                                            <div class="cont-col2">
											  <div class="desc">
											  <?php $p = explode("/", $path); ?>
											  <span class="text-info" style="font-weight:bold;"><?php echo $p[1]; ?>  /</span> <?php echo $p[2]; ?>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </li>
									<?php } ?>
                                    </ul>
                                  </div>
                              </div>
                              <!-- END PORTLET 0: INPUTS -->
                              <form action="#" class="horizontal-form" id="nucleosome-dynamics">
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
                                                    <input type="text" name="dirName" id="dirName" class="form-control" value="<?php echo $dirName;?>">
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
                                        <input type="checkbox" class="make-switch switch-block" id="switch-block1" data-size="mini">
                                        <div style="float:right;margin-left:20px;"> NucleR</div>
                                      </div>
                                      <div class="tools">
                                          <a href="javascript:;" class="collapse"></a>
                                      </div>
                                  </div>
                                  <div class="portlet-body form form-block" id="form-block1">
                                      <div class="form-body">
                                          <div class="row">
                                            <div class="col-md-12">
                                              NucleR finds nucleosome positions from MNase experiments using Fourier transform filtering and classifies nucleosomes according to their fuzziness (<a href="javascript:;" target="_blank">more information</a>)
                                            </div>
                                          </div>
                                          <h4>&nbsp;</h4>
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Width <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Size of each nucleosome, in bp, to be considered by NucleR.</p>"></i></label>
                                                      <input type="number" step="any" name="params[nucleR][width]" id="params_nuclr_width" class="form-control form-field-enabled" value="147">
                                                  </div>
                                              </div>
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Minimum Overlap <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Minimum overlap between two nucleosomes for merging them.</p>"></i></label>
                                                      <input type="number" step="any" name="params[nucleR][minoverlap]" id="params_nuclr_minoverlap" class="form-control form-field-enabled" value="80">
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Dyad Length <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Length of the reads that should be used for nucleosome calling to define the dyad of the nucleosomes keeping the given number of bases around the center of the read.</p>"></i></label>
                                                      <input type="number" step="any" name="params[nucleR][dyad__length]" id="params_nuclr_dyad_len" class="form-control form-field-enabled" value="50">
                                                  </div>
                                              </div>
                                              <div class="col-md-6">
                                                <div class="form-group">
                                                <label class="control-label">Background level <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Minimum number of reads (Coverage) to call a nucleosome. Can be given as a percentage (i.e., '25%' means that the peaks with coverage in the 1st quartile of data won't be considered); or as an absolute coverage value (i.e., '20' means that the peaks with less than 20 reads per million of mapped reads won't be considered). Default = 35%.</p>"></i></label>
                                                <div class="input-group">
                                                  <div class="input-icon right" id="nucr-perc">
                                                      <i class="fa fa-percent"></i>
                                                      <input type="number" step="1" min="0" max="100" class="form-control form-field-enabled" name="params[nucleR][thresholdPercentage]" id="params_nuclr_thperc" value="35" >
                                                  </div>
                                                  <div id="nucr-absval" class="display-hide">
                                                      <input type="number" step="any" class="form-control form-field-disabled" name="params[nucleR][thresholdValue]" id="params_nuclr_thval" value="">
                                                  </div>
                                                  <div class="input-group-btn" id="swbglev">
                                                      <input type="checkbox" class="make-switch" id="switch-bglevel" data-size="normal" data-on-text="Abs. Value" data-off-text="Percentage" data-on-color="info" data-off-color="info" data-label-text="Abs. Value">
                                                  </div>
                                                </div>
                                                <span class="help-block-form display-block" id="lab-nucr-perc"> Percentage <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Minimum number of reads (Coverage) to call a nucleosome given as percentage (i.e., '25%' means that the peaks with coverage in the 1st quartile of data won't be considered).</p>"></i></span>
                                                <span class="help-block-form display-hide" id="lab-nucr-absval"> Absolute value <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Minimum number of reads (Coverage) to call a nucleosome (i.e., '20' means that the peaks with less than 20 reads per million of mapped reads won't be considered).</p>"></i></span>
                                                </div>
                                              </div>
                                          </div>
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Height Threshold  <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Height threshold (between 0 and 1) to classify a nucleosome as fuzzy (class=F) or well-positioned ( class=W) according to the number of reads at the dyad. Nucleosomes below this value will be defined as fuzzy. Default = 0.4.</p>"></i></label>
                                                      <input type="number" step="0.1" min="0" max="1" name="params[nucleR][hthresh]" id="params_nuclr_hthresh" class="form-control form-field-enabled" value="0.4">
                                                  </div>
                                              </div>
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Width Threshold <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Width threshold (between 0 and 1) to classify a nucleosome as fuzzy (class=F) or well-positioned (class=W) according to the dispersion of the reads around the dyad. Nucleosomes below this value will be defined as fuzzy. Default = 0.6.</p>"></i></label>
                                                      <input type="number" step="0.1" min="0" max="1" name="params[nucleR][wthresh]" id="params_nuclr_wthresh" class="form-control form-field-enabled" value="0.6">
                                                  </div>
                                              </div>
                                          </div>
                                          <h4 class="form-section">Advanced Settings</h4>
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Coverage Smoothing  <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Parameter used in the smoothing when Fourier transformation is applied. Number of components to select with respect to the total size of the sample. Allowed values are numeric (in range 0:1) for manual setting, or auto for automatic detection.</p>"></i></label>
                                                      <input type="number" step="0.01" min="0" max="1" name="params[nucleR][pcKeepComp]" id="params_nuclr_pcKeepComp" class="form-control form-field-enabled" value="0.02">
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <!-- END PORTLET 2: NUCLER -->
                              <!-- BEGIN PORTLET 3: NUCLEOSOME DYNAMICS -->
                              <div class="portlet box blue form-block-header" id="form-block-header2">
                                  <div class="portlet-title">
                                      <div class="caption">
                                        <input type="checkbox" class="make-switch switch-block" id="switch-block2" data-size="mini">
                                        <div style="float:right;margin-left:20px;"> Nucleosome Dynamics</div>
                                      </div>
                                      <div class="tools">
                                          <a href="javascript:;" class="collapse"></a>
                                      </div>
                                  </div>
                                  <div class="portlet-body form form-block" id="form-block2">
                                      <div class="form-body">
                                          <div class="row">
                                            <div class="col-md-12">
                                              Detection of local changes in the position of nucleosomes at the single read level observed between two reference nucleosome maps (<a href="javascript:;" target="_blank">more information</a>)
                                            </div>
                                          </div>
                                          <h4>&nbsp;</h4>
                                          <h4 class="form-section">MNase data category <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'><strong>MNase data category:</strong><br>Definition of the two conditions to be compared with Nucleosome dynamics <br>- <u>Condition 1 (C1)</u>: MNase data used to define the initial state  when comparing nucleosome positioning (REQUIRED).<br>- <u>Condition 2 (C2)</u>: MNase data used to define the final state  when comparing nucleosome positioning (REQUIRED).<br>- <u>Not to be included</u>: Ignores this BAM for the current analysis</p>" style="font-size:14px;"></i></h4>
                                          <div class="row">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">TSS_V136_filter.pdb</label>
                                                <div class="col-md-9">
                                                  <select class="form-control form-field-enabled" name="params[nucDyn][category][]">
                                                      <option value="p1" selected>Condition 1 - reference (C1)</option>
                                                      <option value="p2">Condition 2 (C2)</option>
                                                      <option value="0">Not to be included</option>
                                                  </select>
                                                </div>
                                            </div>
                                          </div>
                                          <div class="row">&nbsp;</div>
                                          <div class="row">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">1DIZ_bound-DNA.pdb_3</label>
                                                <div class="col-md-9">
                                                  <select class="form-control form-field-enabled" name="params[nucDyn][category][]">
                                                      <option value="p1">Condition 1 - reference (C1)</option>
                                                      <option value="p2" selected="">Condition 2 (C2)</option>
                                                      <option value="0">Not to be included</option>
                                                  </select>
                                                </div>
                                            </div>
                                          </div>
                                          <h6 class="form-section"></h6>
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Genomic Range <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Genomic region to be analyzed: whole genome ('all'), entire chromosome (chromosome name i.e. 'chrX'), or region of a chromosome ('chromosomeName:start-end).</p>"></i></label>
                                                      <input type="text" name="params[nucDyn][range]" id="params_nucdyn_range" class="form-control form-field-enabled" value="All">
                                                  </div>
                                              </div>
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Maximum Diff <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Maximum distance between the dyads of two reads that allows them to still be considered a <i>shift</i>. If unspecified but <i>readSize</i> is specified, it will be set to the half of readSize. If neither of them is specified, it will be set to 70</p>"></i></label>
                                                      <input type="number" step="any" name="params[nucDyn][maxDiff]" id="params_nucdyn_maxdiff" class="form-control form-field-enabled" value="70">
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Maximum Length <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="Used in the preliminar filtering. Reads longer than this number will be filtered out"></i></label>
                                                      <input type="number" step="any" name="params[nucDyn][maxLen]" id="params_nucdyn_maxlen" class="form-control form-field-enabled" value="140">
                                                  </div>
                                              </div>
                                              <div class="col-md-6">

                                              </div>
                                          </div>
                                          <div class="row">
                                            <div class="col-md-6">
                                              <div class="form-group">
                                              <label class="control-label">Equal Size <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>If set to TRUE, all sets will be set to the same length, conserving their original dyad position</p>"></i></label>
                                              <div class="input-group">
                                                <div id="nucd-roundp">
                                                  <input type="number" step="any" class="form-control form-field-enabled" id="params_nucdyn_rpow" name="params[nucDyn][roundPow]" value="5" >
                                                </div>
                                                <div id="nucd-reads" class="display-hide">
                                                    <input type="number" step="any" class="form-control form-field-disabled" id="params_nucdyn_rsize" name="params[nucDyn][readSize]" value="140">
                                                </div>
                                                <div class="input-group-btn">
                                                    <input type="checkbox" class="make-switch" id="switch-eqsize" data-size="normal" data-on-text="TRUE" data-off-text="FALSE" data-on-color="info" data-off-color="default">
                                                </div>
                                              </div>
                                              <span class="help-block-form display-block" id="lab-nucd-roundp"> Round Power  <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>When <i>equalSize</i> is <i>FALSE</i>, the start and end of each read will be rounded to a power of this number to allow a more granular analysis.</p>"></i></span>
                                              <span class="help-block-form display-hide" id="lab-nucd-reads">  Read SIze  <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Length to which all reads will be set in case 'equalSize' is TRUE</p>"></i></span>
                                              </div>
                                            </div>
                                            <div class="col-md-6">
                                              <div class="form-group">
                                              <label class="control-label">Combined <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'> If TRUE, nearby hotspots will be combined. Otherwise, all the information about detected shifts and changes in coverage will be returned without further processing.</p>"></i></label>
                                              <div class="input-group">
                                                <div id="nucd-samem">
                                                  <input type="number" step="any" class="form-control form-field-enabled" id="params_nucdyn_smag" name="params[nucDyn][same__magnitude]" value="2" checked>
                                                </div>
                                                <div id="nucd-samemf" class="display-hide">
                                                  <input class="form-control form-field-disabled">
                                                </div>
                                                <div class="input-group-btn">
                                                    <input type="checkbox" class="make-switch" id="switch-combin" data-size="normal" checked data-on-text="TRUE" data-off-text="FALSE" data-on-color="info" data-off-color="default">
                                                </div>
                                              </div>
                                              <span class="help-block-form display-block" id="lab-nucd-samem"> Same Magnitude  <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Only used if combined=TRUE. When combining two hotspots, this value is the maximum ratio between two spots to be considered with the same magnitude. Two hot spots with the same magnitude can be combined, but a large hotspot will not be merged with a much smaller one.</p>"></i></span>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Shift minimum num. reads <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Minimum number of reads in a 'SHIFT +' or a 'SHIFT -' hotspot</p>"></i></label>
                                                      <input type="number" step="any" name="params[nucDyn][shift_min_nreads]" id="params_nucdyn_shiftmn" class="form-control form-field-enabled" value="3">
                                                  </div>
                                              </div>
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Shifts threshold <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Minimum score for a 'SHIFT +' or a 'SHIFT -' hotspot</p>"></i></label>
                                                      <input type="number" step="0.001" name="params[nucDyn][shift_threshold]" id="params_nucdyn_shiftth" class="form-control form-field-enabled" value="0.075">
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Indels minimum num. reads <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Minimum number of reads in an 'INCLUSION +' or a 'DELETION -' hotspot</p>"></i></label>
                                                      <input type="number" step="any" name="params[nucDyn][indel_min_nreads]" id="params_nucdyn_indelmn" class="form-control form-field-enabled" value="15">
                                                  </div>
                                              </div>
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Indels threshold <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Minimum score for an 'INCLUSION' or a 'DELETION' hotspot</p>"></i></label>
                                                      <input type="number" step="0.1" name="params[nucDyn][indel_threshold]" id="params_nucdyn_indeth" class="form-control form-field-enabled" value="0.5">
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <!-- END PORTLET 3: NUCLEOSOME DYNAMICS -->
                              <!-- BEGIN PORTLET 4: NUCLEOSOME FREE REGIONS -->
                              <div class="portlet box blue form-block-header" id="form-block-header3">
                                  <div class="portlet-title">
                                      <div class="caption">
                                        <input type="checkbox" class="make-switch switch-block" id="switch-block3" data-size="mini">
                                        <div style="float:right;margin-left:20px;"> Nucleosome Free Regions</div>
                                      </div>
                                      <div class="tools">
                                          <a href="javascript:;" class="collapse"></a>
                                      </div>
                                  </div>
                                  <div class="portlet-body form form-block" id="form-block3">
                                      <div class="form-body">
                                          <div class="row">
                                            <div class="col-md-12">
                                              Nucleosome-free regions (NFR) are regions depleted of nucleosomes and larger than an average linker fragment. (<a href="javascript:;" target="_blank">more information</a>)
                                            </div>
                                          </div>
                                          <h4>&nbsp;</h4>
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Minimum Width <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Minimum width for a linker fragment to be considered a nucleosome-free region</p>"></i></label>
                                                      <input type="number" step="any" name="params[NFR][minwidth]" id="params_nfr_minw" class="form-control form-field-enabled" value="110">
                                                  </div>
                                              </div>
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Maximum width <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Maximum width for a linker fragment to be considered a nucleosome-free region</p>"></i></label>
                                                      <input type="number" step="any" name="params[NFR][threshold]" id="params_nfr_threshold" class="form-control form-field-enabled" value="400">
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <!-- END PORTLET 4: NUCLEOSOME FREE REGIONS -->
                              <!-- BEGIN PORTLET 5: NUCLEOSOME PHASING -->
                              <div class="portlet box blue form-block-header" id="form-block-header4">
                                  <div class="portlet-title">
                                      <div class="caption">
                                        <input type="checkbox" class="make-switch switch-block" id="switch-block4" data-size="mini">
                                        <div style="float:right;margin-left:20px;"> Nucleosome Phasing</div>
                                      </div>
                                      <div class="tools">
                                          <a href="javascript:;" class="collapse"></a>
                                      </div>
                                  </div>
                                  <div class="portlet-body form form-block" id="form-block4">
                                      <div class="form-body">
                                          <div class="row">
                                            <div class="col-md-12">
                                              Nucleosome phasing along a given gene between the first and last nucleosome (<a href="javascript:;" target="_blank">more information</a>)
                                            </div>
                                          </div>
                                          <h4>&nbsp;</h4>
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Period <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Average distance between two consecutive nucleosomes (repeat length).</p>"></i></label>
                                                      <input type="number" step="any" name="params[periodicity][periodicity]" id="params_perio_perio" class="form-control form-field-enabled" value="165">
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <!-- END PORTLET 5: NUCLEOSOME PHASING -->
                              <!-- BEGIN PORTLET 6: TSS CLASSIFICATION -->
                              <div class="portlet box blue form-block-header" id="form-block-header5">
                                  <div class="portlet-title">
                                      <div class="caption">
                                        <input type="checkbox" class="make-switch switch-block" id="switch-block5" data-size="mini">
                                        <div style="float:right;margin-left:20px;"> TSS Classification</div>
                                      </div>
                                      <div class="tools">
                                          <a href="javascript:;" class="collapse"></a>
                                      </div>
                                  </div>
                                  <div class="portlet-body form form-block" id="form-block5">
                                      <div class="form-body">
                                          <div class="row">
                                            <div class="col-md-12">
                                              Classification of the Transcription Start Sites (TSS) according to the nucleosome architecture. (<a href="javascript:;" target="_blank">more information</a>)
                                            </div>
                                          </div>
                                          <h4>&nbsp;</h4>
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Window <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Number of nucleotides on each side of the TSS where -1 and +1 nucleosome are search for.</p>"></i></label>
                                                      <input type="number" step="any" name="params[txstart][window]" id="params_txstart_win" class="form-control form-field-enabled" value="300">
                                                  </div>
                                              </div>
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Open threshold <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Distance between nucleosomes -1 and +1 to discriminate between 'open' and 'close' classes.</p>"></i></label>
                                                      <input type="number" step="any" name="params[txstart][open__thresh]" id="params_txstart_opent" class="form-control form-field-enabled" value="215">
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <!-- END PORTLET 6: TSS CLASSIFICATION -->
                              <!-- BEGIN PORTLET 7: STIFFNESS -->
                              <div class="portlet box blue form-block-header" id="form-block-header6">
                                  <div class="portlet-title">
                                      <div class="caption">
                                        <input type="checkbox" class="make-switch switch-block" id="switch-block6" data-size="mini">
                                        <div style="float:right;margin-left:20px;"> Stiffness</div>
                                      </div>
                                      <div class="tools">
                                          <a href="javascript:;" class="collapse"></a>
                                      </div>
                                  </div>
                                  <div class="portlet-body form form-block" id="form-block6">
                                      <div class="form-body">
                                          <div class="row">
                                            <div class="col-md-12">
                                              Measure of the resistance of a given nucleosome to be displaced, derived from the properties of the nucleosome calls fitted into a Gaussian distribution. (<a href="javascript:;" target="_blank">more information</a>)
                                            </div>
                                          </div>
                                          <h4>&nbsp;</h4>
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <div class="form-group">
                                                      <label class="control-label">Genomic Range <i class="icon-question tooltips" data-container="body" data-html="true" data-placement="right" data-original-title="<p align='left' style='margin:0'>Genomic region to be analyzed: whole genome ('all'), entire chromosome (chromosome name i.e. 'chrX'), or region of a chromosome ('chromosomeName:start-end).</p>"></i></label>
                                                      <input type="text" name="params[gausfitting][range]" id="params_gausfit_range" class="form-control form-field-enabled" value="All">
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <!-- END PORTLET 7: STIFFNESS -->

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
                <div class="modal fade bs-modal" id="myModal1" tabindex="-1" role="basic" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                <h4 class="modal-title">Warning!</h4>
                            </div>
                            <div class="modal-body"> This analysis uses nucleosome calls as input. 'NucleR' has been authomatically selected to cumpute them. </div>
                            <div class="modal-footer">
                                <button type="button" class="btn dark btn-outline" data-dismiss="modal">Accept</button>
                            </div>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->

<?php 

require "../../htmlib/footer.inc.php"; 
require "../../htmlib/js.inc.php";

?>
