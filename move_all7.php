<?php

	$src_course = $argv[1];
	$dst_course = $argv[2];

	if ($src_course == "" || $dst_course == "") {
		echo "\n";
		echo "ERROR: Incorrect syntax";
		echo "\n";
		echo "\n";
		echo "USAGE: php move.php <source_course_id> <destination_course_id>";
		echo "\n";
		echo "\n";
		exit(1);
	}

	echo "Moving modules in $src_course to $dst_course";
	echo "\n\n";

	$state = updateContentObject($src_course,$dst_course);
	if (!$state) {
		echo "Stage 1 ERROR: Failed to update content object\n\n";
		exit(0);
	}

	$state = updateArticles($src_course,$dst_course);
	if (!$state) {
		echo "Stage 2 ERROR: Failed to update artciles\n\n";
		exit(0);
	}

	$state = updateBlocks($src_course,$dst_course);
	if (!$state) {
		echo "Stage 3 ERROR: Failed to update blocks\n\n";
		exit(0);
	}

	$state = updateComponents($src_course,$dst_course);
	if (!$state) {
		echo "Stage 4 ERROR: Failed to update components\n\n";
		exit(0);
	}

	$state = updateAssets($src_course,$dst_course);
	if (!$state) {
		echo "Stage 5 ERROR: Failed to update assets\n\n";
		exit(0);
	}

	echo "SUCCESS!";
	echo "\n\n";
	

function executeUpdate($collection,$search,$set,$multiple) {
	require 'vendor/autoload.php';
	require('config.inc.php');
   	try {
		$client = new MongoDB\Client("mongodb://localhost:27017");
		$col = $client->$db_name->$collection;
		$res = $col->updateMany($search,$set);
		return $res;
	} catch ( Exception $e ) {
		echo "\n";
		echo('Error: ' . $e->getMessage());
		echo "\n";
		echo "\n";
		exit(1);
   	}
}
function updateContentObject($src_course,$dst_course) {
	$search = array('_courseId' => new MongoDB\BSON\ObjectId($src_course));
	$set = array('$set' => array("_courseId" => new MongoDB\BSON\ObjectId($dst_course), "_parentId" => new MongoDB\BSON\ObjectId($dst_course)));
	$multiple = array("multiple" => true);
	$res = executeUpdate("contentobjects",$search,$set,$multiple);
	return $res;
}
function updateArticles($src_course,$dst_course) {
	$search = array('_courseId' => new MongoDB\BSON\ObjectId($src_course));
	$set = array('$set' => array("_courseId" => new MongoDB\BSON\ObjectId($dst_course)));
	$multiple = array("multiple" => true);
	$res = executeUpdate("articles",$search,$set,$multiple);
	return $res;
}
function updateBlocks($src_course,$dst_course) {
	$search = array('_courseId' => new MongoDB\BSON\ObjectId($src_course));
	$set = array('$set' => array("_courseId" => new MongoDB\BSON\ObjectId($dst_course)));
	$multiple = array("multiple" => true);
	$res = executeUpdate("blocks",$search,$set,$multiple);
	return $res;
}
function updateComponents($src_course,$dst_course) {
	$search = array('_courseId' => new MongoDB\BSON\ObjectId($src_course));
	$set = array('$set' => array("_courseId" => new MongoDB\BSON\ObjectId($dst_course)));
	$multiple = array("multiple" => true);
	$res = executeUpdate("components",$search,$set,$multiple);
	return $res;
}
function updateAssets($src_course,$dst_course) {
	$search = array('_courseId' => $src_course);
	$set = array('$set' => array("_courseId" => $dst_course));
	$multiple = array("multiple" => true);
	$res = executeUpdate("courseassets",$search,$set,$multiple);
	return $res;
}

?>
