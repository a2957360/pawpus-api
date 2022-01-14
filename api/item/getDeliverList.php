<?php
include("../../include/sql.php");
include("../../include/conf/config.php");

http_response_code(200);
header('content-type:application/json;charset=utf8');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

$data = file_get_contents('php://input');
$data = json_decode($data,true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$deliverPostal=substr($data['deliverPostal'], 0 , 3);
	$subTotal=$data['subTotal'];

  	$searchSql .= isset($itemCategory)?" AND `itemTable`.`itemCategory`='$itemCategory'":"";

	$list = array();
	$stmt = $pdo->prepare("SELECT * From `deliverTable`
						   WHERE (`deliverPostal` LIKE '%$deliverPostal%' AND `deliverRequirePrice` <= $subTotal) OR `deliverType` = '1'");
	$stmt->execute();
	if($stmt != null){
	while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
	    $list[] = $row;
	}
	}else{
	  echo json_encode(["message"=>"database error"]);
	  exit();
	}

	echo json_encode(["message"=>"success","data"=>$list]);
	exit();
}

