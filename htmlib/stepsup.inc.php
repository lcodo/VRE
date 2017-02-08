<?php
$ap_step_upload = 0;
switch(pathinfo($_SERVER['PHP_SELF'])['filename']){
case 'uploadForm': $ap_step_upload = 1;
	break;
case 'uploadForm2': $ap_step_upload = 2;
	break;
}
?>

<div class="col-md-6 mt-step-col first active">
	<div class="mt-step-number bg-white">1</div>
	<div class="mt-step-title uppercase font-grey-cascade">Upload Files</div>
	<div class="mt-step-content font-grey-cascade" style="padding:0 20%;"></div>
</div>
<div class="col-md-6 mt-step-col <?php if($ap_step_upload >= 2) {echo 'active';} ?> last">
	<div class="mt-step-number bg-white">2</div>
	<div class="mt-step-title uppercase font-grey-cascade">Edit Metadata File</div>
	<div class="mt-step-content font-grey-cascade" style="padding:0 15%;"></div>
</div>

