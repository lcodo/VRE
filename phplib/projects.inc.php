<?php

function prepUserWorkSpace($dataDir) {
	$_SESSION['curDir'] = $dataDir;
	
	$dataDirP  = $GLOBALS['dataDir']."/$dataDir";
	$dataDirId = getGSFileId_fromPath($dataDir);

	if (! isGSDirBNS($GLOBALS['filesCol'],$dataDirId) || ! is_dir($dataDirP) ){

		//creating home directory
		$dataDirId  = createGSDirBNS($dataDir);
		if ($dataDirId == "0" ){
			$_SESSION['errorData']['login'][] = "Cannot create home directory $dataDir";
			return 0;
		}
		//$r = modifyGSFileBNS($dataDirId, "expiration", new MongoDate(strtotime("+365 day")));
		$r = addMetadataBNS($dataDirId,Array("expiration"=>-1));
		if ($r == "0" ){
			$_SESSION['errorData']['login'][] = "Cannot set home directory $dataDir";
			return 0;
		}
		mkdir($dataDirP, 0775) or die("Cannot create $dataDirP");

		//creating uploads directory
		$upDirId  = createGSDirBNS($dataDir."/uploads");
		$r = addMetadataBNS($upDirId,Array("expiration"=>-1));
		if ($r == "0" ){
			$_SESSION['errorData']['login'][] = "Cannot create structural directory $dataDir/uploads";
			return 0;
		}
		mkdir("$dataDirP/uploads", 0775);

		//creating README
		copy($GLOBALS['sampleData']."/README.md","$dataDirP/uploads/README.md");
		//$readmeId= createLabel();
		$insertData=array(
			//'_id'   => $readmeId,
			'owner' => $_SESSION['User']['id'],
			'size'  => filesize("$dataDirP/uploads/README.md"),
			'mtime' => new MongoDate(filemtime("$dataDirP/uploads/README.md")),
			'path'  => "uploads/README.md"
		);
		$insertMeta=array(
			'format'     => "TXT",
			'description'=> "Virtual Research Environment README",
			'validated'  => true,
			'trackType'  => "TXT"
		);
		$readmeId = uploadGSFileBNS("uploads/README.md", "$dataDirP/uploads/README.md", $insertData,$insertMeta,FALSE);
		if ($fnId == "0")
			$_SESSION['errorData']['login'][]="Cannot inject sample data README";

		//injecting sample data for Anonimous
		if ($_SESSION['User']['Anon'] == TRUE ){
			$files = scandir($GLOBALS['sampleData']);
			foreach ($files as $file){
				if ($file == "." || $file == "..")
					continue;
				symlink($GLOBALS['sampleData']."/$file", "$dataDirP/uploads/$file");
				$extension = pathinfo("$dataDirP/uploads/$file",PATHINFO_EXTENSION);
				$extension= preg_replace('/_\d+$/',"",$extension);

				if (strtoupper($extension) == "BAM" ){
					if (basename($file) ==  "sample_cellcycle_M.bam"){
						$descrip = "Sample MNase data phase M";
					}elseif (basename($file) ==  "sample_cellcycle_G1.bam"){
						$descrip = "Sample MNase data phase G1";
					}else{
						$descrip = "Sample MNase data";
					}
					$label= createLabel();
					$insertData=array(
						'_id'   => "$dataDir/uploads/$file",
						'owner' => $_SESSION['userId'],
						'size'  => filesize("$dataDirP/uploads/$file"),
						'mtime' => new MongoDate(filemtime("$dataDirP/uploads/$file")),
						'type'  => "link"
					);
					#TODO check metadata
					$insertMeta=array(
						'format'     => "BAM",
						'refGenome'  => "R64-1-1",
						'paired'     => "paired",
						'sorted'     => "sorted",
						'description'=> $descrip,
						'validated'  => true,
						'trackType'  => "BAM",
						'label'	      => $label
					);
					$fnId = uploadGSFileBNS("$dataDir/uploads/$file", "$dataDirP/uploads/$file", $insertData,$insertMeta,FALSE);
					if ($fnId == "0"){
						$_SESSION['errorData']['login'][]="Cannot inject sample data $file";
					}
				}
			}
		}

		// creating other directories not registered in mongo

		//mkdir("$dataDirP/.jbrowse", 0775);
		mkdir("$dataDirP/.tmp", 0775);
	}

	$GLOBALS['filesCol']->update(
		array('_id' => $dataDirId),
		array('$set' => array(
			  'lastAccess' => moment()
			)
		)
	);

	//$_SESSION['User']['dataDir'] = $dataDir;

//	if (! isset( $_SESSION['curDir'])){
		$_SESSION['curDir'] = $dataDir;
//	}
	return $dataDirId;	
}

