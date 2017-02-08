<?php 

// system call in a subprocess
function subprocess($cmd, &$stdout=null, &$stderr=null,$cwd=null) {
        $proc = proc_open($cmd,[
                1 => ['pipe','w'],
                2 => ['pipe','w'],
                ],$pipes,$cwd,null);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        return proc_close($proc);
}


// define JBrowse tracktype from file format
function format2trackType($format,$fn=NULL){
        if (!isset ($format) ){
                return FALSE;
        }
        $program = NULL;
        if ($fn){
                if (preg_match('/^([A-Z]+)_/',basename($fn),$m) ){
                        $program = $m[1];
                }
        }
        switch ($format){
        case "BAM":
                $type = "BAM";
                break;
        case "GTF":
        case "GFF":
        case "GFF3":
                if ($program && $program == "ND"){
                        $type = "GFF_ND";
                }elseif($program && $program == "NR"){
                        $type = "GFF_NR";
                }elseif($program && $program == "TSS"){
                        $type = "GFF_TX";
                }elseif($program && $program == "P"){
                        $type = "GFF_P";
                }elseif($program && $program == "NFR"){
                        $type = "GFF_NFR";
                }elseif($program && $program == "STF"){
                        $type = "GFF_GAU";
                }else{
                        $type = "GFF";
                }
                break;
        case "BW":
        //case "BEDGRAPH":
        //case "WIG":
                if ($program && $program == "P"){
                        $type = "BW_P";
                }else{
                        $type = "BW";
                }
                break;
        default:
                $type = 0;
        }
        return $type;
}


function createLink ($source, $target){
	if (is_file($source))
                 unlink($source);
	if (is_file($target) || is_link($target))
        	unlink($target);
	touch($source);
	symlink($source,$target);
}



//return html from
//SESSION['errorData'] = Array( 'seccionA' => Array( 'error msg A1', 'error msg A2'))
function printErrorData($targetSeccion=0){
   $txt="";
    foreach ($_SESSION['errorData'] as $seccion =>$lines) {
	if ($targetSeccion && $targetSeccion != $seccion )
		continue;
        $txt .="<b>$seccion</b></br>";
        if (!is_array($lines))
		$lines[0] = $lines;
	$txt .= "<span style=\"margin-left:45px;\"></span>";
	$txt .= join("<br/><span style=\"margin-left:45px;\"></span>",$lines);
	$txt .= "<br/>";

    }
    unset($_SESSION['errorData']);
    return $txt;
}



function fromPrefix2Program($prefix){

        $tools   = $GLOBALS['toolsCol']->find(array('prefix' => array('$exists'=> true)));
	if (empty($tools)){
		$_SESSION['errorData']['Error'][]="Internal Error. Cannot extract any prefix from 'tools' collection";
		return 0;
	}
	foreach ( $tools as $toolId => $d ){
		if ($d['prefix'] == $prefix)
			return $d['title'];
	}
	$_SESSION['errorData']['Warning'][]=" Prefix '$prefix"."_' not registered. File descriptions may not be complete.";
	return 0;
}


// build text description for running jobs in datatables
function getJobDescription($descrip0,$jobSGE,$lastjobs){

	$descrip = ($descrip0?$descrip0."<br/>":"");
        if ($jobSGE['state'] == "RUNNING"){
		$descrip = "<b>Job in course<b/><br/>".$descrip;

	}elseif($jobSGE['state'] == "HOLD"){
	        $descrip .= "<br><strong>Job waiting</strong>";

		# get info for dependent jobs from lastjobs
	        if (isset($jobSGE['jid_predecessor_list'])){
	                $depText = "";  
	               $depPids = explode(",",$jobSGE['jid_predecessor_list']);
	               foreach ($depPids as $depPid){
	                        if (isset($lastjobs[$depPid])){
	                                $depText.=basename($lastjobs[$depPid]['out'][0])." ";
	                        }
	                }
	                if ($depText)
	                        $descrip .= " for predecessor analyses to finish: $depText";
	        }

	}elseif($jobSGE['state'] == "ERROR"){
	        $descrip .= " Job in error.";
	        if (isset($jobSGE['error reason    1'])){
	                $descrip .= "<br/>".$jobSGE['error reason    1'];
	        }
	}
	return $descrip;
}

// build text description from systematic execution file names
function getDescriptionFromFN ($fn,$prefix=0){
        $descr  = "";
	$ext = strtoupper(pathinfo($fn,PATHINFO_EXTENSION));
	if (!$prefix){
	   if (preg_match('/([A-Z]+)_.+\.(\w+)$/',$fn,$m)){
                $prefix = $m[1];
                $ext = strtoupper($m[2]);
	   }
	}
	if ($ext){
	    switch ($ext){
               	case "SH":
			$descr="Execution file";
			break;
               	case "LOG":
			$descr="Log file";
			break;
                case "GFF":
                case "BW":
			$descr="Result file";
			break;
                case "PNG":
			$descr="Image file";
			break;
                case "BAM":
			$descr="BAM file";
			if (!$prefix)
                		$descr.=" is being sorted (if needed), indexed, and preprocessed for running Nucleosome Dynamics ";
			break;
                default:
			$descr="$ext file";
	    }
            if (preg_match('/E\d+/',$ext)){
		$descr= "ERROR file";
	    }
	}
	if ($prefix){
	    $program = fromPrefix2Program($pre);
            if ($program)
            	$descr.= " from  $program";
	}
        return $descr;
}



//adds regular metadata
function saveMetadataUpload($fn,$request,$validationState){

        // filters known metadata fields
        $insertMeta = prepMetadataUpload($request,$validationState);

        // save to mongo        
        $r = modifyMetadataBNS($fn,$insertMeta);
        return $r;
}


