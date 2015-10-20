<?php

require_once("Database.php");

class TreeNode
{
	var $db = false;	

	public function __construct($dsn)
	{
		$this->db = new Database($dsn);
	}

	public function addNodeTopLevel()
	{
		$positionLeftBound = "a";
		$positionRightBound = "b";
		$numNodes = $this->getNumNodes(false);
		if (0 < $numNodes) {
			$positionLeftBound = $this->getPositionLeftBoundToAdd();
		}
		
		$positionLeftToAdd = $positionLeftBound . "a";
		$positionRightToAdd = $positionLeftBound . "b";
		
		$index = $this->getIndexLatestNode();
		$indexPathTree = $this->getIndexThread(false, $positionLeftToAdd, $positionRightToAdd);

		$q = "INSERT INTO tree_node_tbl (node_index, node_depth, node_path_tree, " .
			"active, " .
			"position_left, position_right, date_modified) VALUES (?, ?, ?, ?, ?, ?, NOW())";

		$a = [$index, 0, $indexPathTree,
				1, $positionLeftToAdd, $positionRightToAdd];

		$result = $this->db->executeSql($q, $a);
		if (!$result) return false;
		$indexInsertLast = $this->db->getIndexInsertedLast();
		return $indexInsertLast;
	}

	public function addNodeChild($idNodeTarget)
	{
		$nodeTarget = $this->getNode($idNodeTarget);

		$positionLeftTarget = $nodeTarget["position_left"];
		$positionRightTarget = $nodeTarget["position_right"];
		$positionLeftBound = $this->getPositionLeftBoundToReply($positionLeftTarget, $positionRightTarget);
		$positionRightBound = $positionRightTarget;

		$positionLeftToAdd = $positionLeftBound . "a";
		$positionRightToAdd = $positionLeftBound . "b";

		$index = $this->getIndexLatestNode();
		$depth = $nodeTarget["node_depth"] + 1;
		$indexPathTree = $this->getIndexThread($nodeTarget, $positionLeftToAdd, $positionRightToAdd);
		$pathTree = $nodeTarget["node_path_tree"] . "-" . $indexPathTree;

		$q = "INSERT INTO tree_node_tbl (node_index, node_depth, node_path_tree, " .
			"active, " .
			"position_left, position_right, date_modified) VALUES (?, ?, ?, ?, ?, ?, NOW())";

		$a = [$index, $depth, $pathTree,
				1, $positionLeftToAdd, $positionRightToAdd];

		$result = $this->db->executeSql($q, $a);
		if (!$result) return false;
		$indexInsertLast = $this->db->getIndexInsertedLast();
		return $indexInsertLast;
	}

	public function deleteNode($idNode)
	{
		$q = "DELETE FROM tree_node_tbl WHERE node_id = ?";
		$a = [$idNode];
		
		$result = $this->db->executeSql($q, $a);
		if (!$result) return false;
		return true;
	}

	public function markNodeAsDeleted($idNode)
	{
		$q = "UPDATE tree_node_tbl SET active = ? WHERE node_id = ?";
		$a = [0, $idNode];
		
		$result = $this->db->executeSql($q, $a);
		if (!$result) return false;
		return true;
	}

	public function getNode($idNode)
	{
		$q = "SELECT " .
			"node_id, node_index, node_depth, node_path_tree, " .
			"active, " .
			"position_left, position_right, date_created, date_modified " .
			"FROM tree_node_tbl WHERE node_id = ?";
	  	$a = [$idNode];
		
		$result = $this->db->getRow($q, $a);
		return $result;
	}

	public function getNodeByIndex($index)
	{
		$q = "SELECT * FROM tree_node_tbl WHERE node_index = ? ";
		$a = [$index];
		
		$result = $this->db->getRow($q, $a);
		return $result;
	}
	public function getNumNodes($isActive = false)
	{
		$q = "select count(*) from tree_node_tbl WHERE 1 ";
		$a = [];
		if ($isActive != false) {
			$q .= "AND active = ? ";
			$a[] = $isActive;
		}
		$result = $this->db->getOne($q, $a);
		
		return $result;
	}

	public function getNodes($isActive,
										   $nameColumnOrder, $isAscending,
										   $indexOffset, $numNodes)
	{
		$q = "select " .
			"node_id, node_index, node_depth, node_path_tree, " .
			"active, " .
			"position_left, position_right, date_created, date_modified " .
			"from tree_node_tbl WHERE 1 ";
		$a = [];
		if ($isActive != false) {
			$q .= "AND active = ? ";
			$a[] = $isActive;
		}
		$q .= "ORDER BY " . $nameColumnOrder . " " . ($isAscending ? "ASC" : "DESC") . " ";
		if ($numNodes > 0) {
			$q .= "LIMIT ? OFFSET ?";
			$a = array_merge($a, [$numNodes, $indexOffset]);
		}
		
		$result = $this->db->getRows($q, $a);
		return $result;
	}