function getFilesToDisplay($dataSelection) {
	$filesAll=Array();

	// Retrieve data

	$files = array();
	switch($GLOBALS['fsStyle']){
		case "fsMongo":
		    $files=getGSFilesFromDir($dataSelection);
		    break;
		case "mongo":
		    $files=getGSFilesFromDir($dataSelection);
		case "fs":
		    #TODO
		default:
		    $_SESSION['errorData']['internal'][]="Cannot update dashboard. Given fsStyle (".$GLOBALS['fsStyle'].") not set. Please, report to <a href=\"mailto:helpdesk@multiscalegenomics.eu\">helpdesk@multiscalegenomics.eu</a>";
		    return $filesAll;
	}
	if (!$files){
	    $_SESSION['errorData']['Error'][]="Cannot update dashboard.";
	    return $filesAll;
	}

	// Extract pending files

	$filesPending= processPendingFiles($_SESSION['User']['_id'],$files);

	// Merge pending files and mongo files

	if ($filesPending){
		foreach ($filesPending as $r){
			// Update $files[id][files]
			if (!isset($filesPending[$r['_id']]['parentDir'] )){
				$_SESSION['errorData']['Error'][]="Pending file ".$filesPending[$r['_id']]['path']." has no parentDir";
				continue;
			}
			$parentId = $filesPending[$r['_id']]['parentDir'];
			if (!isset($files[$parentId])){
			   if ($r['pending']){
				$_SESSION['errorData']['Warning'][]="Output directory of job '".$r['title']."' (".basename($r['logPath']).") does not exist anymore. <a href=\"javascript:;\">[ Cancel Job ]</a>";
				unset($filesPending[$r['_id']]);
			    }else{
				$_SESSION['errorData']['Error'][]="FS inconsistency. The directory containing file '".$r['path']."' does not exist anymore.";
				unset($filesPending[$r['_id']]);		
			    }
			    continue;
			}
			array_push($files[$parentId]['files'],$r['_id']);
		}
		$filesAll=array_merge($files,$filesPending);
		//$filesAll=$files;
	}else{
		$filesAll=$files;
	}

	return $filesAll;

}
function addTreeTableNodesToFiles($filesAll){
	// Add Tree Nodes

    	//add datatable tree nodes and hidden cols values
	$n=1;
	foreach ($filesAll as $r){
        	if (isset($r['files'])){
	            $filesAll[$r['_id']]['tree_id']     = $n;
	            $filesAll[$r['_id']]['size']        = calcGSUsedSpaceDir($r['_id']);
	            $filesAll[$r['_id']]['size_parent'] = $filesAll[$r['_id']]['size'];
	            $filesAll[$r['_id']]['mtime_parent']=(isset($r['atime'])? $r['atime']->sec : $r['mtime']);
	            $i=1;
	            foreach ($r['files'] as $rr){
	                $filesAll[$rr]['tree_id']       = "$n.$i";
	                $filesAll[$rr]['tree_id_parent']= $n;
	                $filesAll[$rr]['size_parent']   = $filesAll[$r['_id']]['size_parent'];
	                $filesAll[$rr]['mtime_parent']  = $filesAll[$r['_id']]['mtime_parent'];
	                $i++;
	            }
	            $n++;
		}else{
			if (isset($r['pending']) ){
				$dir = $r['parentDir'];
				$filesAll[$dir]['pending']="true";
			}	
		}
    	}

//	foreach ($filesAll as $id => $r){
//		if ($r['files']){
//		print "DIR $id -- PATH=<strong>".$r['path']."</strong> PENDING=".$r['pending']." TREE=".$r['tree_id']." NFILES=".count($r['files'])."</br>";
//		}else{
//			print "FIL $id -- PATH=".$r['path']." PENDING=".$r['pending']." TREE=".$r['tree_id']."</br>";
//		}
//		if (isset($r['pending'])){
//			//print "-- VAR_DUMP: ";
//			//var_dump($r);
//			//print "<br/>";
//			foreach ($r as $k=>$v){
//				print "-- -- --  $k=$v<br/>";
//			}
//		}
//	}
	return $filesAll;

}

function printTable($filesAll=Array() ) {

	$autorefresh=0;
	?>

	<table id="workspace" class="display" cellspacing="0" width="100%">

	<?php
		print parseTemplate($_REQUEST, getTemplate('/TreeTblworkspace/header.htm'));

		?>
		<tbody><?php

		foreach ($filesAll as $r) {
			if (isset($r['files'])){
				if (preg_match('/\/\./',$r['_id']))
					continue;
				if (isset($r['pending'])){
					print parseTemplate(formatData($r), getTemplate('/TreeTblworkspace/TR_folderPending.htm'));
				}elseif(basename($r['path']) == "uploads"){
					print parseTemplate(formatData($r), getTemplate('/TreeTblworkspace/TR_folder_uploads.htm'));
				}else{
					print parseTemplate(formatData($r), getTemplate('/TreeTblworkspace/TR_folder.htm'));
				}
			}elseif(isset($r['pending'])){
					print parseTemplate(formatData($r), getTemplate('/TreeTblworkspace/TR_filePending.htm'));
					$autorefresh=1;
			}elseif(isset($r['_id'])){
					if ($r['validated'] == 1){
						print parseTemplate(formatData($r), getTemplate('/TreeTblworkspace/TR_file.htm'));
					}else{
						print parseTemplate(formatData($r), getTemplate('/TreeTblworkspace/TR_fileDisabled.htm'));
					}
			}else{
				//empty mongo entry;
			}
		}
		?>
		</tbody>

	</table>

	<?php 
	if ($autorefresh){
		print "<input type=\"hidden\" id=\"autorefresh\" value=\"$autorefresh\"/>\n";
	}
}

