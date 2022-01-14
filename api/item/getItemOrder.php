<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $itemOrderId=$_GET['itemOrderId'];
  $userId=$_GET['userId'];
  //  0:未支付;1:待发货;2:已发货;3:已收货;4:退款中;5:退款成功;6:拒绝退款
  $orderState =$_GET['orderState'];

  $itemOrderState=ITEM_ORDER_STATE; 

  if(!isset($userId)){
    echo json_encode(["message"=>"no user"]);  
    exit();
  }

  $searchSql .= isset($itemOrderId)?" AND `itemOrderTable`.`itemOrderId`='$itemOrderId'":"";
  $searchSql .= isset($userId)?" AND `itemOrderTable`.`userId`='$userId'":"";
  $searchSql .= isset($orderState)?" AND `itemOrderTable`.`orderState`='$orderState'":"";

  $list = array();
  $stmt = $pdo->prepare("SELECT `itemOrderTable`.*,`userTable`.`userName`,`reviewTable`.`reviewId` AS `userReview`
                         FROM `itemOrderTable` 
                         LEFT JOIN `userTable` ON `userTable`.`userId` = `itemOrderTable`.`userId`
                         LEFT JOIN `reviewTable`ON `reviewTable`.`orderId` = `itemOrderTable`.`itemOrderId` 
                         AND `reviewTable`.`fromId` = `itemOrderTable`.`userId` AND `reviewTable`.`targetType` = '1'
                         WHERE 1 ".$searchSql." ORDER BY `itemOrderTable`.`createTime` DESC");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["itemList"] = json_decode($row["itemList"], true); 
      $row["address"] = json_decode($row["address"], true); 
      $row["userReview"] = $row["userReview"] == null?false:true;
      // $row["orderState"] = $itemOrderState[$row["orderState"]];
      $list[] = $row;
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }

  echo json_encode(["message"=>"success","data"=>$list]);
  exit();

}
