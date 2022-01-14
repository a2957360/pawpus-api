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
	$limit = YOU_LIKE_QUERY_LIMIT;

	$itemCategory=$_GET['itemCategory'];
	$itemId=$_GET['itemId'];

  	$searchSql .= isset($itemCategory)?" AND `itemTable`.`itemCategory`='$itemCategory'":"";
  	$searchSql .= isset($itemId)?" AND `itemTable`.`itemId`!='$itemId'":"";

	$list = array();
	$stmt = $pdo->prepare("SELECT `itemTable`.*,`categoryTable`.`categoryName`,
							min(`itemOptionTable`.`itemOptionPrice`) AS `price`,IFNULL(min(`saleOption`.`itemOptionSalePrice`),0) AS `salePrice` From `itemTable`
						   LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `itemTable`.`itemCategory`
						   LEFT JOIN `itemOptionTable`  ON `itemOptionTable`.`itemId` = `itemTable`.`itemId` AND `itemOptionTable`.`itemOptionState` = '0'
						   LEFT JOIN `itemOptionTable` `saleOption` ON `saleOption`.`itemId` = `itemTable`.`itemId` AND `saleOption`.`itemOptionState` = '0' AND `saleOption`.`itemOptionSalePrice` > 0
						   WHERE itemState = '0' ".$searchSql." GROUP BY `itemTable`.`itemId` limit $limit");
	$stmt->execute();
	if($stmt != null){
	while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
		$row["categoryName"] = json_decode($row["categoryName"], true); 
		$row["itemImage"] = json_decode($row["itemImage"], true);
		// $row["itemCategory"] = json_decode($row["itemCategory"], true);
		$row["itemSubCategory"] = json_decode($row["itemSubCategory"], true);
		$row["itemPetCategory"] = json_decode($row["itemPetCategory"], true);
        $row["itemPetSubCategory"] = json_decode($row["itemPetSubCategory"], true);
		// $row["itemOption"] = json_decode($row["itemOption"], true);
		// $row["isShowText"] = $row["itemState"] == 0 ?"上架":"下架";
		$row["price"] = round((float)$row["price"],2);
		$row["salePrice"] = round((float)$row["salePrice"],2);

	    $list[] = $row;
	}
	}else{
	  echo json_encode(["message"=>"database error"]);
	  exit();
	}

	echo json_encode(["message"=>"success","data"=>$list]);
	exit();
}

