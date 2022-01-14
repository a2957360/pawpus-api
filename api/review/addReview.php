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
    // targetId可以是itemId或serviceId
    $targetId=$data['targetId'];
    $fromId=$data['fromId'];
    $orderId=$data['orderId'];
    $reviewContent=$data['reviewContent'];
    $reviewStar=$data['reviewStar'];
    // 0:用户给服务;1:用户给商品;2:服务者给用户  
    $targetType=$data['targetType'];

    //判断订单信息是否符合
    switch ($targetType) {
      case '0':
        $sql = "SELECT `serviceId` AS `targetId`,`serverId` From `serviceOrderTable` WHERE `serviceOrderId`= '$orderId'";
        break;
      case '1':
        $targetId = json_encode($targetId,JSON_UNESCAPED_UNICODE);
        $sql = "SELECT `itemList` AS `targetId` From `itemOrderTable` WHERE `itemOrderId`= '$orderId'";
        break;
      case '2':
        $sql = "SELECT `userId` AS `targetId` From `serviceOrderTable` WHERE `serviceOrderId`= '$orderId'";
        break;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    if($stmt != null){
      $row=$stmt->fetch(PDO::FETCH_ASSOC);
      $serverId = $row['serverId'];
      if($targetId != $row['targetId'] && $targetType != '1'){
        echo json_encode(["message"=>"no match"]);
        exit(); 
      }
    }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
    }

    //serviceType 0:寄样;1:日托;2:遛狗  
    $stmt = $pdo->prepare("INSERT INTO `reviewTable`(`targetId`, `fromId`, `orderId`, `reviewContent`, `reviewStar`, `targetType`)
                           VALUES ('$targetId','$fromId','$orderId','$reviewContent','$reviewStar','$targetType')");
    $stmt->execute();
    if($stmt != null){
        $serviceId = $pdo->lastInsertId();
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }

    //更新Rate字段

    switch ($targetType) {
      case '0':
        //修改服务
        $stmt = $pdo->prepare("UPDATE `serviceTable` SET `serviceStar` = (SELECT AVG(`reviewStar`) FROM `reviewTable` WHERE `targetId`='$targetId' AND `targetType` = '0')
                                WHERE `serviceId` = '$targetId'");
        $stmt->execute();
        $stmt = $pdo->prepare("UPDATE `userTable` SET `serverStar` = (SELECT AVG(`serviceStar`) FROM `serviceTable` WHERE `userId`='$serverId')
                                WHERE `userId` = '$serverId'");
        $stmt->execute();
        break;
      case '1':
        $stmt = $pdo->prepare("UPDATE `itemTable` SET `itemStar` = (SELECT AVG(`reviewStar`) FROM `reviewTable` WHERE `targetId` = '$targetId' AND `targetType` = '1')
                                WHERE `itemId` = '$targetId'");
        $stmt->execute();
        break;
    }

    if($serviceId != 0){
      echo json_encode(["message"=>"success"]);
      exit();
    }
    echo json_encode(["message"=>"fail"]);
    exit();
  }