function formatData($data) {
		//_id id_URL
		if (!isset($data['_id']))
			return $data;
		$data['_id_URL'] = urlencode($data['_id']);
		//mtime atime
		if (isset($data['mtime'])){
			if (is_object($data['mtime']))
				$data['mtime']=$data['mtime']->sec;
			$data['mtime'] = strftime('%Y/%m/%d %H:%M', $data['mtime']);
		}else{
			$data['mtime']="";
		}
		if (isset($data['atime'])){
			if (is_object($data['atime']))
				$data['atime'] =$data['atime']->sec;
			$data['atime'] = strftime('%Y/%m/%d %H:%M', $data['atime']);
			$data['mtime'] = $data['atime'];
		}
		//format
		if (!isset($data['format']))
			$data['format']="";
		//expiration
		if (isset($data['expiration'])){
			$days2expire = intval(( $data['expiration']->sec  -time() ) / (24 * 3600));
			if ($days2expire < 7)
				$data['expiration'] ="<span style=\"color:#b30000;font-weight:bold;\">".$days2expire."</span>";
			else
				$data['expiration'] =$days2expire;
		}else{
			$data['expiration'] ="";
		}
		//size
		if (isset($data['files']) && !isset($data['size']) ){
			$data['size'] = calcGSUsedSpaceDir($data['_id']);
		}
		if (isset($data['size'])){
			$sz = 'BKMGTP';
			$factor = floor((strlen($data['size']) - 1) / 3);
			$data['size']	= sprintf("%.2f %s", $data['size'] / pow(1024, $factor),@$sz[$factor]);
		}else{
			$data['size']="";
		}
		//project
		if (isset($data['parentDir'])){
			$data['parentDir'] = getAttr_fromGSFileId($data['parentDir'],'path');
			$data['project'] = array_pop(split("/",$data['parentDir']));
		}
		// description
		if (isset($data['description'])){
			if(strlen($data['description']) > 50) $data['description'] = substr($data['description'], 0, 50).'...';
		}
		//filename logFile logFile_URL execDetails
		if (isset($data['pending'])){
			if (!isset($data['files'])){
				$data['filename']=$data['title'];
				$viewLog_state="enabled";
				if ($data['pending']=="HOLD" || $data['pending']=="PENDING"){
					$viewLog_state = 'disabled';
				}elseif(!is_file($GLOBALS['dataDir']."/".$data['logPath']) && !is_link($GLOBALS['dataDir']."/".$data['logPath'])){
					$viewLog_state = 'disabled';
				}
				$data['viewLog'] = "<tr><td>Log file:</td><td><a target=\"_blank\" href=\"/workspace/workspace.php?op=openPlainFileFromPath&fnPath=".urlencode($data['logPath'])."\" class=\"$viewLog_state\">View</a></td></tr>";
				$data['logPath'] = basename($data['logPath']);
			}else{
				$data['filename']= basename($data['path']);
			}
		}else{
			$data['filename']= basename($data['path']);
		}
		// TODO for debug. Temporal. To delete
		if ($data['filename']){
			$rfn      = $GLOBALS['dataDir']."/".$data['path'];
			if (!is_file($rfn) && !is_dir($rfn)){
				$data['filename']="<span style=\"color:red\">".$data['filename']."</span>";
			}
		}
		if(isset($data['shPath'])){
			$data['execDetails'] = "<tr><td>Execution details:</td><td><a href=\"javascript:callShowSHfile('".$data ['tool']."','".$data['shPath']."');\">Analysis parameters</a></td></tr>";
		}else{
			$data['execDetails'] = "";
		}
		//notes
		if(isset($data['notes']) && strlen($data['notes']) ){
			$data['notes'] = "<tr><td>Notes:</td><td>".$data['notes']."</td></tr>";
		}else{
			$data['notes'] = "";
		}
		//paired sorted refGenome
		if(isset($data['paired']) ||  isset($data['sorted']) ){
			$row = "<tr><td>BAM properties:</td><td>";
			if (isset($data['paired']))
				$row.=$data['paired'];
			if (isset($data['sorted']))
				$row.= "&nbsp;" . $data['sorted'];
			$row.= "</td></tr>";
			$data['paired']=$row;
		}else{
			$data['paired'] = "";
		}
		if (isset($data['refGenome'])){
			$data['refGenome'] = "<tr><td>Reference Genome:</td><td>".$data['refGenome']."</td></tr>";
		}else{
			$data['refGenome']="";
		}
		//state and metadataLink  
		if (isset($data['validated']) && $data['validated'] ){
			$data['state'] = 'enabled';
			$data['metadataLink'] = "<li><a href=\"/handlefiles/editFile.php?fn[]=".$data['_id_URL']."\"><i class=\"fa fa-pencil\"></i> Edit file metadata</a></li>";
		}else{
			$data['state'] = 'disabled';
			$data['metadataLink'] = "<li><a href=\"/handlefiles/editFile.php?fn[]=".$data['_id_URL']."\"><i class=\"fa fa-exclamation-triangle\"></i> Validate file</a></li>";
		}
		//tools list
		if ( isset($data['format']) ){

			switch($data['format']){
				case 'PDB':
					$data['tools'] = '<button class="btn btn-xs blue-oleo dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Tools
								<i class="fa fa-angle-down"></i></button>
							<ul class="dropdown-menu pull-center" role="menu">
								<li><a href="javascript:;"><i class="fa fa-sort-amount-asc"></i> MDWeb</a></li>
								<li><a href="javascript:;"><i class="fa fa-sort-amount-asc"></i>  NAFlex</a></li>
							</ul>';
		
					break;
				case 'BAM':
					$data['tools'] = '<button class="btn btn-xs blue-oleo dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Tools
								<i class="fa fa-angle-down"></i></button>
							<ul class="dropdown-menu pull-center" role="menu">
								<li><a href="javascript:;"><i class="fa fa-motorcycle"></i> Nucleosome Dynamics</a></li>
								<li><a href="javascript:;"><i class="fa fa-motorcycle"></i> NucleR</a></li>
							</ul>';
					break;
				case 'GFF':
				case 'GFF3':
					$data['tools'] = '<button class="btn btn-xs blue-oleo dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Tools
									<i class="fa fa-angle-down"></i></button>
							<ul class="dropdown-menu pull-center" role="menu">
								<li><a href="javascript:;"><i class="fa fa-motorcycle"></i>  Nucleosome Free Regions</a></li>
								<li><a href="javascript:;"><i class="fa fa-motorcycle"></i>  Nucleosome phasing</a></li>
								<li><a href="javascript:;"><i class="fa fa-motorcycle"></i>  TSS classification</a></li>
								<li><a href="javascript:;"><i class="fa fa-motorcycle"></i>  Stiffness</a></li>
							</ul>';
					break;
				default:
					break;
			}
		}
	
		//PDB visualization
		if ((isset($data['format'])) && ($data['format'] == 'PDB')){
			if ($pos = strrpos($data['filename'], '.')) {
				$name = substr($data['filename'], 0, $pos);
				$ext = substr($data['filename'], $pos);
			} else {
				$name = $data['filename'];
			}
			
			if(strtolower($ext) == '.pdb'){
				$data['PDBView'] = "<li><a href=\"javascript:openNGL('".$data['path']."', '".$name."');\"><i class=\"fa fa-cube\"></i> View in 3D</a></li>";
			}
		}
		//inPaths and 
		if (isset($data['inPaths'])){
			$ins =$data['inPaths'];
			$data['inPaths']="<tr><td>Input files:</td><td>";
			if (count($ins)){
				foreach ($ins as $in){
				    $data['inPaths'].= "<div>";
				    $inFolders=split("/",dirname($in));
				    for ($i=count($inFolders)-1;$i>=1;$i--){
					$data['inPaths'].= "<span class=\"text-info\" style=\"font-weight:bold;\">".$inFolders[$i]."/</span>";
				    }
				    $data['inPaths'].= basename($in)."</div>";
				}
			}
			$data['inPaths'].="</td></tr>";
		}
		//rerunLink
		if (isset($data['inPaths']) && isset($data ['tool'])){
			$tool = $GLOBALS['toolsCol']->findOne(array('_id' => $data['tool']));
			if (!empty($tool)){
				$formPath  = "/tools/".$data['tool']."/".$tool['formId'].".php";
			    $data['rerunLink'] ="<li><a href=\"$formPath?rerunDir=".$data['_id_URL']."\"><i class=\"fa fa-share\"></i> Rerun Project</a></li>";
			}
		}
		//analyses
		if (isset($data['analyses'])){
			$as =$data['analyses'];
			$data['analyses']="<tr><td>Analyses:</td><td>";
			if (count($as)){
				foreach ($as as $a)
					$data['analyses'].="$a &nbsp;";
			}
			$data['analyses'].="</td></tr>";
		}
		//compressed
		$ext = pathinfo($data['path'], PATHINFO_EXTENSION);
		$ext = preg_replace('/_\d+$/',"",$ext);
		$content_type  = ( array_key_exists($ext, mimeTypes()) ? mimeTypes()[$ext] : "application/octet-stream");
		$data['openFunction'] = ($content_type == "text/plain" || $ext=="pdf" || preg_match('/image/',$content_type) || preg_match('/(e|o)\d+/',$ext) ? "openPlainFile" : "downloadFile");
		$data['compressionLink'] = "";
		if (! in_array($data['format'],array("BAM","PNG","JPG") ) ){
			switch (strtolower($ext)) {
				case 'tar':
					$func   = "untar";
					$img    = "fa fa-expand";
					$linkTxt= "Uncompress";
					break;
				case 'gz':
				case 'zip':
					$func   = "unzip";
					$img    = "fa fa-expand";
					$linkTxt= "Uncompress";
				case 'tgz':
					$func   = "untar";
					$img    = "fa fa-expand";
					$linkTxt= "Uncompress";
					break;
				case 'bz2':
					$func   = "bzip2";
					$img    = "fa fa-expand";
					$linkTxt= "Uncompress";
				default :
					$func   = "zip";
					$img    = "fa fa-file-zip-o";
					$linkTxt= "Compress";
			}
			$data['compressionLink'] = "<li><a  href=\"workspace.php?op=$func&fn=".$data['_id_URL']."\" class=\"enabled\"><i class=\"$img\"></i> $linkTxt</a></li>";
			//$data['compressionLink'] = "<li><a  href=\"javascript:;\" class=\"disabled\"><i class=\"$img\"></i> $linkTxt</a></li>";
		}

		return $data;
}

