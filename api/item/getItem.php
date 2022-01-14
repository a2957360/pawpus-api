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
	$limit = QUERY_LIMIT;
  	$offset=isset($data['offset'])?$data['offset']:0;

	$itemCategory=$data['itemCategory'];
	$itemSubCategory=$data['itemSubCategory'];
	$itemPetCategory=$data['itemPetCategory'];
	$itemPetSubCategory=$data['itemPetSubCategory'];
	$searchContent=$data['searchContent'];

	$startPrice=$data['startPrice'];
  	$endPrice=$data['endPrice'];

	$sort = $data['sort'];
	$orderBy = $data['orderBy'];
	if($orderBy == "regular"){
		$orderBy = "";
	}else{
		$orderBy=isset($orderBy) && isset($sort)?" ORDER BY `$orderBy` $sort":"";
	}


  	$searchSql .= isset($itemCategory)?" AND `itemTable`.`itemCategory`='$itemCategory'":"";
  	$haveSql .= (isset($startPrice) && isset($endPrice))?" HAVING IFNULL(min(`saleOption`.`itemOptionSalePrice`),min(`itemOptionTable`.`itemOptionPrice`))>='$startPrice' AND IFNULL(min(`saleOption`.`itemOptionSalePrice`),min(`itemOptionTable`.`itemOptionPrice`))<='$endPrice'":"";
  	$searchSql .= isset($searchContent)?" AND `itemTable`.`itemTitle` LIKE '%$searchContent%'":"";

	$list = array();
	$stmt = $pdo->prepare("SELECT `itemTable`.*,`categoryTable`.`categoryName`,
							min(`itemOptionTable`.`itemOptionPrice`) AS `price`,IFNULL(min(`saleOption`.`itemOptionSalePrice`),0) AS `salePrice`,IFNULL(min(`saleOption`.`itemOptionSalePrice`),min(`itemOptionTable`.`itemOptionPrice`)) AS `itemPrice` From `itemTable`
						   LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `itemTable`.`itemCategory`
						   LEFT JOIN `itemOptionTable`  ON `itemOptionTable`.`itemId` = `itemTable`.`itemId` AND `itemOptionTable`.`itemOptionState` = '0'
						   LEFT JOIN `itemOptionTable` `saleOption` ON `saleOption`.`itemId` = `itemTable`.`itemId` AND `saleOption`.`itemOptionState` = '0' AND `saleOption`.`itemOptionSalePrice` > 0
						   WHERE itemState = '0' ".$searchSql." GROUP BY `itemTable`.`itemId`".$haveSql.$orderBy." limit $offset, $limit");
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


		//筛选二级分类
		$tmpsub = $row["itemSubCategory"];
		$subresult = array_intersect($itemSubCategory,$tmpsub);
		if(isset($itemSubCategory) && (count($subresult) == 0 || $subresult != $itemSubCategory)){
			continue;
		}

		//筛选宠物分类
		$tmpsub = $row["itemPetCategory"];
		$subresult = array_intersect($itemPetCategory,$tmpsub);
		if(isset($itemPetCategory) && (count($subresult) == 0 || $subresult != $itemPetCategory)){
			continue;
		}

		//筛选宠物sub分类
		$tmpsub = $row["itemPetSubCategory"];
		$subresult = array_intersect($itemPetSubCategory,$tmpsub);
		if(isset($itemPetSubCategory) && (count($subresult) == 0 || $subresult != $itemPetSubCategory)){
			continue;
		}

	    $list[] = $row;
	}
	}else{
	  echo json_encode(["message"=>"database error"]);
	  exit();
	}

	echo json_encode(["message"=>"success","data"=>$list,"offset"=>$offset + count($list)]);
	exit();
}

