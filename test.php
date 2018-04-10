<?php
	require 'vendor/autoload.php';
	require('config.inc.php');
	$src_course = "579b77341e2f01b51c77a17c";
	$client = new MongoDB\Client("mongodb://localhost:27017");
	$collection = "contentobjects";
	$col = $client->$db_name->$collection;
	$search = array('_courseId' => new MongoDB\BSON\ObjectId($src_course));
	$res = $col->find($search);
	foreach ($res as $doc) {
		print_r($doc);
	}

?>
