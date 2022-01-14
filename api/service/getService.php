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
  $serviceState = SERVICE_STATE;
  $limit = QUERY_LIMIT;
  $offset=isset($data['offset'])?$data['offset']:0;

  $serviceCity="'".str_replace( ",","','", implode(',',$data['serviceCity']))."'";
  $categoryId=$data['categoryId'];
  $petStock=$data['petStock'];
  $userId=$data['userId'];
  $serviceSubCategory=$data['serviceSubCategory'];
  $serviceExtra=$data['serviceExtra'];
  $searchContent=$data['searchContent'];

  $startPrice=$data['startPrice'];
  $endPrice=$data['endPrice'];
  
  $startDate=$data['startDate'];
  $endDate=$data['endDate'];
  $serviceLanguage=$data['serviceLanguage'];

  $sort = $data['sort'];
  $orderBy = $data['orderBy'];
  if($orderBy == "regular"){
    $orderBy = "";
  }else{
    $orderBy=isset($orderBy) && isset($sort)?" ORDER BY `$orderBy` $sort":"";
  }


  //清除5分钟之前的数据
  $todaymin5 = date('Y-m-d H:i:s', strtotime("-10 minute"));
  $stmt = $pdo->prepare("DELETE FROM `serviceOrderTable` WHERE `creatTime` < '$todaymin5' AND `orderState` = '0'");
  $stmt->execute();

  $searchSql .= isset($data['serviceCity']) && $data['serviceCity'] != []?" AND `serviceTable`.`serviceCity` IN ($serviceCity)":"";
  $searchSql .= isset($categoryId)?" AND `serviceTable`.`serviceCategory`='$categoryId'":"";
  $searchSql .= isset($userId)?" AND `serviceTable`.`userId`='$userId'":"";
  $searchSql .= (isset($startPrice) && isset($endPrice))?" AND `serviceTable`.`servicePrice`>='$startPrice' AND `serviceTable`.`servicePrice`<='$endPrice'":"";
  $searchSql .= isset($serviceLanguage)?" AND `serviceTable`.`serviceLanguage` LIKE '%$serviceLanguage%'":"";
  $searchSql .= isset($searchContent)?" AND `serviceTable`.`serviceName` LIKE '%$searchContent%'":"";
  //时间 1 查询开始时间<=order开始时间 order结束时间>order开始时间 2 查询开始时间>order开始时间 并且<order结束时间
  $datesearch = (isset($startDate) && isset($endDate))?",sum(`serviceOrderTable`.`servicePetNumber`) AS `stock`":"";
  $dateSql .= (isset($startDate) && isset($endDate))?"LEFT JOIN `serviceOrderTable` ON `serviceOrderTable`.`serviceId` = `serviceTable`.`serviceId` AND ((`serviceOrderTable`.`orderStartDate`<='$startDate' AND `serviceOrderTable`.`orderEndDate`>'$startDate') OR (`serviceOrderTable`.`orderStartDate`>'$startDate' AND `serviceOrderTable`.`orderStartDate`<'$endDate'))":"";

  $list = array();
  $stmt = $pdo->prepare("SELECT `serviceTable`.*,`categoryTable`.`categoryName`,`userTable`.`userName`,`userTable`.`userImage`,
                          `userTable`.`serverPoints`,`userTable`.`serverLevel`,count(`sumOrder`.`serviceOrderId`) AS `serviceSaleNumber`".$datesearch." 
                          From `serviceTable` 
                          LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `serviceTable`.`serviceCategory`
                          LEFT JOIN `userTable` ON `userTable`.`userId` = `serviceTable`.`userId`
                          LEFT JOIN `serviceOrderTable` `sumOrder` ON `sumOrder`.`serviceId` = `serviceTable`.`serviceId`
                          ".$dateSql."
                          WHERE `serviceTable`.`serviceState` = '1' AND `serviceBlock` = '0' ".$searchSql." Group BY `serviceTable`.`serviceId`".$orderBy.
                          " limit $offset, $limit");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["categoryName"] = json_decode($row["categoryName"], true); 
      $row["serviceSubCategory"] = json_decode($row["serviceSubCategory"], true);
      $row["serviceExtra"] = json_decode($row["serviceExtra"], true);
      $row["serviceImage"] = json_decode($row["serviceImage"], true);
      $row["serviceStateDisplay"] = $serviceState[$row["serviceState"]];

      //筛选二级分类
      $tmpsub = array_keys($row["serviceSubCategory"]);
      $subresult = array_intersect($serviceSubCategory,$tmpsub);
      if(isset($serviceSubCategory) && $serviceSubCategory != [] && (count($subresult)==0 || $subresult != $serviceSubCategory)){
        continue;
      }
      //筛选额外服务
      $tmpextra = array_keys($row["serviceExtra"]);
      $extraresult = array_intersect($serviceExtra,$tmpextra);
      if(isset($serviceExtra) && $serviceExtra != [] && (count($extraresult)==0 || $extraresult !=$serviceExtra)){
        continue;
      }
      if(isset($row["stock"]) && $row["serviceStock"] <= ($row["stock"] + $petStock)){
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
