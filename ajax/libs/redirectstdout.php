<?php
/* 
 * @file
 *
 * Redirect stdout to file (Useful on debug ajax)
 */ 

//$debugLogRedirect = false;
$debugLogRedirect = true;

class RedirectStdout
{
	var $nameFileLog;
	var $nameLog = "";
	var $isValid = false;
	var $strTimeOnStartLog = 0;

	function __construct($nameFileLog = "/tmp/TreeNodeAjax.log")
	{
		$this->nameFileLog = $nameFileLog;
		$this->nameLog = getNameFileCaller();
		$this->isValid = true;
		$this->strTimeOnStartLog = date("Y/m/d H:i:s ");

		ob_start();
	}

	function flush()
	{
		global $debugLogRedirect;
		if ($this->isValid == false) return;

		if ($debugLogRedirect) {
			$output = ob_get_contents();
			// Add date time string at header of each line:
			$length = strlen($output);
			if (substr($output, $length - 1, 1) == "\n") {
				    $output = substr($output, 0, $length - 1);
			}
			$output = $this->strTimeOnStartLog . preg_replace("/\n/", "\n" . $this->strTimeOnStartLog, $output);
	
			$handleFile = fopen($this->nameFileLog, "a");
			fwrite($handleFile, $this->strTimeOnStartLog . "*** start log: " . $this->nameLog . " ***\n");
			fwrite($handleFile, $output . "\n");
			fwrite($handleFile, $this->strTimeOnStartLog . "***   end log: " . $this->nameLog . " ***\n");
			fclose($handleFile);
		}
		ob_end_clean();
		
		$this->isValid = false;
	}

	function __destruct()
	{
		if ($this->isValid) {
			// just discard log.
			ob_end_clean();
			$this->isValid = false;
		}
	}

}

?>