//update Mongo lastjobs
function updatePendingFiles($sessionId,$singleJob=Array()){
    $SGE_updated = Array(); // jobs to be monitored in next round

    # get jobs from mongo[users][lastjobs]
    $jobData = getUserJobs($sessionId);
    //  $jobData = $_SESSION['SGE'];

    $SGE_updated=Array();
    if (count($jobData)){
      # classify jobs
      foreach ($jobData as $data){

        if (!isset($data['_id'])){
            continue;
        }
        $pid = $data['_id'];

        //get qstat info
        $j = getRunningJobInfo($pid);
        //job keeps running: maintain original job data 
        if (count($j)){
            //keep monitoring
            $data['state']  = $j['state'];
            $SGE_updated[$pid]= $data;

        //job not running : editi SGE_updated to register the change
        // and consequently reload workspace (checkPendingJobs.php)
        }else{
            $SGE_updated[$pid]=$data;
            $SGE_updated[$pid]['state']="NOT_RUNNING";
        }
      }
    }
    //update session and save to mongo 
    saveUserJobs($sessionId,$SGE_updated);
    return 1;
}



function processPendingFiles($sessionId,$files){
	$SGE_updated = Array(); // jobs to be monitored. Stored in SESSION. Updated by checkPendingJobs.php (called by ajax)
	$filesPending= Array(); // files to be listed 

	// get files already in mongo
	$filesStored = Array();
	foreach ($files as $k => $v){
		array_push($filesStored,$v['_id']);
	}
	// get jobs from mongo[users][lastjobs]
	$lastjobs = getUserJobs($sessionId);

	if (!count($lastjobs)){
		return $filesPending;
	}

//	print "JOBS DEL USER HAS [".count($lastjobs)."]JOBS <br/>";
//	var_dump($lastjobs);
//	print "</br><br/>";

	// classify jobs
	foreach ($lastjobs as $job){
		
		if (!isset($job['_id'])){
			continue;
		}
		$pid	 = $job['_id'];

		//get qstat info
		$jobSGE = getRunningJobInfo($pid);

		$title   = (isset($job['title'])?$job['title']:"Job ".$job['project']);
		$outCtrl = (isset($job['outCtrl'])?$job['outCtrl']:0);
		
		$descrip = getJobDescription($job['description'],$jobSGE,$lastjobs);

		//get qstat info
		$jobSGE = getRunningJobInfo($pid);
		
//		print "<br/>USER JOB [$pid] Title='$title' SH='".$job['shPath']."'<br/>";
		
		//set as running job
		if (count($jobSGE)){
//			print "JOB IN SGE!!! $title --  ".$job['shPath']."<br/>";
				$dummyId = createLabel()."_dummy";
				$fileDummy = Array(
					'_id'     => $dummyId,
					'pid'     => $pid,
					//'path'  => outPath,
					'title'   => $title,
					'mtime'   => strtotime($jobSGE['submission_time']),
					'size'    => "",
					'visible' => 1,
					'tool'    => $job['tool'],
					'parentDir'=> getGSFileId_fromPath($job['outDir']),
					'description'=> $descrip,
					'pending' => $jobSGE['state'],
					'shPath'  => $job['shPath'],
					'logPath' => $job['logPath']
				);
				//list job in table
				$filesPending[$dummyId] = $fileDummy;
			//}

			//update job state in mongo
			$job['state'] = $jobSGE['state'];
			$SGE_updated[$pid]=$job;

		//processing non running job
		}else{
//			print "JOB NOT RUNNING ANYMORE<br/>";
			if (!count($job['outPaths']) && !$outCtrl){
				$_SESSION['errorData']['Warning'][]="Job '$title' finished in folder ".basename($job['outDir']).". Cannot predict if successfully or not. Check folder.";
				//TODO: save everything in the folder?
				continue;
			}

			// build output list, if outCtrl
			if ($outCtrl){
				$outCtrl_rfn = $GLOBALS['dataDir']."/".$outCtrl;
//				print "JOB with control outfile: $outCtrl_rfn </br>";
				if (! is_file($outCtrl_rfn)){
					array_push($job['outPaths'],$outCtrl);
					array_push($job['metaData'],Array());
				}else{
					$json_str = file_get_contents($outCtrl_rfn);
					$json = json_decode($json_str, true);
					if (!$json || !isset($json['outPaths'])){
						$_SESSION['errorData']['Warning'][]="Job '$title' appears to be finished in folder ".basename($job['outDir']).". But error reading output control file. Check output/s metadata.";
						array_push($job['outPaths'],"force_failing");
						array_push($job['metaData'],Array());
					}else{
						foreach ($json['outPaths'] as $idx => $outPath){
//						    print "<br/>OUTPATHS IDX=$idx";
//						    var_dump($outPath);
//						    print "<br/>";
						    foreach ($outPath as $outFn => $outMeta){
							array_push($job['outPaths'],$outFn);
							array_push($job['metaData'],$outMeta);
						    }
						}
					}
				}
				//$job['state'] = "FINISHED";
				//$SGE_updated[$pid]=$job;
//				print "JOB IS NOW<br/>";
//				var_dump($job);
//				print "<br/>";
			}
		
			// checking each expected job output
			$outPaths= (isset($job['outPaths']) && !is_array($job['outPaths'])? Array($job['outPaths']):$job['outPaths']);

			for($i=0;$i<count($outPaths);$i++){
	
				// get expected job output
				$outPath  = $outPaths[$i];
				$rfn      = $GLOBALS['dataDir']."/".$outPath;
				$fileId   = getGSFileId_fromPath($outPath);

				// get metadata for job output
				$metaData = ((isset($job['metaData'][$i]))?$job['metaData'][$i]:Array());

				// get job log
				$logFile = $job['logPath'];
				if (preg_match('/^\//',$logFile)){
					$logFileP = $logFile;
					$logFile  = str_replace($GLOBALS['dataDir']."/","",$logFileP);
				}else{
					$logFileP = $GLOBALS['dataDir']."/".$logFile;
				}

//				print "JOB [$pid] OUT=$outPath with META=".count($metaData)." LOG=$logFile<br/>";			

				// job successfully finished and already in mongo. Update medatada

				if ($fileId){
					print "JOB $pid FINISHED $fileId ($outPath). Found on mongo. Adding metadata if there is any<br>";
					$metaData['validated']=1;
					$metaData = prepMetadataResult($metaData,$outPath,$job);
					
					//save updated metadata
					$ok = addMetadataBNS($fileId,$metaData);
					$fData_updated  = $GLOBALS['filesCol']->findOne(array('_id' => $fileId));
					$fMeta_updated  = $GLOBALS['filesMetaCol']->findOne(array('_id' => $fileId));
					$fData_updated['mtime'] = $fData_updated['mtime']->sec;
			
					//list new metadata in table
					$f_updated =array_merge($fData_updated,$fMeta_updated);
					$filesPending[$fileId] = $f_updated;


				// job successfully finished but not yet on mongo

				}elseif (is_file($rfn) ) {
//					print "JOB $pid FINISHED BUT NOT IN MONGO ($outPath). Saving and adding metadata if there is any<br>";
					//register file and save updated metadata
					$metaData['validated']=1;
					$fileInfo = saveResults($outPath,$metaData,$job); 

					//list new metadata in table
					if (is_array($fileInfo))
						$filesPending[$fileInfo['_id']]=$fileInfo;
					elseif($fileInfo == "0")
						$_SESSION['errorData']['error'][]="Cannot register the LOG file of the failed execution of ".basename($outPath);
					
				// jobs nor finished nor running: in error OR deleted OR SESSION[sge] not updated

				}elseif (is_file($logFileP) ){
					$logId = getGSFileId_fromPath($logFile);

					// save and show log
//					print "JOB IN ERROR $fileId storing LOG $logFile <br>";
					if (!$logId){
						$metaDataLog = prepMetadataLog($metaData,$logFile);
						$logInfo = saveResults($logFile,$metaDataLog,$job);
						if (is_array($logInfo))
							$filesPending[$logInfo['_id']]=$logInfo;
							
					// log already registered by another job output
					}else{
						break;
					}

				// job has neither log nor outfile

				}else{
//					print "JOB $pid NO log (".$logFile.") NO output ($outPath) <br>";
					$proj =  $GLOBALS['dataDir']."/".dirname($outPath);
					if (is_dir($proj)){
						$projContent = glob($proj.'/*.e[0-9]*', GLOB_BRACE);
						if (count($projContent)){
							$errFile = $projContent[0];
							if (is_file($errFile)){
								$err_fn = dirname($outPath) ."/". pathinfo($errFile,PATHINFO_BASENAME);
								$metaDataErr = prepMetadataLog($metaData,$err_fn,"ERR");
								$errInfo = saveResults($err_fn,$metaDataErr,$job);
								$filesPending[$errInfo['_id']]=$errInfo;
							}
						}else{
							$_SESSION['errorData']['Error'][]="Execution ".$job['title']." '".basename($outPath)."' failed with neither log nor error file.";
						}
					}else{
						$_SESSION['errorData']['Error'][]="Execution ".$job['title']." '".basename($outPath)."' failed with neither log nor error file.";
					}
				}
	 		}
		}
	}

	//print "<br/>FILES PENDING <br/>";
	//var_dump($filesPending);
	//print "<br/><br/>";
	//update session and save to mongo
	saveUserJobs($sessionId,$SGE_updated);
	return $filesPending;
}



