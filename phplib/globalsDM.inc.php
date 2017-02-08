<?php

//$GLOBALS['logFile']  = "/orozco/services/Rdata/Web/NucDyn-dev.log";

$GLOBALS['fsStyle'] = "fsMongo"; # fsMongo,fs,mongo 


//lib paths
//$GLOBALS['baseDir']  = $_SERVER['DOCUMENT_ROOT']."/datamanager/";
//$GLOBALS['htmlib']   = $GLOBALS['baseDir']."/htmlib";
//$GLOBALS['classlib'] = $GLOBALS['baseDir']."/phplib/classes";

//file paths
$GLOBALS['dataDir']    = "/orozco/services/Rdata/MuG/MuG_userdata/";
$GLOBALS['tmpDir']     = "/orozco/services/Rdata/MuG/MuG_userdata/";
$GLOBALS['refGenomes'] = "/orozco/services/Rdata/MuG/refGenomes";
$GLOBALS['sampleData'] = "/orozco/services/Rdata/MuG/sampleData";


//datamanger templates
$GLOBALS['htmlib'] = "/var/www/html/htmlib";

//
$GLOBALS['caduca']            = "40"; //days
$GLOBALS['disklimit']         = 12*1024*1024*1024;
$GLOBALS['disklimitAnon']     = 4*1024*1024*1024;
$GLOBALS['limitFileSize']     = '900M';
$GLOBALS['max_execution_time']= 2000;


$GLOBALS['refGenomes_names'] = Array(
		'R64-1-1' => "Saccharomyces cerevisiae (R64-1-1)"
	);

$GLOBALS['internalResults']  = Array(
		"RDATA",
		"COV",
		"BAI"
	);

?>
