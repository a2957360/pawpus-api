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
  if(isset($data['isGet']) && $data['isGet'] !== ""){

    $serviceOrderId=$data['serviceOrderId'];
    $userId=$data['userId'];
    $serverId=$data['serverId'];
    //0:待付款;1:待商家确认;2:等待寄送;3:寄养中;4:服务结束;5:已取消 
    $orderState=$data['orderState'];

    $serviceOrderState=SERVICE_ORDER_STATE; 

    // if(!isset($userId) && !isset($serverId)){
    //   echo json_encode(["message"=>"no user"]);  
    //   exit();
    // }

    $searchSql .= isset($serviceOrderId)?" AND `serviceOrderTable`.`serviceOrderId`='$serviceOrderId'":"";
    $searchSql .= isset($userId)?" AND `serviceOrderTable`.`userId`='$userId'":"";
    $searchSql .= isset($serverId)?" AND `serviceOrderTable`.`serverId`='$serverId'":"";
    $searchSql .= isset($orderState)?" AND `serviceOrderTable`.`orderState`='$orderState'":"";

    $list = array();
    $stmt = $pdo->prepare("SELECT `serviceOrderTable`.*,
                           `userTable`.`userName`,`serverTable`.`userName` AS `serverName`,`serviceTable`.`servicePhone`,
                           `categoryTable`.`categoryName`
                           From `serviceOrderTable` 
                           LEFT JOIN `serviceTable` ON `serviceTable`.`serviceId` = `serviceOrderTable`.`serviceId`
                           LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `serviceTable`.`serviceCategory`
                           LEFT JOIN `userTable` ON `userTable`.`userId` = `serviceOrderTable`.`userId`
                           LEFT JOIN `userTable` `serverTable` ON `serverTable`.`userId` = `serviceOrderTable`.`serverId`
                           WHERE 1 ".$searchSql." ORDER BY `serviceOrderTable`.`createTime` DESC");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row["categoryName"] = json_decode($row["categoryName"], true); 
        $row["serviceInfo"] = json_decode($row["serviceInfo"], true); 
        $row["serviceOrderDays"] = json_decode($row["serviceOrderDays"], true);
        $row["serviceOrderPetInfo"] = json_decode($row["serviceOrderPetInfo"], true);
        $row["serviceOrderPetCard"] = json_decode($row["serviceOrderPetCard"], true);
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

  //获取统计
  if(isset($data['isGetStatistic']) && $data['isGetStatistic'] !== ""){
    $startDate=$data['startDate']." 00:00:00";
    $endDate=$data['endDate']." 23:59:59";

    $searchSql .= isset($startDate) && isset($endDate)?" AND `itemOrderTable`.`createTime`>='$startDate' AND `itemOrderTable`.`createTime`<='$endDate'":"";

    $list = array();
    $serviceList = array();
    $stmt = $pdo->prepare("SELECT COALESCE(sum(`serviceOrderTable`.`serviceOrderTotalPrice`),0) AS `totalPrice`,count(`serviceOrderTable`.`serviceOrderId`) AS `orderNumber`
                           FROM `serviceOrderTable`
                           WHERE `serviceOrderTable`.`createTime`>='$startDate' AND `serviceOrderTable`.`createTime`<='$endDate' AND `orderState` >= '1' AND `orderState` < '5'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row['totalPrice'] = round($row['totalPrice'],2);
        $list = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);  
        exit();
    }

    //排序-》取前十位-》取key-》转换成string
    $stmt = $pdo->prepare("SELECT `serviceTable`.*,sum(`serviceOrderTable`.`serviceOrderTotalPrice`) AS `totalPrice`,`categoryTable`.`categoryName`,`userTable`.`userName`
                           From `serviceOrderTable` 
                           LEFT JOIN `serviceTable` ON `serviceOrderTable`.`serviceId` = `serviceTable`.`serviceId`
                           LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `serviceTable`.`serviceCategory`
                           LEFT JOIN `userTable` ON `userTable`.`userId` = `serviceTable`.`userId`
                           WHERE `serviceOrderTable`.`createTime`>='$startDate' AND `serviceOrderTable`.`createTime`<='$endDate'  AND `orderState` >= '1' AND `orderState` < '5'
                           ORDER BY `totalPrice` DESC");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        if($row['serviceId'] == null){
          continue;
        }
        $row['totalPrice'] = round($row['totalPrice'],2);
        $row["serviceImage"] = json_decode($row["serviceImage"], true);
        $serviceList[] = $row;
      }
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }

    $list['serviceList'] = $serviceList;
    echo json_encode(["message"=>"success","data"=>$list]);
    exit();
  }

}
