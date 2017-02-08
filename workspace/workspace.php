<?php
/*
 * 
 */

require "../phplib/genlibraries.php";

redirectOutside();


if (!isset($_REQUEST['op'])) {
	header("location:../workspace/");
}
if (!isset($_REQUEST['fn']) && !isset($_REQUEST['fnPath']) && !preg_match('/cancelJob/',$_REQUEST['op']) ) {
	$_SESSION['errorData']['Error'][] = "Selected operation ('".$_REQUEST['op']."') requires at least one file. Any file name received.";
	header("location:../workspace/");
}
if (is_array($_REQUEST['fn']))
	$_REQUEST['fn']=$_REQUEST['fn'][0];
    	
$fileData = $GLOBALS['filesCol']->findOne(array('_id' => $_REQUEST['fn'], 'owner' => $_SESSION['User']['id']));
$fileMeta = $GLOBALS['filesMetaCol']->findOne(array('_id' => $_REQUEST['fn']));
$filePath = getAttr_fromGSFileId($_REQUEST['fn'],'path'); 
$rfn      = $GLOBALS['dataDir']."/$filePath";


// process operation

if (isset($_REQUEST['op'])){
  switch ($_REQUEST['op']) {

	case 'newFolder':
		$r = createGSDirBNS($GLOBALS['filesCol'],$_REQUEST['fn']);
		if ($r == 0){
			mkdir ($rfn,0777);
			chmod($rfn, 0777);
		}
		break;

	case 'downloadFile' :
		set_time_limit(0); 
		ini_set('memory_limit', '512M');

		if(empty($rfn) || !file_exists($rfn) ){
			$_SESSION['errorData']['Error'][] = "File ".$_REQUEST['fn']." not found in disk anymore or empty <a href=\"javascript:location.reload();\">[ OK ]</a>";
			break;
		}
		downloadFile($rfn);
		break;

	case 'downloadtgz' :
		if (filetype($rfn) != 'dir') {
			$_SESSION['errorData']['Error'][] = "Cannot tar ".$_REQUEST['fn']." File is not a directory";
			break;
		}
		$newName= $_REQUEST['fn'].".tar.gz";
		$tmpZip = $GLOBALS['tmpDir']."/".basename($newName); 

		$cmd = "/bin/tar -czf $tmpZip -C $rfn .  2>&1";
		exec($cmd,$output);
		if ( !is_file($tmpZip) ){
			$_SESSION['errorData']['Error'][] = "Uncompressed file not created.";
			if ($output)
				$_SESSION['errorData']['Error'][] = implode(" ", $output)."</br> <a href=\"javascript:location.reload();\">[ OK ]</a>";
			break;
		}
		downloadFile($tmpZip);
		unlink($tmpZip);
		break;

	case 'openPlainFileEdit':
		// read the textfile
		$text = file_get_contents($rfn);
		?>
		<form action="" method="post">
			<textarea style="border:2px solid #92b854; background-color: #fefbfa;"  cols=150 rows=37 name="text"><?php echo htmlspecialchars($text) ?></textarea>
			</br>
			<input type="submit" value="Save edited file"/>
			<input type="reset" />
		</form>
		<?php
		exit;
		break;

	case 'openPlainFile':
		$fileInfo = pathinfo($rfn);
		$contentType = "text/plain";
		$fileExtension = $fileInfo['extension'];
		$content_types_list = mimeTypes();
		if (array_key_exists($fileExtension, $content_types_list))
			$contentType = $content_types_list[$fileExtension];

		if (!$fileData && !preg_match('/\.log/',$rfn) ){
            		break;
	      	}
        	if (!is_file($rfn) || !filesize($rfn)){
	        	$_SESSION['errorData']['error'][]= "'".basename($rfn). "' does not exist anymore or is empty. <a href=\"javascript:deleteMesg('".urlencode($_REQUEST['fn'])."')\">[ Delete ]</a> <a href=\"/workspace/workspace.php\">[ OK ]</a>";
        		 break;
		}
		header('Content-Type: ' . $contentType);
		header("Accept-Ranges: bytes");
		header("Access-Control-Allow-Methods:GET, HEAD, DNT");
		header("Content-Disposition: inline; filename=".basename($rfn));
		header("Content-Length: ".filesize($rfn));
		print passthru("/bin/cat \"$rfn\"");
		exit;
		break;
	
	case 'openPlainFileFromPath':
		if (!$_REQUEST['fnPath']){
			$_SESSION['errorData']['Error'][]="Cannot open file. Variable 'fnPath' not received. Please, try it latter or mail <a href=\"mailto:helpdesk@multiscalegenomics.eu\">helpdesk@multiscalegenomics.eu</a>";
			break;	
		}
		$rfn = $GLOBALS['dataDir']."/".$_REQUEST['fnPath'];
		$fileInfo = pathinfo($rfn);
		$contentType = "text/plain";
		$fileExtension = $fileInfo['extension'];
		$content_types_list = mimeTypes();
		if (array_key_exists($fileExtension, $content_types_list))
			$contentType = $content_types_list[$fileExtension];

		if (!$fileData && !preg_match('/\.log/',$rfn) ){
            		break;
	      	}
        	if (!is_file($rfn) || !filesize($rfn)){
	        	$_SESSION['errorData']['error'][]= "'".basename($rfn). "' does not exist anymore or is empty. <a href=\"javascript:deleteMesg('".urlencode($_REQUEST['fn'])."')\">[ Delete ]</a> <a href=\"/workspace/workspace.php\">[ OK ]</a>";
        		 break;
		}

		header('Content-Type: ' . $contentType);
		header("Accept-Ranges: bytes");
		header("Access-Control-Allow-Methods:GET, HEAD, DNT");
		header("Content-Disposition: inline; filename=".basename($rfn));
		header("Content-Length: ".filesize($rfn));
		print passthru("/bin/cat \"$rfn\"");
		exit;

	case 'uploadFile':
		$writtableDir=$_SESSION['User']['dataDir']."/uploads";
		$writtableDirRE= "/^".preg_quote($writtableDir,"/")."/";
		if ( ! preg_match($writtableDirRE,$_SESSION['curDir']) ){
			$_SESSION['errorData']['error'][]=  "User". $_SESSION['User']['id']." can only write into $writtableDir";
			break;
		}
		ini_set('upload_max_filesize', $GLOBALS['limitFileSize']);
		ini_set('post_max_size', $GLOBALS['limitFileSize']);
		ini_set('max_input_time',$GLOBALS['max_execution_time']);
		ini_set('max_execution_time', $GLOBALS['max_execution_time']);

		$_SESSION['usedDisk']  = getUserSpace();
		$_SESSION['disklimit'] = getUserDiskLimit();

		if (empty($_FILES['fn']['name'][0])) {
			$_SESSION['errorData']['error'][]= "File upload failed. Recieving blank. </br> <a href=\"javascript:location.reload()\">[ OK ]</a>";
			break;
		}
		$fn=Array();
		for ($i = 0; $i < count($_FILES['fn']['tmp_name']); ++$i) {
			if (!$_FILES['fn']['error'][$i]) {
				// check size and space
				if ( $_FILES['fn']['size'][$i]> return_bytes(ini_get('upload_max_filesize')) || $_FILES['fn']['size'][$i]>return_bytes(ini_get('post_max_size')) ){
					$_SESSION['errorData']['error'][] = "ERROR: File size (".$_FILES['fn']['size'][$i].") larger than UPLOAD_MAX_FILESIZE (".ini_get('upload_max_filesize').") </br> <a href=\"javascript:history.go(-1)\">[ OK ]</a>";
					break;
				}
				//if ($_FILES['fn']['size'][$i] > ($GLOBALS['disklimit']-$_SESSION['usedDisk'])){
				if ($_FILES['fn']['size'][$i] > ($_SESSION['disklimit']-$_SESSION['usedDisk'])){
					$_SESSION['errorData']['error'][] = "ERROR: Cannot upload file. Not enough space left in the workspace.</br> <a href=\"javascript:history.go(-1)\">[ OK ]</a>";
					break;
				}
				//do not overwrite, rename
				$rfnNew= $GLOBALS['dataDir']."/".$_SESSION['curDir']."/".$_FILES['fn']['name'][$i];
				if (is_file($rfnNew)){
					foreach (range(1, 99) as $N) {
						$tmpNew= $rfnNew."_".$N;
						if (!is_file($tmpNew)){
							$rfnNew = $tmpNew;
							break;
						}
					}
				}
				//upload
				move_uploaded_file($_FILES['fn']['tmp_name'][$i], $rfnNew);
				chmod($rfnNew, 0666);

			} else {
				$code= $_FILES['fn']['error'][$i];
				$errMsg = array( 
		 		0=>"[UPLOAD_ERR_OK]:  There is no error, the file uploaded with success", 
				1=>"[UPLOAD_ERR_INI_SIZE]: The uploaded file exceeds the upload_max_filesize directive in php.ini", 
				2=>"[UPLOAD_ERR_FORM_SIZE]: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form", 
				3=>"[UPLOAD_ERR_PARTIAL]: The uploaded file was only partially uploaded", 
				4=>"[UPLOAD_ERR_NO_FILE]: No file was uploaded", 
				6=>"[UPLOAD_ERR_NO_TMP_DIR]: Missing a temporary folder", 
				7=>"[UPLOAD_ERR_CANT_WRITE]: Failed to write file to disk",
				8=>"[UPLOAD_ERR_EXTENSION]: File upload stopped by extension"
				);
				if(isset($errMsg[$code])){
					$_SESSION['errorData']['error'][] = "ERROR [code $code] ".$errMsg[$code];
				}else{
					$_SESSION['errorData']['error'][] = "Unknown upload error";
				}
				$_SESSION['errorData']['error'][].="</br> <a href=\"javascript:history.go(-1)\">[ OK ]</a>";
			}
			if (is_file($rfnNew)){
				$fnNew = basename($rfnNew);
				$insertData=array(
					'_id'   => $_SESSION['curDir']."/".$fnNew,
					'owner' => $_SESSION['userId'],
					'size'  => filesize($rfnNew),
					'mtime' => new MongoDate(filemtime($rfnNew))
				);
				uploadGSFileBNS($_SESSION['curDir']."/".$fnNew, $rfnNew, $insertData,NULL,FALSE);
				array_push($fn,$_SESSION['curDir']."/".$fnNew);
			}else{
				$_SESSION['errorData']['error'][]="Uploaded file not stored at $rfnNew.";
			}
		}
		$url = "uploadForm.php";
		if (count($fn)> 0){
			$url .= "?fn[]=". implode('&fn[]=', array_map('urlencode', $fn));
		}
	   	redirect($url);
		break;

	case 'uploadFileValidate':
		unset($_SESSION['errorData']);
		//validate file
		$ok=false;
		if ($_REQUEST['format'] == "BAM"){
			$ok = validateBAM($rfn,$_REQUEST['refGenome']);
			if ($ok && !isset($_SESSION['errorData']['validate'])){
				$REQUEST['op'] = "uploadFileValidateSure";
			}
		}else{
			$ok=true;
		}
		if (! $ok){
			$_SESSION['errorData']['error'][]="Uploaded file ".$_REQUEST['fn']." is not valid. Data cannot be analysed nor vizualized. Please, check your file and upload it again";
		}
		var_dump($_SESSION['errorData']);
		$url = "uploadForm.php?validated=$ok&fn[]=".$_REQUEST['fn'];
	   	redirect($url);
		break;
    case 'uploadFileValidate':
		if ($_REQUEST['format'] == "BAM"){
			$ok = sortBAM();
		}else{
			$ok=true;
		}
		break;			
	case 'uploadFileMeta':
		//upload file metadata	
		$tracktype= format2trackType($_REQUEST['format']);
		$label= uniqid("lab");
		$validated = (isset($_REQUEST['validated'])?$_REQUEST['validated']:false);

		if (! empty($GLOBALS['filesCol']->findOne(array('label' => $label))))
			$label= uniqid("lab");
		
		$insertMeta=array(
			'format' 	 => $_REQUEST['format'],
			'refGenome'  => $_REQUEST['refGenome'],
			'paired'     => $_REQUEST['paired'],
			'sorted'     => $_REQUEST['sorted'],
			'description'=> $_REQUEST['description'],
			'trackType'  => $tracktype,
			'validated'  => $validated,
			'label'      => $label
		);
		uploadGSFileBNS($_REQUEST['fn'], $_REQUEST['fn'], NULL,$insertMeta,FALSE);
		break;

// delete message shown by javascript (deleteMsg)
//	case 'delete':
//		$_SESSION['errorData']['error'][] = "Are you sure you want to remove '" . basename($_REQUEST['fn']) . "' <a href=\"".$GLOBALS['managerDir']."/workspace.php?op=deleteSure&fn=" . $_REQUEST['fn'] . "\">[ Yes]</a> <a href=\"".$GLOBALS['managerDir']."/workspace.php\">[ Cancel ]</a>";
//		break;

	case 'deleteSure':
		$r = deleteGSFileBNS($_REQUEST['fn']);
		if ($r == 0)
			break;
		unlink ($rfn);
		if (error_get_last()){
			$_SESSION['errorData']['error'][]=error_get_last()["message"];
			break;
		}

		if ($fileMeta['format'] == "BAM"){
			$bai  = $GLOBALS['dataDir']."/".$_REQUEST['fn'].".bai";
			$Rdata= $GLOBALS['dataDir']."/".$_REQUEST['fn'].".RData";
			$cov  = $GLOBALS['dataDir']."/".$_REQUEST['fn'].".cov";
			if (is_file($bai))
				unlink ($bai);
			if (is_file($Rdata))
				unlink ($Rdata);
			if (is_file($cov))
				unlink ($cov);
		}
		$GLOBALS['filesMetaCol']->remove(array('_id'=> $_REQUEST['fn']));
		break;

	case 'deleteDirOk':
		$r = deleteGSDirBNS($_REQUEST['fn']);
		if ($r == 0)
			break;
		exec ("rm -r \"$rfn\" 2>&1",$output);
		$_SESSION['errorData']['error'][]=implode(" ",$output);
		break;

// delete message shown by javascript (deleteDirMsg)
//	case 'deleteDir':
//		if (count($fileData['files']) == 0){
//			$r = deleteGSFileBNS($_REQUEST['fn']);
//			if ($r == 0)
//				break;
//			exec ("rm -r \"$rfn\" 2>&1",$output);
//			$_SESSION['errorData']['error'][]=implode(" ",$output);
//		}else{
//			$_SESSION['errorData']['error'][] = "Could not remove. Not empty Folder. <a href=\"".$GLOBALS['managerDir']."/workspace.php?op=deleteDirOk&fn=" . $_REQUEST['fn'] . "\">[ Delete anyway ]</a>";
//		}
//		break;

	case 'cancelJobPids':
		$r = delJob($_REQUEST['pids']);
		break;

	case 'cancelJobDir':
		$jobList=Array();
		$jobData = getUserJobs($_SESSION['userId']);
		if (count($jobData)){
	  		foreach ($jobData as $data){
				$filesId = (!is_array($data['out'])? Array($data['out']):$data['out']);
	  			foreach ($filesId as $fileId){
					if (isset($fileId) && preg_match('/^'.preg_quote($_REQUEST['fn'],'/').'/',$fileId) ){
						array_push($jobList,$fileId);
						continue;
					}
				}
			}
		}
		if (count($jobList)==0 ){
			$_SESSION['errorData']['error'][]= "Cannot cancel tasks from ".$_REQUEST['fn'].". Not submited jobs found. Have they just finished? <a href=\"".$GLOBALS['managerDir']."/workspace.php\">[ OK ]</a>";
		}else{
			$dirFiles = "<li>- ". implode('</li><li>- ', array_map('basename', $jobList))."</li>";
			$_SESSION['errorData']['error'][]   = "Are you sure you want to cancel all jobs from project '".basename($_REQUEST['fn'])."'? <a href=\"".$GLOBALS['managerDir']."/workspace.php?op=cancelJobDirSure&fn=" . urlencode($_REQUEST['fn']). "\">[ Yes, I'm sure ]</a> <a href=\"".$GLOBALS['managerDir']."/workspace.php\">[ Cancel ]</a> <ul>$dirFiles</ul>";
		}
		break;

	case 'cancelJob':
		$_SESSION['errorData']['error'][] = "Are you sure you want to cancel job for file '" . basename($_REQUEST['fn']) . "'? ";
		$pid = $_REQUEST['pid'];
		//$pid = getPidFromOutfile($_REQUEST['fn']);
		$jobInfo= getRunningJobInfo($pid);
		$SGE_updated = getUserJobs($_SESSION['userId']);
		$succList = "";
		if (isset($jobInfo['jid_successor_list'])){
			foreach (explode(",",trim($jobInfo['jid_successor_list'])) as $pidSucc ){
				if (isset($SGE_updated[$pidSucc])){
					if (!is_array($SGE_updated[$pidSucc]['out']))
						$outSucc =  $SGE_updated[$pidSucc]['out'];
					else
						$outSucc =  $SGE_updated[$pidSucc]['out'][0];
					$succList .= "<li>- ".basename($outSucc)."</li>";
            			}
			}
		}
		if ($succList){
			$_SESSION['errorData']['error'][] .= "The following execution dependencies will also be cancelled:<ul>$succList<ul/>";
		}
		$_SESSION['errorData']['error'][].= "<a href=\"".$GLOBALS['managerDir']."/workspace.php?op=cancelJobSure&fn=" . $_REQUEST['fn'] . "\">[ Yes, I'm sure ]</a> <a href=\"".$GLOBALS['managerDir']."/workspace.php\">[ Cancel ]</a>";
		break;

	case 'cancelJobSure':
		$userJobs = getUserJobs($_SESSION['User']['_id']);
		
		$r = delJob($_REQUEST['pid']);
//		//$r = delJobFromOutfiles($_REQUEST['fn']);
		print "fet";
		break;

	case 'cancelJobDirSure':
		$jobList=Array();
		$jobData = getUserJobs($_SESSION['User']['_id']);
		if (count($jobData)){
	  	    foreach ($jobData as $jobId =>$data){
			if ($data['outDir'] == $filePath){
				$r = delJob($jobId);
			}
			
		    }
		}
//		if (count($jobData)){
//	  		foreach ($jobData as $data){
//				$filesId = (!is_array($data['out'])? Array($data['out']):$data['out']);
//	  			foreach ($filesId as $fileId){
//					if (isset($fileId) && preg_match('/^'.preg_quote($_REQUEST['fn'],'/').'/',$fileId) ){
//						array_push($jobList,$fileId);
//						continue;
//					}
//				}
//			}
//		}
//		$r = delJobFromOutfiles($jobList);
		if (count($fileData['files'])==0 && !isset($_SESSION['errorData']['SGE'])){
		        $r = deleteGSDirBNS($_REQUEST['fn']);
		        if ($r == 0)
	        	    break;
			exec ("rm -r \"$rfn\" 2>&1",$output);
		        $_SESSION['errorData']['error'][]=implode(" ",$output);
		}
		break;


	case 'close':
		session_destroy();
		redirect($GLOBALS['homeURL']);
		break;

	case 'unzip':
	case 'untar':
		$ext	  = pathinfo($filePath, PATHINFO_EXTENSION);
		$extClean = preg_replace('/_\d+$/',"",$ext);
		$fn_Tmp   = str_replace(".$ext","",$filePath);
		$rfn_Tmp  = dirname($rfn)."/".basename($fn_Tmp);
		$cmd      = "";

		switch ($extClean) {
			case 'tar':
				#touch option force tar to update uncompressed files atime - required by the expiration time
				$cmd = "tar --touch -xf \"" . $rfn . "\" 2>&1";
				break;
			case 'zip':
				$cmd = "unzip -o \"" . $rfn . "\" 2>&1";
				break;
			case 'bz2':
				$cmd = "bzip2 -d \"" . $rfn . "\" 2>&1";
				break;
			case 'gz':
			case 'tgz':
				if (pathinfo(pathinfo($rfn, PATHINFO_FILENAME), PATHINFO_EXTENSION) == 'tar'){
					$fn_Tmp  = str_replace(".tar","",$fn_Tmp);
					$rfn_Tmp = dirname($rfn)."/".basename($fn_Tmp);
					$cmd = "tar --touch -xzf \"" . $rfn . "\" 2>&1";
				}else{
					$cmd = "gunzip -S .$ext -f \"" . $rfn . "\" 2>&1";
				}
				break;
			default:
				$_SESSION['errorData']['error'][] = "Cannot uncompress $extClean file. Method not supported";
				
		}

		if ($cmd){
			exec($cmd, $output);
	
			if (file_exists($rfn_Tmp)){
				$insertData = array(
					'_id'   => $_REQUEST['fn'],
					'owner' => $_SESSION['User']['id'],
					'size'  => filesize($rfn_Tmp),
					'mtime' => new MongoDate(filemtime($rfn_Tmp)),
					'path'  => $fn_Tmp
				);
				$insertMeta = $fileMeta;
				$insertMeta['compress'] = 0;
	
				$r = uploadGSFileBNS($rfn_Tmp,$fn_Tmp,$insertData,$insertMeta,FALSE);
				if ($r == 0)
					break;

				if (is_file($rfn)){
					unlink($rfn);
				}
	
			}elseif (is_dir($rfn_Tmp) ){
				//TODO
				$_SESSION['errorData']['error'][]=" Error inflating ".basename($filePath).". Directories cannot be uncompressed </br>";
				unlink($rfn_Tmp);
		
			}else{
				$_SESSION['errorData']['error'][]= "Error wile uncompressing ".basename($filePath).". Outfile not created.<br/>";
				if ($output)
					$_SESSION['errorData']['error'][].= implode("</br>", $output)."</br>";
			}
		}
		unset($_REQUEST['op']);
		break;

	case 'zip':
		$rfn_TmpZip = dirname($rfn)."/".basename($rfn).".gz";
		$fn_TmpZip  = dirname($filePath)."/".basename($filePath).".gz";

		$cmd = "gzip -f \"$rfn\" 2>&1";
		exec($cmd, $output);
		

		if (file_exists($rfn_TmpZip)){

	    		$insertData=array(
	        	   '_id'   => $_REQUEST['fn'],
		           'owner' => $_SESSION['User']['id'],
		           'size'  => filesize($rfn_TmpZip),
		           'path'  => $fn_TmpZip,
		           'mtime' => new MongoDate(filemtime($rfn_TmpZip))
			);


			$insertMeta = $fileMeta;
			$insertMeta['compressed'] = "zip";
			
			$r = uploadGSFileBNS($fn_TmpZip,$rfn_TmpZip,$insertData,$insertMeta,FALSE);
			if ($r == 0)
				break;

//			$r = deleteGSFileBNS($_REQUEST['fn']);
//			if ($r == 0)
//				break;

		}else{
			$_SESSION['errorData']['error'][] = "Compressed ZIP file not created.";
			if ($output)
				$_SESSION['errorData']['error'][] .= implode(" ", $output)."</br> <a href=\"javascript:history.go(-1)\">[ OK ]</a>";
		}
		unset($_REQUEST['op']);
		break;

	case 'tar':
//		$cmd = "tar --touch  -cf \"" . $_REQUEST['fn'] . ".tar\" \"" . $_REQUEST['fn'] . "\" 2>&1";
//		chdir($_SESSION['curDir']);
//		exec($cmd, $output);
//		$_SESSION['errorData']['error'][] = implode(" ", $output);
//		chdir($_SESSION['User']->dataDir);
		break;
  }
}


