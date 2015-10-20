<?php
/**
 * util.db.php
 * 
 * Utilities for access DB.
 * 
 */

require_once("MDB2.php");
require_once("utils.php");

class Database
{
	var $handleDb = false;
	var $debug = true;

	public function __construct($dsn)
	{
		$this->handleDb =& MDB2::connect($dsn);
		$this->handleDb->setFetchMode(MDB2_FETCHMODE_ASSOC);
		// don't assume '' as null
		$this->handleDb->setOption('portability',
		   MDB2_PORTABILITY_ALL & (~MDB2_PORTABILITY_EMPTY_TO_NULL));
		// nextId() compatible to PEAR::DB
		$this->handleDb->setOption('seqcol_name', 'id');
		$this->handleDb->setCharset('utf8');
		$this->handleDb->loadModule('Extended');
	}
	
	public function __destructor()
	{
		$this->handleDb->disconnect();
		
	}

	function getIndexInsertedLast()
	{
		$q = "SELECT LAST_INSERT_ID()";
		$result = $this->getOne($q);
		
		return $result;
	}


	function dbGetRand ($size = 20) {
		$q = "SELECT RAND()";
		$r = $this->getOne($q);

		$r = str_replace("0.", "", $r);
		$r = preg_replace("/^0+/", "", $r);
		$r = substr($r, 0, $size);

		return $r;
	} // end func dbGetRand

	function executeSql($q, $a = array())
	{
		if ($this->debug) echo getStrInfoFuncCaller() . "<br />\n";

		$statement = $this->handleDb->prepare($q);
		if (MDB2::isError($statement)) {
			if ($this->debug) echo "Error statement : " . $statement->getMessage() . "\n";
			return false;
		}
		$result = $statement->execute($a);
		if (MDB2::isError($result)) {
			if ($this->debug) echo "Error result: " . $result->getMessage() . "\n";
			$statement->free();
			return false;
		}

		$statement->free();
		$result->free();

		return true;
	}

	function getRow($q, $a = array())
	{
		if ($this->debug) echo getStrInfoFuncCaller() . "<br />\n";

		$result = $this->handleDb->extended->getRow($q, null, $a, null);

		if (MDB2::isError($result)) {
			if ($this->debug) echo "Error result: " . $result->getMessage() . "\n";
			return false;
		}

		return $result;
	}

	function getRows($q, $a = array())
	{
		if ($this->debug) echo getStrInfoFuncCaller() . "<br />\n";

		$statement = $this->handleDb->prepare($q, null, MDB2_PREPARE_RESULT);
		if (MDB2::isError($statement)) {
			if ($this->debug) echo "Error statement : " . $statement->getMessage() . "\n";
			return false;
		}

		$query = $statement->execute($a);

		if (MDB2::isError($query)) {
			if ($this->debug) echo "Error query : " . $query->getMessage() . "\n";
			return false;
		}

		$rows = array();
		while ($row = $query->fetchRow()) {
			array_push($rows, $row);
		}
		$statement->free();
		$query->free();
	
		return $rows;
	}

	function getRowsOneColumn($q, $a = array())
	{
		if ($this->debug) echo getStrInfoFuncCaller() . "<br />\n";

		$statement = $this->handleDb->prepare($q, null, MDB2_PREPARE_RESULT);
		if (MDB2::isError($statement)) {
			if ($this->debug) echo "Error statement : " . $statement->getMessage() . "\n";
			return false;
		}

		$query = $statement->execute($a);

		if (MDB2::isError($query)) {
			if ($this->debug) echo "Error query : " . $query->getMessage() . "\n";
			return false;
		}

		$rows = array();
		while ($row = $query->fetchRow()) {
			$values = array_values($row);
			array_push($rows, $values[0]);
		}
		$statement->free();
		$query->free();

		return $rows;
	}

	function getOneResult($q, $a = array())
	{
		return $this->getOne($q, $a);
	}

	function getOne($q, $a = array())
	{
		if ($this->debug) echo getStrInfoFuncCaller() . "<br />\n";

		$result = $this->handleDb->getOne($q, null, $a, null);
		if (MDB2::isError($result)) {
			if ($this->debug) echo "Error result : " . $result->getMessage() . "\n";
			return false;
		}

		return $result;
	}

	function escapePattern($str)
	{
		if ($this->debug) echo getStrInfoFuncCaller() . "<br />\n";

		return $this->handleDb->escapePattern($str);
	}

}

?>
