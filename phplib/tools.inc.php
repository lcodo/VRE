<?php

function createCmd($analysisId,$analysisParams){
	$cmd="";
        switch($analysisId){
                case 'pyDock':
                        $cmd = createCmd_pyDock($analysisParams);
                        break;
                case 'ND':
                        $cmd = createCmd_ND($analysisParams);
                        break;
                default:
                        $_SESSION['ErrorData']['Error'][]="Given '$analysisId' has no associated CMD creator";
                        return 0;
        }
        return $cmd;
}

function createCmd_pyDock($analysisParams){
    $executable = "/home/user/bin/mug/docking_dna.py";
    
    $cmd="$executable ";
    foreach ($analysisParams as $key => $value) {
        if (!is_array($value)){
	    if ($key == "description"){
		continue;
	    }
            if ($value == ""){
                $_SESSION['errorData']['Error'][]="Missing '$toolId' parameter: $key";
                return 0;
            }
            if ( $key == "ligand" || $key == "receptor" ){
		$fn  = getAttr_fromGSFileId($value,'path');
		$rfn = $GLOBALS['dataDir']."/$fn";
                $cmd .= " --$key '$rfn'";
            }else{
                 $cmd .= " --$key $value";
            }   
        }
    }
    $cmd.= " --abs_path ".$GLOBALS['dataDir']." --user_id ". $_SESSION['User']['id'];
    return $cmd;
}

function launchTool(&$jobMeta,$cmds){
	$toolId = $jobMeta['tool'];
	$tool   = $GLOBALS['toolsCol']->findOne(array('_id' => $toolId));
	if (empty($tool)){
                $_SESSION['errorData']['Error'][]="Tool '$toolId' not registered. Cannot submit execution";
                return 0;
	}
	$r=0; 
        switch($toolId){
                case 'pydock':
                        $r = launchTool_pyDock($jobMeta,$cmds);
                        break;
                case 'NDFW':
                        $r = launchTool_NDFW($jobMeta,$cmds);
                        break;
                default:
                        $_SESSION['ErrorData']['Error'][]="Given tool '$tool' has no associated CMD launcher";
                        return 0;
        }
        return $r;
}

function launchTool_PyDock(&$jobMeta,$cmds){

	$html="";

	$cmd    = $cmds['pyDock'];
	$tool   = $GLOBALS['toolsCol']->findOne(array('_id' => $jobMeta['tool']));
	$proj   = basename($jobMeta['outDir']);
	$tmpdir = $GLOBALS['dataDir']."/".$_SESSION['User']['id']."/.tmp";

	$jobMeta['title']    = $tool['title']; 
	$jobMeta['outPaths'] = Array();
	$jobMeta['metaData'] = Array();
	$jobMeta['outCtrl']  = $jobMeta['outDir']."/.result.json"; 
	$jobMeta['shPath']   = $jobMeta['outDir']."/$proj.sh";
	$jobMeta['logPath']  = $jobMeta['outDir']."/$proj.log";;

        $cores    = $tool['cores'];

        $outFile  = Array("NR_".$files[$i]['prefix'].".gff");

        // Queue CSH files generation (nucleR):
        
	print "<br/>GENERATING QUEUE SH: <br/>queueToolPyDock(<br/>- ".$GLOBALS['dataDir']."/".$jobMeta['shPath']."<br/>- ".$GLOBALS['dataDir']."/".$jobMeta['logPath']."<br/>- $tmpdir<br/>- $cmd<br/>";
	$shFn = queueToolPyDock($GLOBALS['dataDir']."/".$jobMeta['shPath'],$GLOBALS['dataDir']."/".$jobMeta['logPath'],$tmpdir,$cmd); 
	
	// Queuing processes
	$pid  = execJob($GLOBALS['dataDir']."/".$jobMeta['outDir'],$GLOBALS['dataDir']."/".$jobMeta['shPath'],$tool);

	if (!$pid){
                $_SESSION['errorData']['error'][]="Cannot enqueue job.  SH file was: ".$GLOBALS['dataDir']."/".$jobMeta['shPath'] ;
                ?><script>focusWorkspace();</script><?php
                return 0;
        }

	$jobMeta['_id'] = $pid;
        return 1;

	//$SGE_updated[$pid]= Array('_id' => $pid,
        //                          'out' => $files[$i]['nucleRFN'],
        //                          'log' => str_replace(".sh",".log","$wdFN/$cmdNucleR"),
        //                          'sh' => "$wdFN/$cmdNucleR",
        //                          'in'  => $files[$i]['fn']
        //                      );
}