function saveResults($filePath,$metaData=array(),$job=array() ){

	# NOT saving internal or temporal files
	# according to : extension, metadata['visible'] or filename
        if ((isset($metaData['visible']) && !$metaData['visible']) || in_array($ext,$GLOBALS['internalResults']) || preg_match('/^\./',basename($filePath))){
        //        return 1;
        }

	# check given filePath
	$rfn   = $GLOBALS['dataDir']."/".$filePath;
	if (preg_match('/^\//',$filePath)){
		$rfn      = $filePath;
		$filePath = str_replace($GLOBALS['dataDir']."/","",$rfn);
	}
	if (! is_file($rfn) || !filesize($rfn)){
		$_SESSION['errorData']['Error'][]="Execution result $file does not exist or has size 0. Cannot save it into database";
		return 0;
	}

	# prepare file metaData
	$metaData = prepMetadataResult($metaData,$filePath,$job);

	#save Data
	$parent= dirname($filePath);
	$fileId = createLabel();
	
	$insertData=array(
		'_id'   => $fileId,
		'owner' => $_SESSION['User']['id'],
		'size'  => filesize($rfn),
		'path'  => $filePath,
		'mtime' => new MongoDate(filemtime($rfn)),
		'parentDir' => getGSFileId_fromPath(dirname($filePath))
	);

	#save to MONGO
	$fnId = uploadGSFileBNS($filePath, $rfn, $insertData,$metaData, FALSE);

	if ($fnId){
		$insertData['mtime'] = $insertData['mtime']->sec;
		return array_merge($insertData,$metaData);
	}else{
		$_SESSION['errorData']['mongoDB'][]="Cannot save execution result 'basename($filePath)' into database. Stored only on disk";
		return 0;
	}
}


