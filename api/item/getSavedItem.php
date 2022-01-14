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

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$limit = QUERY_LIMIT;
	$userId=$_GET['userId'];

  	$searchSql .= isset($userId)?" AND `savedTable`.`userId`='$userId'":"";

	$list = array();
	$stmt = $pdo->prepare("SELECT `itemTable`.*,`categoryTable`.`categoryName` From `savedTable`
						   LEFT JOIN `itemTable` ON `itemTable`.`itemId` = `savedTable`.`targetId`
						   LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `itemTable`.`itemCategory`
						   WHERE `itemState` = '0' AND `targetType`='1' ".$searchSql);
	$stmt->execute();
	if($stmt != null){
	while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
		$row["categoryName"] = json_decode($row["categoryName"], true); 
		$row["itemImage"] = json_decode($row["itemImage"], true);
		// $row["itemCategory"] = json_decode($row["itemCategory"], true);
		$row["itemSubCategory"] = json_decode($row["itemSubCategory"], true);
		$row["itemPetCategory"] = json_decode($row["itemPetCategory"], true);
        $row["itemPetSubCategory"] = json_decode($row["itemPetSubCategory"], true);
		// $row["isShowText"] = $row["itemState"] == 0 ?"上架":"下架";
	    $list[] = $row;
	}
	}else{
	  echo json_encode(["message"=>"database error"]);
	  exit();
	}

	echo json_encode(["message"=>"success","data"=>$list]);
	exit();
}