//creates SH for enqueuing PyDock
function queueToolPyDock($sh,$log,$tmpdir,$cmd) {

    $executable = "/home/user/bin/mug/docking_dna.py";
    $bamTmp= "$tmpdir/".basename($bamFn);
    $bai   = "$bamFn.bai";

    $fout = fopen($sh, "w");
    if (!$fout){
        $_SESSION['errorData']['error'][]="Cannot create executable file '$sh'.";
        return 0;
    }
    fwrite($fout, "#!/bin/bash\n");
    fwrite($fout, "# generated by MuG VRE\n");
    fwrite($fout, "cd $tmpdir\n");

    fwrite($fout, "\n# Running PyDock tool ...\n");
    fwrite($fout, "\necho '# Start time:' \$(date) > $log\n");

    fwrite($fout, "\n# Loading environment ...\n");
    fwrite($fout, "source /home/user/bin/mug/load_env.sh\n");

    fwrite($fout, "$cmd >> $log 2>&1\n");
    fwrite($fout, "echo '# End time:' \$(date) >> $log\n");

    return basename($sh);
}

###########
##########


function parseSHFile_pyDock($rfn){

	$cmdsParsed = array();

	$cmds = preg_grep("/\.py /",file($rfn));
	$cwd  = str_replace("cd ","",join("",preg_grep("/^cd /", file($rfn))));

	$n=1;
        foreach ($cmds as $cmd){

		$cmdsParsed[$n]['cmdRaw']    = $cmd;
		$cmdsParsed[$n]['cwd']       = $cwd;

		$cmdsParsed[$n]['prgPrefix'] = "";      # toolId as appears in help
		$cmdsParsed[$n]['prgName']   = "";      # tool executable name for table title
		$cmdsParsed[$n]['params']    = array(); # paramName=>paramValue

		if (preg_match('/^#/',$cmd))
                	continue;
		if (preg_match('/^(.[^ ]*) (.[^>]*)(\d*>*.*)$/',$cmd,$m)){
	                $executable =  ($m[1]? basename($m[1]):"No information" );
	                $paramsStr  =  ($m[2]? $m[2]:"" );
			$log        =  ($m[3]? $m[3]:"" );
			
			$cmdsParsed[$n]['prgName']  = $executable;
                	foreach (split("--",$paramsStr) as $p){
				trim($p);
	                        if (!$p)
	                                continue;
	                        list($k,$v) = split(" ",$p);
				if (strlen($k)==0 && strlen($v)==0)
					continue;
				if (!$v)
					$v="";

                                $v  = str_replace($GLOBALS['dataDir']."/".$_SESSION['User']['id']."/","",$v);
				$cmdsParsed[$n]['params'][$k]=$v;
			}
		}
		$n++;
        }
	return $cmdsParsed;
}

function parseSHFile_BAMval($rfn){

	$cmdsParsed = array();

	$cmds = preg_grep("/samtools /",file($rfn));
	$cwd  = str_replace("cd ","",join("",preg_grep("/^cd /", file($rfn))));

	$n=1;
        foreach ($cmds as $cmd){
		$cmdsParsed[$n]['cmdRaw']    = $cmd;
		$cmdsParsed[$n]['cwd']       = $cwd;

		$cmdsParsed[$n]['prgPrefix'] = "";      # toolId as appears in help
		$cmdsParsed[$n]['prgName']   = "";      # tool executable name for table title
		$cmdsParsed[$n]['params']    = array(); # paramName=>paramValue

		
		if (preg_match('/^#/',$cmd))
                	continue;
                if (preg_match('/^(.[^ ]*) (.[^ ]*) (.[^>]*)(\d*>*.*)$/',$cmd,$m)){
                        $program    =  ($m[1]? basename($m[1]):"" ); #samtools
	                $executable =  ($m[2]? $m[2]:"" );
	                $paramsStr  =  ($m[3]? $m[3]:"" );
			$log        =  ($m[4]? $m[4]:"" );
			
			$cmdsParsed[$n]['prgName']  = $program ." ".$executable;

			trim($paramsStr);
                        $paramsRaw  = split(" ",$paramsStr);
                        $p_ant="";
                        foreach ($paramsRaw as $p){
                            if (!$p)
				continue;
			    $p = str_replace($GLOBALS['dataDir']."/".$_SESSION['User']['id']."/","",$p);
			    if (preg_match('/^ *(-\w+)/',$p,$m)){
				if ($p_ant){
					//array_push($params, $p_ant." "."NULL");
					$cmdsParsed[$n]['params'][$p_ant]= "NULL";
				}
                                $p_ant=$m[1];
			    }else{
                            	if ($p_ant){
					//array_push($params, $p_ant." ".$p);
					$cmdsParsed[$n]['params'][$p_ant]=$p;
					$p_ant="";
				}else{
					//array_push($params,"infile"." ".$p);
					$cmdsParsed[$n]['params']['infile']=$p;

				}
			    }
			}
		}
		$n++;
	}
	return $cmdsParsed;
	
}	