function topDir() {
	return ($_SESSION['curDir'] == $_SESSION['userId']);
}

function upDir() {
	if (!topDir())
		$_SESSION['curDir'] = dirname($_SESSION['curDir']);
}

function downDir($fn) {
	$fnData = $GLOBALS['filesCol']->findOne(array('_id' => $fn));
	if (! empty($fnData)) {
	if (isset($fnData['type']) && $fnData['type'] == "dir"){
		$_SESSION['curDir'] = $fn;
	}else{
		$_SESSION['errorData'][error][]="Cannot change directory. $fn is not a directory ";
	}
	}
}

// return sum of FS or Mongo directory (in bytes)

function getUsedDiskSpace($fn = '',$source="fs") {
    if (!$fn)
        $fn = $_SESSION['User']['id'];
    if (!preg_match('/^\//',$fn) )
	 $fn = $GLOBALS['dataDir']."/".$fn;

    if ($source = "fs"){
	    $data = explode("\t", exec("du -sb $fn"));
	    return $data[0];
    }else{
	    $fnId= getGSFileId_fromPath($fn);
	    return calcGSUsedSpace($fnId);
    }
}

// return user diskquota from mongo

function getDiskLimit($login = '') {
        if (!$login){
                $login  = $_SESSION['User']['_id'];
        }
        $sp = getUser_diskQuota($login);
        if ($sp === false){
                return $GLOBALS['disklimit'];
        }else{
                return $sp;
        }
}



