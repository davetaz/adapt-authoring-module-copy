<?php

	$src_module = $argv[1];
	$dst_course = $argv[2];

	if ($src_module == "" || $dst_course == "") {
		echo "\n";
		echo "ERROR: Incorrect syntax";
		echo "\n";
		echo "\n";
		echo "USAGE: php move.php <source_module_id> <destination_course_id>";
		echo "\n";
		echo "\n";
		exit(1);
	}

	echo "Moving $src_module to $dst_course";
	echo "\n\n";

	echo "Currently $src_module is in course: ";
	$course = getExistingCourse($src_module);
	echo "\n\n";
	
	echo "Articles: ";
	$articles = getArticles($src_module);
	print_r($articles);
	echo "\n\n";
	
	echo "Blocks: ";
	$blocks = getBlocks($articles);
	print_r($blocks);
	echo "\n\n";

	echo "Components: ";
	$components = getComponents($blocks);
	print_r($components);
	echo "\n\n";

	echo "Assets: ";
	$assets = getAssets($blocks);	
	print_r($assets);
	echo "\n\n";

	$state = updateContentObject($src_module,$dst_course);
	if (!$state) {
		echo "Stage 1 ERROR: Failed to update content object\n\n";
		exit(0);
	}

	$state = updateArticles($articles,$dst_course);
	if (!$state) {
		echo "Stage 2 ERROR: Failed to update artciles\n\n";
		exit(0);
	}

	$state = updateBlocks($blocks,$dst_course);
	if (!$state) {
		echo "Stage 3 ERROR: Failed to update blocks\n\n";
		exit(0);
	}

	$state = updateComponents($components,$dst_course);
	if (!$state) {
		echo "Stage 4 ERROR: Failed to update components\n\n";
		exit(0);
	}

	$state = updateAssets($assets,$dst_course);
	if (!$state) {
		echo "Stage 5 ERROR: Failed to update assets\n\n";
		exit(0);
	}


	$state = updateExtraAssets($src_module,$dst_course);
	if (!$state) {
		echo "Stage 6 ERROR: Failed to update extra assets\n\n";
		exit(0);
	}

	echo "SUCCESS!";
	echo "\n\n";
	

function executeUpdate($collection,$search,$set,$multiple) {
	require('config.inc.php');
   	try {
		$m = new MongoClient();
		$db = $m->selectDB($db_name);
		$col = new MongoCollection($db,$collection);
		$res = $col->update($search,$set,$multiple);
		$m->close();
		return $res;
	} catch ( Exception $e ) {
		echo "\n";
		echo('Error: ' . $e->getMessage());
		echo "\n";
		echo "\n";
		exit(1);
   	}
}

function executeSearch($query,$collection) {
	require('config.inc.php');
   	try {
		$m = new MongoClient();
		$db = $m->selectDB($db_name);
		$col = new MongoCollection($db,$collection);
		$res = $col->find($query);
		$m->close();
		return $res;
	} catch ( Exception $e ) {
		echo "\n";
		echo('Error: ' . $e->getMessage());
		echo "\n";
		echo "\n";
		exit(1);
   	}
}

function getExistingCourse($src_module) {
	$query = array('_id' => new MongoId("$src_module"));
	$res = executeSearch($query,"contentobjects");
	$array = iterator_to_array($res);
	return $array[$src_module]["_courseId"];
}

function updateContentObject($src_module,$dst_course) {
	$search = array('_id' => new MongoId($src_module));
	$set = array('$set' => array("_courseId" => new MongoId($dst_course), "_parentId" => new MongoId($dst_course)));
	$multiple = array("multiple" => true);
	$res = executeUpdate("contentobjects",$search,$set,$multiple);
	return $res;
}

function getArticles($src_module) {
	$query = array('_parentId' => new MongoId("$src_module"));
	$res = executeSearch($query,"articles");
	$articles = [];
	foreach ($res as $doc) {
		$articles[] = $doc["_id"]->{'$id'};
	}
	return $articles;
}

function updateArticles($articles,$dst_course) {
	for ($i=0;$i<count($articles);$i++) {
		echo "Updating article " . $articles[$i] . "\n\n"; 
		$search = array('_id' => new MongoId($articles[$i]));
		$set = array('$set' => array("_courseId" => new MongoId($dst_course)));
		$multiple = array("multiple" => true);
		$res = executeUpdate("articles",$search,$set,$multiple);
		if (!$res) {
			return $res;
		}
	}
	return true;
}

function getBlocks($articles) {
	$blocks = [];
	for ($i=0;$i<count($articles);$i++) {
		$query = array('_parentId' => new MongoId($articles[$i]));
		$res = executeSearch($query,"blocks");
		foreach ($res as $doc) {
			$blocks[] = $doc["_id"]->{'$id'};
		}
	}
	return $blocks;
}

function updateBlocks($blocks,$dst_course) {
	for ($i=0;$i<count($blocks);$i++) {
		$search = array('_id' => new MongoId($blocks[$i]));
		$set = array('$set' => array("_courseId" => new MongoId($dst_course)));
		$multiple = array("multiple" => true);
		$res = executeUpdate("blocks",$search,$set,$multiple);
		if (!$res) {
			return $res;
		}
	}
	return true;
}

function getComponents($blocks) {
	$components = [];
	for ($i=0;$i<count($blocks);$i++) {
		$query = array('_parentId' => new MongoId($blocks[$i]));
		$res = executeSearch($query,"components");
		foreach ($res as $doc) {
			$components[] = $doc["_id"]->{'$id'};
		}
	}
	return $components;
}

function updateComponents($components,$dst_course) {
	for ($i=0;$i<count($components);$i++) {
		$search = array('_id' => new MongoId($components[$i]));
		$set = array('$set' => array("_courseId" => new MongoId($dst_course)));
		$multiple = array("multiple" => true);
		$res = executeUpdate("components",$search,$set,$multiple);
		if (!$res) {
			return $res;
		}
	}
	return true;
}

function getAssets($blocks) {
	$assets = [];
	for ($i=0;$i<count($blocks);$i++) {
		$query = array('_contentTypeParentId' => $blocks[$i]);
		$res = executeSearch($query,"courseassets");
		foreach ($res as $doc) {
			$assets[] = $doc["_id"]->{'$id'};
		}
	}
	return $assets;
}

function updateAssets($assets,$dst_course) {
	for ($i=0;$i<count($assets);$i++) {
		$search = array('_id' => new MongoId($assets[$i]));
		$set = array('$set' => array("_courseId" => $dst_course));
		$multiple = array("multiple" => true);
		$res = executeUpdate("courseassets",$search,$set,$multiple);
		if (!$res) {
			return $res;
		}
	}
	return true;
}

function updateExtraAssets($src_module,$dst_course) {
	$search = array('_contentTypeId' => $src_module);
	$set = array('$set' => array("_courseId" => $dst_course));
	$multiple = array("multiple" => true);
	$res = executeUpdate("courseassets",$search,$set,$multiple);
	if (!$res) {
		return $res;
	}
	return true;
}


?>