	public function getChildNodesWithDepthByThread($idNodeRoot,
													   $isAscending,
													   $indexOffset, $numNodes)
	{
		$positionLeftRoot = "0";
		$positionRightRoot = "1";
		if ($idNodeRoot != false) {
			$nodeRoot = $this->getNode($idNodeRoot);
			if ($nodeRoot != false) {
				$positionLeftRoot = $nodeRoot["position_left"];
				$positionRightRoot = $nodeRoot["position_right"];
			}
		}

		$q = "SELECT " .
			"parents.node_id, parents.node_index, parents.node_depth, parents.node_path_tree, " .
			"parents.active, " .
			"parents.position_left, parents.position_right, parents.date_created, parents.date_modified, " .
			"COUNT(parents.node_id) " .
			"FROM tree_node_tbl parents, tree_node_tbl children " .
			"WHERE parents.active = ? AND children.active = ? ";
		$a = [1, 1];
		if ($positionLeftRoot != false && $positionRightRoot != false) {
			$q .= "AND STRCMP(?, parents.position_left) <= 0 " .
				"AND STRCMP(parents.position_right, ?) <= 0 ";
			$a = array_merge($a, [$positionLeftRoot, $positionRightRoot]);
		}
		$q .= "AND parents.position_left BETWEEN children.position_left AND children.position_right " .
			"GROUP BY parents.node_id ";
		$q .= "ORDER BY parents.position_left " . ($isAscending ? "ASC" : "DESC") . " ";
		if ($numNodes > 0) {
			$q .= "LIMIT ? OFFSET ?";
			$a = array_merge($a, [$numNodes, $indexOffset]);
		}

		$result = $this->db->getRows($q, $a);
		return $result;	
	}

	public function getNodeParent($nodeCurrent)
	{
		$positionLeftCurrent = $nodeCurrent["position_left"];
		$positionRightCurrent = $nodeCurrent["position_right"];

		$q = "SELECT * FROM tree_node_tbl " +
			"WHERE STRCMP(position_left, ?) < 0 AND STRCMP(?, position_right) < 0 " +
			"ORDER BY position_left DESC LIMIT 1 OFFSET 0";
		$a = [$positionLeftCurrent, $positionRightCurrent];
		
		$result = $this->db->getRow($q, $a);
		return $result;	
	}

	public function getNodeParentRoot($nodeCurrent)
	{
		$positionLeftCurrent = $nodeCurrent["position_left"];
		$positionRightCurrent = $nodeCurrent["position_right"];

		$q = "SELECT * FROM tree_node_tbl " +
			"WHERE STRCMP(position_left, ?) < 0 AND STRCMP(?, position_right) < 0 " +
			"ORDER BY position_left ASC " .
		  	"LIMIT 1 OFFSET 0";

		$a = [$positionLeftCurrent, $positionRightCurrent];
		
		$result = $this->db->getRow($q, $a);
		return $result;	
	}

	public function getFlagsTreeParent($node)
	{
		$listFlags = [];
		while ($node != false) {
			$nodeParent = $this->getNodeParent($node);
			if ($node["active"]) {
				$doesExist = $this->doesExistNextNode(idSite, node, nodeParent);
				array_unshift($listFlags, $doesExist);
			}
			$node = $nodeParent;
		}
		return $listFlags;

	}

	private function getIndexThread($nodeParent, $positionLeftCurrent, $positionRightCurrent)
	{
		$positionLeftParent = $nodeParent["position_left"];
		$positionRightParent = $nodeParent["position_right"];

		$q = "SELECT COUNT(brother.node_id) FROM tree_node_tbl AS brother " .
			"WHERE STRCMP(brother.position_left, ?) < 0 ";
		$a = [$positionLeftCurrent];
		if ($nodeParent != false) {
			$q .= "AND STRCMP(?, brother.position_left) < 0 " .
				"AND STRCMP(brother.position_right, ?) < 0 ";
			$a = array_merge($a, [$positionLeftParent, $positionRightParent]);
		}
		$q .= "AND NOT EXISTS (" .
			"SELECT * FROM tree_node_tbl AS nephew " .
			"WHERE STRCMP(nephew.position_left, brother.position_left) < 0 " .
			"AND STRCMP(brother.position_right, nephew.position_right) < 0 ";
		if ($nodeParent != false) {
			$q .= "AND STRCMP(?, nephew.position_left) < 0 " .
				"AND STRCMP(nephew.position_right, ?) < 0 ";
			$a = array_merge($a, [$positionLeftParent, $positionRightParent]);
		}
		$q .= ")";

		$result = $this->db->getOne($q, $a);
		return $result + 1;
	}
	private function getIndexLatestNode()
	{
		$index =  $this->getNumNodes(false, false) + 1;
		return $index;
	}
	
	public function getPositionLeftBoundToAdd()
	{
		$q = "SELECT position_right FROM tree_node_tbl " .
				"ORDER BY position_right DESC LIMIT 1 OFFSET 0";
		$result = $this->db->getOne($q, []);

		return $result;
	}
	public function getPositionLeftBoundToReply($positionLeftTarget, $positionRightTarget)
	{
		$q = "SELECT position_right FROM tree_node_tbl " .
			"WHERE STRCMP(?, position_right) < 0 AND STRCMP(position_right, ?) < 0 " .
			"ORDER BY position_right DESC " .
		  	"LIMIT 1 OFFSET 0";
		$a = [$positionLeftTarget, $positionRightTarget];

		$result = $this->db->getOne($q, $a);
		if (!$result) return $positionLeftTarget;

		return $result;
	}
	public function doesExistNextNode($nodeCurrent, $nodeParent)
	{
		$positionRightCurrent = $nodeCurrent["position_right"];
		$q = "SELECT node_id FROM tree_node_tbl " .
			"WHERE active = ? " .
			"AND STRCMP(?, position_left) < 0 ";
		$a = [1, $positionRightCurrent];

		if ($nodeParent != false) {
			$positionRightParent = $nodeParent["position_right"];

			$q .= "AND STRCMP(position_right, ?) < 0 ";
			$a[] = $positionRightParent;
		}
		$q .= "ORDER BY position_right ASC LIMIT 1 OFFSET 0";
	
		$result = $this->db->getOne($q, $a);
	
		return $result;
	}
	

}

?>