// filters uploadForm2 request and formats mongo file metadata
function prepMetadataUpload($request,$validationState=0){
        $fnPath    = getAttr_fromGSFileId($fn,'path');

        $format    = (isset($request['format'])?$request['format']:"UNK");
        $validated = $validationState;
        $tracktype = format2trackType($format,$fnPath);
        $visible   = (isset($insertMeta['visible'])?$insertMeta['visible']:"1");

        // compulsory metadata
        $insertMeta=array(
            'format'     => $format,
            'validated'  => $validated,
            'trackType'  => $tracktype,
            'visible'    => $visible,
        );
        // GFF, BAM, BW,.. metadata
        if (isset($request['refGenome']))   {$insertMeta['refGenome']  = $request['refGenome'];}
        // BAM metadata
        if (isset($request['paired']))      {$insertMeta['paired']     = $request['paired'];}
        if (isset($request['sorted']))      {$insertMeta['sorted']     = $request['sorted'];}
        if (isset($request['description'])) {$insertMeta['description']= $request['description'];}
        //  results metadata
        if (isset($request['shPath']))      {$insertMeta['shPath']     = $request['shFile'];}
        if (isset($request['logPath']))     {$insertMeta['logPath']    = $request['logFile'];}
        if (isset($request['inPaths']))     {$insertMeta['inPaths']    = $request['inPaths'];}
        if (isset($request['outPaths']))    {$insertMeta['outPaths']   = $request['outPaths'];}

        return  $insertMeta;
}


//formats and completes mongo file metadata from $meta and $lastjob
function prepMetadataResult($meta,$fnPath=0,$lastjob=Array() ){
        if ($fnPath){
                $extension = pathinfo("$fnPath",PATHINFO_EXTENSION);
                $extension = preg_replace('/_\d+$/',"",$extension);
	        if (preg_match('/^E\d+$/',strtoupper($extension)) )
	                $extension="ERR";
        }

        if (!isset($meta['format']) && $fnPath)
                $meta['format']= strtoupper($extension);
        
        if (!isset($meta['tracktype']) && $fnPath )
                $meta['tracktype']=format2trackType($meta['format'],basename($fnPath));
        
        if (!isset($meta['inPaths']) && isset($lastjob['inPaths']) )
                $meta['inPaths']=$lastjob['inPaths'];

        if (!isset($meta['shPath']) && isset($lastjob['shPath']) )
                $meta['shPath']=$lastjob['shPath'];

        if (!isset($meta['logPath']) && isset($lastjob['logPath']) )
                $meta['logPath']=$lastjob['logPath'];

        if (!isset($meta['tool']) && isset($lastjob['tool']))
                $meta['tool']=$lastjob['tool'];

        if (!isset($meta['refGenome']) && in_array($meta['format'],array("BAM","GFF","GFF3","BW")) ){
            if (isset($meta['inPaths']) ){
		$inp = $meta['inPaths'][0];
                $inpObj = $GLOBALS['filesMetaCol']->findOne(array('path'  => $inp));
                if (!empty($inpObj) && isset($inpObj['refGenome']) ){
                        $meta['refGenome']= $inpObj['refGenome'];
                }
            }
            if (!isset($meta['refGenome']) && $fnPath ){
                $fnCore   = "";
                $refGenome= "";
                $ext = $meta['format'];
                if (preg_match("/^[A-Z]+_\(\w+\)(.+)-\(\w+\)(.+)\.$ext/i",basename($fnPath),$m)) {
                        $fnCore = $m[1];
                }elseif (preg_match("/^[A-Z]+_\(\w+\)(.+)-/",basename($fnPath),$m)) {
                         $fnCore = $m[1];
                }elseif (preg_match("/^[A-Z]+_(.+)\.$ext/i",basename($fnPath),$m)) {
                        $fnCore = $m[1];
                }elseif (preg_match("/^[A-Z]+_(.+)\./i",basename($fnPath),$m)) {
                        $fnCore = $m[1];
                }else{
                        $fnCore = preg_replace("/.$ext/i","",basename($fnPath));
                        $fnCore = preg_replace("/^.*_/","",$fnCore);
                }
                $reObj = new MongoRegex("/".$_SESSION['User']['id'].".*".$fnCore."/i");
                $relatedBAMS = $GLOBALS['filesMetaCol']->find(array('path'  => $reObj));
                if (!empty($relatedBAMS)){
                       $relatedBAMS->next();
                       $BAM = $relatedBAMS->current();
                       if (!empty($BAM))
                                $meta['refGenome'] = $BAM['refGenome'];
                }
            }
        }
	if (!isset($meta['description']) ){
		$prefix = 0;
		if ($meta['tool']){
			$tool = $GLOBALS['toolsCol']->findOne(array('_id' => $meta['tool']));
			$prefix = $tool['prefix'];
		}
		$meta['description'] = getDescriptionFromFN(basename($fnPath),$prefix);
	}
        if (!isset($meta['validated'])){
                $meta['validated']=1;
        }
        if (!isset($meta['visible']) && $fnPath){
                $meta['visible']=((in_array($extension,$GLOBALS['internalResults']))?0:1);
        }
        return $meta;
}

//completes $meta for log files based on expected outfile
function prepMetadataLog($metaOutfile,$logPath=0,$format="LOG"){
        $metaLog = $metaOutfile;
        $metaLog['format']    = $format;
        $metaLog['tracktype'] = format2trackType($metaLog['format'],$logPath);
        $metaLog['validated'] = 1;
        $metaLog['visible']   = 1;
        return $metaLog;
}



?>
