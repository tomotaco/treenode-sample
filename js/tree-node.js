var urlApi = "/~tomotaco/tree-node/ajax/tree-node-request-handler.php";

var indexPageCurrent = 0;
var typeSortCurrent = "thread";

$(function() {
	function addNodeTopLevel()
	{
		$.ajax({
			type: "POST",
			url: urlApi,
			dataType: "json",
			cache: false,
			data: {
				command: "addNodeTopLevel",
			}
		}).done(function(result) {
			retrieveNumNodes();
			retrieveTreeNode();
		});
	}

	function addNodeChild(idNode)
	{
		$.ajax({
			type: "POST",
			url: urlApi,
			dataType: "json",
			cache: false,
			data: {
				command: "addNodeChild",
				idNode: idNode,
			}
		}).done(function(result) {
			retrieveNumNodes();
			retrieveTreeNode();
		});
	}

	function deleteNode(idNode)
	{
		$.ajax({
			type: "POST",
			url: urlApi,
			dataType: "json",
			cache: false,
			data: {
				command: "deleteNode",
				idNode: idNode,
			}
		}).done(function(result) {
			retrieveNumNodes();
			retrieveTreeNode();
		}).fail(function(result) {
			alert("error");
		});
	}


	function createDivNode(node, doesIndent)
	{
		var $elemDiv = $('<div class="node" />');
		$elemDiv.attr("id", "node_" + node["node_id"]);
		if (doesIndent) {
			var depth = node["node_depth"];
			for (var indexIndent = 0; indexIndent < depth; indexIndent ++) {
			var $elemIndent = $('<div class="indent" />');
				$elemDiv.append($elemIndent);
			}
		}
		var text = "index=" + node["node_index"] + ", " +
			"depth=" + node["node_depth"] + ", " +
			"pathtree=" + node["node_path_tree"] + ", " +
			"posLeft=" + node["position_left"] + ", " +
			"posRight=" + node["position_right"];
		var $elemText= $('<div class="text">' + text + '</div>');
		$elemDiv.append($elemText);
		var $elemButtonReply = $('<button class="addchild">Add child node</button>');
		$elemText.append($elemButtonReply);
		var $elemButtonDelete = $('<button class="delete">Delete</button>');
		$elemText.append($elemButtonDelete);

		return $elemDiv;
	}

	function setupAddNode()
	{
		$("button#addtoplevel").click(function(event) {
			addNodeTopLevel();
		});

		$("div#treenode").on("click", "button.addchild", function(event) {
			var node = $(this).parents("div.node");
			var idNode = node.attr("id").split("_")[1];
			addNodeChild(idNode);
		});

		$("div#treenode").on("click", "button.delete", function(event) {
			var node = $(this).parents("div.node");
			var idNode = node.attr("id").split("_")[1];
			deleteNode(idNode);
		});

	}

	function setupNavPages()
	{
		$("#navPages").on("click", "a", function(event) {
			event.preventDefault();
			var indexPage = parseInt($(this).text());
			indexPageCurrent = indexPage - 1;
			var numNodes = parseInt($("#num").text());
			updateNavPages(numNodes, indexPageCurrent);
			retrieveTreeNode();
		});
		
		$("#update").click(function(event) {
			var numNodes = parseInt($("#num").text());
			updateNavPages(numNodes, indexPageCurrent);
			retrieveTreeNode();			
		});
	}

	function setupSort()
	{
		$("#sort").on("click", "a", function(event) {
			event.preventDefault();
			var typeSort = $(this).text();
			typeSortCurrent = typeSort;
			updateSort();
			retrieveTreeNode();
		});
	}

	function updateNavPages(numNodes, indexPageCurrent)
	{
		var $elemNavPages = $("#navPages");
		$elemNavPages.children().remove();
		var numNodesPerPage = parseInt($("#numPerPage").val());
		var numPages = Math.floor((numNodes + numNodesPerPage - 1) / numNodesPerPage);

		for (var indexPage = 0; indexPage < numPages; indexPage ++) {
			var $elem = null;
			if (indexPage == indexPageCurrent) {
				$elem = $('<span /> ');
			} else {
				$elem = $('<a href=#" />');
			}
			$elem.text(indexPage + 1);
			$elemNavPages.append($elem);
		}
	}

	function retrieveNumNodes()
	{
		$.ajax({
			type: "GET",
			url: urlApi,
			dataType: "json",
			cache: false,
			data: {
				command: "getNumNodes"
			}
		}).done(function(result) {
			var numNodes = parseInt(result["numNodes"]);
			$("#num").text(numNodes);
			updateNavPages(numNodes, indexPageCurrent);
		}).fail(function(result) {
			alert("error");
		});
	}

	function retrieveTreeNode()
	{
		var typeSort = typeSortCurrent;
		var numNodesPerPage = parseInt($("#numPerPage").val());
		var indexPage = indexPageCurrent;

		var offset = indexPage * numNodesPerPage;

		var command = ((typeSort == "thread") ? "getNodesAllByThread" : "getNodesAllByDate");
		var isAscending = (typeSort == "date(desc)" ? false : true);
		$.ajax({
			type: "GET",
			url: urlApi,
			dataType: "json",
			cache: false,
			data: {
				command: command,
				isAscending: isAscending,
				numNodes: numNodesPerPage,
				offset: offset
			}
		}).done(function(result) {
			var $elemDivNodes = $('<div class="nodes">');
			var nodes = result["nodes"];
			$('#treenode').children().remove();
			var doesIndent = (typeSortCurrent == "thread" ? true : false);
			for (var index = 0; index < nodes.length; index ++) {
				var $elemNode = createDivNode(nodes[index], doesIndent);
				$('#treenode').append($elemNode);
			}
			$("button").button();
		});
	}

	function updateSort()
	{
		var $elemSort = $("#sort");
		$elemSort.children().remove();
		var tableTypeSort = [ "thread", "date(asc)", "date(desc)" ];
		for (var index = 0; index < tableTypeSort.length; index ++) {
			var typeSort = tableTypeSort[index];
			var $elem;
			if (typeSort == typeSortCurrent) {
				$elem = $('<span />');
			} else {
				$elem = $('<a href="#" />');
			}
			$elem.text(typeSort);
			$elemSort.append($elem);
		}
	}

	updateSort();
	setupAddNode();
	setupNavPages();
	setupSort();
	retrieveNumNodes();
	retrieveTreeNode();
});
