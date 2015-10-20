<?php

/**
* Returns str of info where the current func is called.
*/
function getStrInfoFuncCaller($numTraceBack = 1)
{
	$dbg = debug_backtrace();
	
	$stackTrace = $dbg[$numTraceBack];
	$nameFunc = $stackTrace["function"];
	$nameFile = $stackTrace["file"];
	$indexLine = $stackTrace["line"];
	$args = $stackTrace["args"];
	
	$strInfoFUnc = "${nameFile}:${indexLine}: ${nameFunc}(" . print_r($args, true) . ")";
																		  
	return $strInfoFUnc;
}

function getNameFileCaller($numTraceBack = 1)
{
	$dbg = debug_backtrace();
	$stackTrace = $dbg[$numTraceBack];
	$nameFile = $stackTrace["file"];
	$indexLine = $stackTrace["line"];

	return "$nameFile(${indexLine})";
}

?>