header("location:../workspace/");


?>
<script>
	//mantains REQUEST[in] ckecked
	/*checkList();

	//keeps pendingJobs updated
	function refresh() {
		var control = document.getElementById("autorefresh");
		if (control && control.value == 1){
			var newCont = checkNewContent();
			if (newCont == "201"){
	             window.location.reload(true);

			}else{
				setTimeout(refresh, 30000);
			}
		}else{
             setTimeout(refresh, 30000);
		}
	}
	//calls checkPendingJobs.php via AJAX
	function checkNewContent(){
		var url  = "http://"+ window.location.hostname + "/NucleosomeDynamics-dev/datamanager/checkPendingJobs.php";
 		if (! window.XMLHttpRequest){
    		alert("Your browser does not support AJAX. Please, download Google Chrome or Firefox.");
			return null;
		}
		var http = new XMLHttpRequest();
        http.open("GET", url, false);
        http.send();
        if ( http.readyState == 4) {
			return http.status;
        }
		return 0;
	}
	setTimeout(refresh, 30000);*/
			//location.href = "../home.php";
</script>


<?php
//scan disk to upload Mongo -- TODO: move function upper, for syncronizing before showing the table but after processingPendingJobs
//syncWorkDir2Mongo($GLOBALS['dataDir']."/".$_SESSION['curDir']);
?>

<!-- pop up container-->
<!--
<div class="popup" data-popup="popup-1">
    <div class="popup-inner">
		<div></div>
        <a class="popup-close" data-popup-close="popup-1" href="javascript:closepop();">x</a>
    </div>
</div>
-->

