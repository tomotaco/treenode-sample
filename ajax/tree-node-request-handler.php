<?php

require_once("config.php");
require_once("libs/redirectstdout.php");
require_once("libs/TreeNode.php");

$redirector = new RedirectStdout();

$handler = new TreeNodeRequestHandler($dsn);

if (!array_key_exists("command", $_REQUEST)) {
	$result = getResultError("No command.");
} else {
	$command = $_REQUEST["command"];
	if (!$handler->hasMethod($command)) {
		$result = getResultError("Unknown command: " . $command);
	} else {
		$result = $handler->$command($_REQUEST);
	}
}

$redirector->flush();
error_reporting(0);
echo json_encode($result) . "\n";

	
function getResultError($messageError)
{
	return [
		"result" => false,
		"error" => $messageError
	];
}

class TreeNodeRequestHandler
{
	var $treeNode;

	public function __construct($dsn)
	{
		$this->treeNode = new TreeNode($dsn);		
	}
	
	public function hasMethod($nameMethod)
	{
		$classReflection = new ReflectionClass($this);
		try {
			$methodReflection = $classReflection->getMethod($nameMethod);
			return $methodReflection->isPublic();
		} catch (ReflectionException $e) {
			return false;
		}
	}
	
	public function addNodeTopLevel($requests)
	{
		$idNode = $this->treeNode->addNodeTopLevel();
		return [
			"result" => true,
			"idNode" => $idNode
		];
	}

	function addNodeChild($requests)
	{
		$idNode = $requests["idNode"];
		$idNode = $this->treeNode->addNodeChild($idNode);
		return [
			"result" => true,
			"idNode" => $idNode
		];
	}

	function deleteNode($requests)
	{
		$idNode = $requests["idNode"];
		$result = $this->treeNode->markNodeAsDeleted($idNode);
		return [
			"result" => $result
		];
	}


	function getNode($requests)
	{
		$idNode = $requests["idNode"];
		$node = $this->treeNode->getNode($idNode);
		return [
			"result" => true,
			"node" => $node
		];
	}

	function setNode($requests)
	{
		$idNode = $requests["idNode"];
		$body = $requests["body"];
		$result = $this->treeNode->setNode($idNode, $body);
		return [
			"result" => $result
		];
	}

	function getNumNodes($requests)
	{
		return [
			"result" => true,
			"numNodes" => $this->treeNode->getNumNodes(1)
		];
	}


	function getNodesAllByDate($requests)
	{
		$isAscending = ($requests["isAscending"] == "true") ? true : false;
		$numNodes = $requests["numNodes"];
		$offset = $requests["offset"];

		return [
			"result" => true,
			"nodes" => $this->treeNode->getNodes(1,
				"date_modified", $isAscending, $offset, $numNodes)
		];
	}

	function getNodesAllByThread($requests)
	{
		$isAscending = $requests["isAscending"]; 
		$numNodes = $requests["numNodes"];
		$offset = $requests["offset"];

		return [
			"result" => true,
			"nodes" => $this->treeNode->getChildNodesWithDepthByThread(false,
				$isAscending, $offset, $numNodes)
		];
	}

}

?>