/*
function navigation() {
	$cdir = $_SESSION['curDir'];

	$fnData = $GLOBALS['filesCol']->findOne(array('_id' => $cdir));
	if (empty($fnData)){
		$_SESSION['errorData']['error'][]="Current directory is not found. Restart <a href=\"".$GLOBALS['managerDir']."/gesUser.php?op=loginForm\">login</a>, please";
		return false;
	}
	$d = (isset($fnData['parentDir'])? $fnData['parentDir'] : 0);
	
	$dirs = array();
	if (!topDir()) {
		while ($d and ( $d != $_SESSION['userId'] ) ) {
			$dirs[] = "<a href=\"".$GLOBALS['managerDir']."/workspace.php?op=gotoDir&fn=$d\">" . basename($d). "</a>";
			$fnData = $GLOBALS['filesCol']->findOne(array('_id' => $d));
		if (empty($fnData))
			$_SESSION['errorData'][error][]="Directory $d not found. Error in navigation menu";
		$d = (isset($fnData['parentDir'])? $fnData['parentDir'] : 0);
		}
		$dirs[] = "<a href=\"".$GLOBALS['managerDir']."/workspace.php?op=gotoDir&fn=$d\">".basename($d)."</a>";
	}
	return join(' > ', array_reverse($dirs)) . "> " . pathinfo($cdir, PATHINFO_FILENAME);
}
*/

function formatSize($bytes) {
	$types = array('B', 'KB', 'MB', 'GB', 'TB');
	for ($i = 0; $bytes >= 1024 && $i < ( count($types) - 1 ); $bytes /= 1024, $i++);
	return( round($bytes, 2) . " " . $types[$i] );
}


