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
    //网站url
    $websiteLink = WEBSITE_LINK;

    //房东Id
    $userId=$data['userId'];
    $serviceOrderId=$data['serviceOrderId'];

    $stmt = $pdo->prepare("SELECT `serviceOrderTable`.*,`orderCount`.`orderNumber` From `serviceOrderTable`
                           LEFT JOIN (SELECT count(`serviceOrderId`) AS `orderNumber`,`serverId` FROM `serviceOrderTable` WHERE `serverId` = 'userId') `orderCount`
                           ON ``orderCount`.`serverId` = `serviceOrderTable`.`serverId`
                           WHERE `serviceOrderId` = 'serviceOrderId' AND `serverId` = 'userId'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $serviceOrderTotalPrice = $row['serviceOrderTotalPrice'];
        $serverChargeFee = $row['serverChargeFee'];
        $list[] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);  
        exit();
    }
    //计算商家获得价格
    $serverGetPrice = $serviceOrderTotalPrice - $serverChargeFee;
    //orderState  0:待付款;1:待商家确认;2:等待寄送;3:寄养中;4:服务结束;5:已取消
    $stmt = $pdo->prepare("UPDATE `serviceTable` SET `orderState`='4'
                           WHERE `userId`='$userId' AND `serviceOrderId`='$serviceOrderId' AND `orderState`='0'");
    $stmt->execute();
    if($stmt != null){
        if($stmt->rowCount() != 0){
          $stmt = $pdo->prepare("UPDATE `userTable` SET `serverPoints`= `serverPoints` + '$serverGetPrice' WHERE `userId`='$userId'");
          $stmt->execute();
          echo json_encode(["message"=>"success"]);
          exit();
        }
        echo json_encode(["message"=>"fail"]);
        exit();
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }
  }
