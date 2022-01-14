<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $serviceOrderId=$_GET['serviceOrderId'];
  $userId=$_GET['userId'];
  $serverId=$_GET['serverId'];
  //0:待付款;1:待商家确认;2:等待寄送;3:寄养中;4:服务结束;5:已取消 
  $orderState=$_GET['orderState'];

  $serviceOrderState=SERVICE_ORDER_STATE; 

  if(!isset($userId) && !isset($serverId)){
    echo json_encode(["message"=>"no user"]);  
    exit();
  }

  $searchSql .= isset($serviceOrderId)?" AND `serviceOrderTable`.`serviceOrderId`='$serviceOrderId'":"";
  $searchSql .= isset($userId)?" AND `serviceOrderTable`.`userId`='$userId'":"";
  $searchSql .= isset($serverId)?" AND `serviceOrderTable`.`serverId`='$serverId'":"";
  $searchSql .= isset($orderState)?" AND `serviceOrderTable`.`orderState`='$orderState'":"";

  $list = array();
  $stmt = $pdo->prepare("SELECT `serviceOrderTable`.`createTime`,`serviceOrderTable`.`serviceOrderNo`,
                         `userTable`.`userName`,`serverTable`.`userName` AS `serverName`,`serviceTable`.`servicePhone`,
                         `categoryTable`.`categoryName`,`serviceOrderTable`.`servicePetNumber`,`serviceOrderTable`.`orderStartDate`,`serviceOrderTable`.`orderEndDate`,
                         `serviceOrderTable`.`serviceOrderDays`,`serviceOrderTable`.`serviceInfo`,`serviceOrderTable`.`serviceComment`,`serviceOrderTable`.`orderState`,
                         `serviceOrderTable`.`serviceOrderTotalPrice`,`serviceOrderTable`.`serviceOrderExtra`,`serviceOrderTable`.`orderEndDate`
                         From `serviceOrderTable` 
                         LEFT JOIN `serviceTable` ON `serviceTable`.`serviceId` = `serviceOrderTable`.`serviceId`
                         LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `serviceTable`.`serviceCategory`
                         LEFT JOIN `userTable` ON `userTable`.`userId` = `serviceOrderTable`.`userId`
                         LEFT JOIN `userTable` `serverTable` ON `serverTable`.`userId` = `serviceOrderTable`.`serverId`
                         WHERE 1 ".$searchSql);
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["categoryName"] = json_decode($row["categoryName"], true); 
      $row["serviceInfo"] = json_decode($row["serviceInfo"], true); 
      $row["serviceOrderDays"] = json_decode($row["serviceOrderDays"], true);
      $row["OrderDayNumber"] = count($row["serviceOrderDays"]);
      $row["serviceOrderExtra"] = json_decode($row["serviceOrderExtra"], true); 
      $row["orderState"] = $serviceOrderState[$row["orderState"]];
      $list[] = $row;
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }

  echo json_encode(["message"=>"success","data"=>$list]);
  exit();

}