function downloadFile( $rfn ){
		$fileInfo      = pathinfo($rfn);
		$fileName      = $fileInfo['basename'];
		$fileExtension = $fileInfo['extension'];
		$fileExtension = preg_replace('/_\d+$/',"",$fileExtension);
		$content_type  = (array_key_exists($fileExtension, mimeTypes()) ? mimeTypes()[$fileExtension] : "application/octet-stream");
		$size = filesize($rfn);
		$offset = 0;
		$length = $size;

		if(isset($_SERVER['HTTP_RANGE'])){
			preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
			$offset = intval($matches[1]);
			$length = intval($matches[2]) - $offset;

			$fhandle = fopen($rfn, 'r');
			fseek($fhandle, $offset); // seek to the requested offset, this is 0 if it's not a partial content request
			$data = fread($fhandle, $length);
			fclose($fhandle);

			header('HTTP/1.1 206 Partial Content');
			header('Content-Range: bytes ' . $offset . '-' . ($offset + $length) . '/' . $size);
		}
		header("Content-Disposition: attachment;filename=".$fileName);
		header('Content-Type: '.$content_type);
		header("Accept-Ranges: bytes");
		header("Pragma: public");
		header("Expires: -1");
		header("Cache-Control: no-cache");
		header("Cache-Control: public, must-revalidate, post-check=0, pre-check=0");
		header("Content-Length: ".filesize($rfn));
		$chunksize = 8 * (1024 * 1024); //8MB (highest possible fread length)

		if ($size > $chunksize){
			$handle = fopen($rfn,'rb');
			$buffer = '';
			while (!feof($handle) && (connection_status() === CONNECTION_NORMAL)) {
				$buffer = fread($handle, $chunksize);
				print $buffer;
				ob_flush();
				flush();
			}
			if(connection_status() !== CONNECTION_NORMAL) {
				echo "Connection aborted";
			}
			fclose($handle);
		}else{
			ob_clean();
			flush();
			readfile($rfn);
		}
		
}


/*
function downloadFileSmall( $rfn ){
		$fileInfo	= pathinfo($rfn);
		$fileName  = $fileInfo['basename'];
		$fileExtension   = $fileInfo['extension'];
		$content_type = (array_key_exists($fileExtension, mimeTypes()) ? mimeTypes()[$fileExtension] : "application/octet-stream");

		header("Content-Disposition: attachment;filename=\"" . basename($rfn) . "\"");
		header('Content-Type: ' . $contentType);
		header("Content-Length: " .filesize($rfn));

		print passthru("/bin/cat \"$rfn\"");
}
*/


function mimeTypes() {
	$mime_types = array(
		"log" => "text/plain",
		"txt" => "text/plain",
		"err" => "text/plain",
		"out" => "text/plain",
		"csv" => "text/plain",
		"gff" => "text/plain",
		"gff3"=> "text/plain",
		"wig"=> "text/plain",
		"bed"=> "text/plain",
		"bedgraph"=> "text/plain",
		//"sh" => "application/x-sh",
		"sh" => "text/plain",
		"pdb" => "chemical/x-pdb",
		"crd" => "chemical/x-pdb",
		"xyz" => "chemical/x-xyz",
		"cdf" => "application/octet-stream",
		"xtc" => "application/octet-stream",
		"trr" => "application/octet-stream",
		"gro" => "application/octet-stream",
		"dcd" => "application/octet-stream",
		"exe" => "application/octet-stream",
		"gtar" => "application/octet-stream",
		"bam"=> "application/octet-stream",
		"sam"=> "application/octet-stream",
		"tar" => "application/x-tar",
		"gz" => "application/application/x-gzip",
		"tgz" => "application/application/x-gzip",
		"z" => "application/octet-stream",
		"rar" => "application/octet-stream",
		"bz2" => "application/x-gzip",
		"zip" => "application/zip",
		"h" => "text/plain",
		"htm" => "text/html",
		"html" => "text/html",
		"gif" => "image/gif",
		"bmp" => "image/bmp",
		"ico" => "image/x-icon",
		"jfif" => "image/pipeg",
		"jpe" => "image/jpeg",
		"jpeg" => "image/jpeg",
		"jpg" => "image/jpeg",
		"rgb" => "image/x-rgb",
		"svg" => "image/svg+xml",
		"png" => "image/png",
		"tif" => "image/tiff",
		"tiff" => "image/tiff",
		"ps" => "application/postscript",
		"eps" => "application/postscript",
		"js" => "application/x-javascript",
		"pdf" => "application/pdf",
		"doc" => "application/msword",
		"xls" => "application/vnd.ms-excel",
		"ppt" => "application/vnd.ms-powerpoint",
		"tsv" => "text/tab-separated-values");
	return $mime_types;
}

/*
function check_key_repeats($key, $hash) {
	if (!isset($key) || !isset($hash)) {
		return NULL;
	}
	if (array_key_exists($key, $hash)) {
		$key++;
		$key = check_key_repeats($key, $hash);
		return $key;
	} else {
		return $key;
	}
}
*/

function return_bytes($val) {
	$val = trim($val);
	$last = strtolower($val[strlen($val)-1]);
	switch($last) {
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	}
	return $val;
}

?>
