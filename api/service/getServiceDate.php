<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $serviceId=$_GET['serviceId'];

  if(!isset($serviceId) && !isset($serviceId)){
    echo json_encode(["message"=>"no service"]);  
    exit();
  }

  $searchSql .= isset($serviceOrderId)?" AND `serviceOrderTable`.`serviceOrderId`='$serviceOrderId'":"";
  $searchSql .= isset($userId)?" AND `serviceOrderTable`.`userId`='$userId'":"";
  $searchSql .= isset($serverId)?" AND `serviceOrderTable`.`serverId`='$serverId'":"";
  $searchSql .= isset($orderState)?" AND `serviceOrderTable`.`orderState`='$orderState'":"";

  $dateList = array();
  $stmt = $pdo->prepare("SELECT `serviceOrderDays`,`userTable`.`userName`,`categoryTable`.`categoryName`,`servicePetNumber`,`serviceTable`.`serviceStock`
                         From `serviceOrderTable` 
                         LEFT JOIN `serviceTable` ON `serviceTable`.`serviceId` = `serviceOrderTable`.`serviceId`
                         LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `serviceTable`.`serviceCategory`
                         LEFT JOIN `userTable` ON `userTable`.`userId` = `serviceOrderTable`.`userId`
                         WHERE `serviceOrderTable`.`serviceId`='$serviceId'");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["serviceOrderDays"] = json_decode($row["serviceOrderDays"], true);
      $serviceStock = $row["serviceStock"];
      $servicePetNumber = $row["servicePetNumber"];
      $row["categoryName"] = json_decode($row["categoryName"], true); 
      foreach ($row["serviceOrderDays"] as $key => $value) {
        $dateList[$value]['list'][] =  $row["userName"]." ".$row["categoryName"]["Zh"]." ".$row["servicePetNumber"];
        //还可以选择的宠物数量
        $dateList[$value]['number'] =  isset($dateList[$value]['number'])?(int)$dateList[$value]['number'] - (int)$servicePetNumber:(int)$serviceStock - (int)$servicePetNumber;
      }
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }
  $stmt = $pdo->prepare("SELECT `serviceBlockDate` From `serviceTable` 
                          WHERE `serviceState` = '1' AND `serviceId` = '$serviceId'");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["serviceBlockDate"] = json_decode($row["serviceBlockDate"], true);
      foreach ($row["serviceBlockDate"] as $key => $value) {
        $dateList[$value] = ["number"=>0];
      }
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }

  echo json_encode(["message"=>"success","data"=>$dateList]);
  exit();

}
