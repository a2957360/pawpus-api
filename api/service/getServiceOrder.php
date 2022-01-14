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
  $serviceId=$_GET['serviceId'];
  //0:待付款;1:待商家确认;2:等待寄送;3:寄养中;4:服务结束;5:已取消 
  $orderState=$_GET['orderState'];

  $serviceOrderState=SERVICE_ORDER_STATE; 

  if(!isset($userId) && !isset($serverId)){
    echo json_encode(["message"=>"no user"]);  
    exit();
  }

  $searchSql .= isset($serviceOrderId)?" AND `serviceOrderTable`.`serviceOrderId`='$serviceOrderId'":"";
  $searchSql .= isset($userId)?" AND `serviceOrderTable`.`userId`='$userId'":"";
  $searchSql .= isset($serviceId)?" AND `serviceOrderTable`.`serviceId`='$serviceId'":"";
  $searchSql .= isset($serverId)?" AND `serviceOrderTable`.`serverId`='$serverId'":"";
  $searchSql .= isset($orderState)?" AND `serviceOrderTable`.`orderState`='$orderState'":"";

  $list = array();
  // $stmt = $pdo->prepare("SELECT `serviceOrderTable`.`createTime`,`serviceOrderTable`.`serviceOrderId`,`serviceOrderTable`.`serviceOrderNo`,
  //                        `userTable`.`userName`,`userTable`.`userImage`,`serverTable`.`userName` AS `serverName`,
  //                        `serviceTable`.`servicePhone`,`serviceTable`.`serviceImage`,`serviceTable`.`serviceName`,
  //                        `serviceTable`.`serviceAddress`,`serviceTable`.`serviceCity`,`serviceTable`.`serviceProvince`,`serviceTable`.`servicePostal`,
  //                        `categoryTable`.`categoryName`,`serviceOrderTable`.`servicePetNumber`,`serviceOrderTable`.`serviceOrderPetCard`,`serviceOrderTable`.`serviceOrderPetInfo`,
  //                        `serviceOrderTable`.`orderStartDate`,`serviceOrderTable`.`orderEndDate`,
  //                        `serviceOrderTable`.`serviceOrderDays`,`serviceOrderTable`.`serviceInfo`,`serviceOrderTable`.`serviceComment`,`serviceOrderTable`.`orderState`,
  //                        `serviceOrderTable`.`serviceOrderTotalPrice`,`serviceOrderTable`.`serviceOrderTotalPrice`,
  //                        `serviceOrderTable`.`serviceOrderExtra`,`serviceOrderTable`.`orderEndDate`
  //                        From `serviceOrderTable` 
  //                        LEFT JOIN `serviceTable` ON `serviceTable`.`serviceId` = `serviceOrderTable`.`serviceId`
  //                        LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `serviceTable`.`serviceCategory`
  //                        LEFT JOIN `userTable` ON `userTable`.`userId` = `serviceOrderTable`.`userId`
  //                        LEFT JOIN `userTable` `serverTable` ON `serverTable`.`userId` = `serviceOrderTable`.`serverId`
  //                        WHERE 1 ".$searchSql);
    $stmt = $pdo->prepare("SELECT `serviceOrderTable`.*,
                         `userTable`.`userName`,`userTable`.`userImage`,`serverTable`.`userName` AS `serverName`,`serverTable`.`userImage` AS `serverImage`,
                         `serviceTable`.`servicePhone`,`serviceTable`.`serviceImage`,`serviceTable`.`serviceName`,
                         `serviceTable`.`serviceAddress`,`serviceTable`.`serviceCity`,`serviceTable`.`serviceProvince`,`serviceTable`.`servicePostal`,
                         `categoryTable`.`categoryName`,
                         `userReview`.`reviewId` AS `userReview`,`userReview`.`reviewContent` AS `userReviewContent`,`userReview`.`reviewStar` AS `userReviewStar`,`userReview`.`createTime` AS `userReviewCreateTime`,
                         `serverReview`.`reviewId` AS `serverReview`,`serverReview`.`reviewContent` AS `serverReviewContent`,`serverReview`.`reviewStar` AS `serverReviewStar`,`serverReview`.`createTime` AS `serverReviewCreateTime`
                         From `serviceOrderTable` 
                         LEFT JOIN `serviceTable` ON `serviceTable`.`serviceId` = `serviceOrderTable`.`serviceId`
                         LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `serviceTable`.`serviceCategory`
                         LEFT JOIN `userTable` ON `userTable`.`userId` = `serviceOrderTable`.`userId`
                         LEFT JOIN `userTable` `serverTable` ON `serverTable`.`userId` = `serviceOrderTable`.`serverId`
                         LEFT JOIN `reviewTable` `userReview` ON `userReview`.`orderId` = `serviceOrderTable`.`serviceOrderId` 
                         AND `userReview`.`fromId` = `serviceOrderTable`.`userId` AND `userReview`.`targetType` = '0'
                         LEFT JOIN `reviewTable` `serverReview` ON `serverReview`.`orderId` = `serviceOrderTable`.`serviceOrderId` 
                         AND `serverReview`.`fromId` = `serviceOrderTable`.`serverId` AND `serverReview`.`targetType` = '2'
                         WHERE 1 ".$searchSql);
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["serviceImage"] = json_decode($row["serviceImage"], true); 
      $row["categoryName"] = json_decode($row["categoryName"], true); 
      $row["serviceInfo"] = json_decode($row["serviceInfo"], true); 
      $row["serviceOrderPetCard"] = json_decode($row["serviceOrderPetCard"], true); 
      $row["serviceOrderPetInfo"] = json_decode($row["serviceOrderPetInfo"], true); 
      $row["serviceOrderDays"] = json_decode($row["serviceOrderDays"], true);
      $row["OrderDayNumber"] = count($row["serviceOrderDays"]) - 1;
      $row["serviceOrderExtra"] = json_decode($row["serviceOrderExtra"], true); 
      $row["userReview"] = $row["userReview"] == null?false:true; 
      $row["serverReview"] = $row["serverReview"] == null?false:true; 
      // $row["orderState"] = $serviceOrderState[$row["orderState"]];
      $list[] = $row;
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }

  echo json_encode(["message"=>"success","data"=>$list]);
  exit();

}
