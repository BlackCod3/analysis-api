<?php
/**
 * Created by PhpStorm.
 * User: blackcod3
 * Date: 09/02/17
 * Time: 21:18
 */


print_r(array('key' => 'wbid'));

$db = getMongoDB();



$bulk = new MongoDB\Driver\BulkWrite;
$bulk->insert(array('name'=> 'KSA', 'job' => 'b.e-wizB'));
$db->executeBulkWrite('db.testCol', $bulk);


$filter = array('job' => array('$ne' => null));
$query = new MongoDB\Driver\Query($filter);
$cursor = $db->executeQuery('db.testCol', $query);

foreach ($cursor as $document) {
    var_dump($document);
}


//$col = $db->testCol; $col->insert(array('name'=> 'KSA', 'job' => 'b.e-wizB'));


function getMongoDB() {

    $mongo = new \MongoDB\Driver\Manager("mongodb://localhost:27017", array("connect" => true));
    return $mongo;
}