function parseSHFile_NuclDynWF($rfn){
	$prgPrefix = "";
	$prgName   = "";
	$cmds = preg_grep("/Rscript|samtools/", file($rfn));
	$cwd = str_replace("cd ","",join("",preg_grep("/^cd /", file($rfn))));
	$n=1;

        foreach ($cmds as $cmd){
                $params    = Array();
                $prgPrefix = "#";
                $prgName   = $cmd;

                if (preg_match('/^#/',$cmd))
                        continue;
                if (preg_match('/^(.[^ ]*) (.[^ ]*) (.[^>]*)(\d*>*.*)$/',$cmd,$m)){
                        $program    =  ($m[1]? $m[1]:"" ); #Rscript
                        $executable =  ($m[2]? basename($m[2]):"No information" );
                        $paramsStr  =  ($m[3]? $m[3]:"" );
                        $log        =  ($m[4]? $m[4]:"" );


                        if (preg_match('/Rscript/',$program)){
                                $params    = split("--",$paramsStr);
                                $prgPrefix = (isset($getPrefix[$executable])?  $getPrefix[$executable] : "#" );
                                $prgName   = (isset($GLOBALS['ProgFullName'][$prgPrefix])? $GLOBALS['ProgFullName'][$prgPrefix]: $executable );
                        }elseif(preg_match('/samtools/',$program)){
                                trim($paramsStr);

                                $paramsRaw  = split(" ",$paramsStr);
                                $p_ant="";
                                foreach ($paramsRaw as $p){
                                        if (!$p)
                                                continue;
                                        if (preg_match('/^ *(-\w+)/',$p,$m)){
                                                if ($p_ant){
                                                        array_push($params, $p_ant." "."NULL");
                                                }
                                                $p_ant=$m[1];
                                        }else{
                                                if ($p_ant){
                                                        array_push($params, $p_ant." ".$p);
                                                        $p_ant="";
                                                }else{
                                                        array_push($params,"infile"." ".$p);
                                                }

                                        }
                                }
                                $prgPrefix = (isset($getPrefix[$executable])?  $getPrefix[$executable] : "#" );
                                $prgName   = (isset($GLOBALS['ProgFullName'][$prgPrefix])? $GLOBALS['ProgFullName'][$prgPrefix]: "samtools ". $executable );
                        }else{
                                $prgName   = $program;
                        }
                }
                $html.= "<table style=\"margin:1.8em 0 0.5em\">
                          <tr>
				<th><b>Analysis #$n></b></th>
				<th><a style=\"color:white;text-decoration:underline;\" target=\"_blank\" href=\"help.php?id=analyzes#$prgPrefix\" ></b>$prgName</b></a></th>
			</tr>";
                foreach ($params as $p){
                        if (!$p)
                                continue;
                        list($k,$v) = split(" ",$p);
                        if ($k){
                                $v  = str_replace($GLOBALS['dataDir']."/".$_SESSION['User']['uniqId']."/","",$v);
                                if (!preg_match('/PP/',$prgPrefix) )
                                        $v  = str_replace(".RData","",$v);

                                $html.= "<tr><td>$k</td><td>$v</td></tr>";
                        }
                }
		$html.="</table>";


		$html .= "<a href=\"javascript:toggleVis('raw$n');\"><img src=\"datamanager/images/info.png\" style=\"height:24px;vertical-align:bottom;\"/>View raw comand</a>";
		$html .= "<div id=\"raw$n\" style=\"display:none;visibility:hidden;\">
                  <table style=\"border-color:#0a7964;\">
                        <tr><td>Raw command</td>
                                <td>$cmd</td>
                        </tr>
                        <tr><td>Working directory</td>
                                <td>$cwd</td>
                        </tr>
                  </table>
                </div>";
                $n++;
        }
	return $html;
}

?>

