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
    //查询
    if(isset($data['isGet']) && $data['isGet'] !== ""){

      $exchangeState = EXCHANGE_STATE;
      $categoryType=$data['categoryType'];
      $categoryId=$data['categoryId'];
      $searchSql .= isset($categoryType)?" AND `exchangeTable`.``='$categoryType'":"";
      $searchSql .= isset($categoryId)?" AND `categoryId`='$categoryId'":"";

      $list = array();
      $stmt = $pdo->prepare("SELECT `exchangeTable`.*,`userTable`.`userName`,`userTable`.`userImage`,`userTable`.`userEmail`,`userTable`.`userPhone` From `exchangeTable` 
                              LEFT JOIN `userTable` ON `userTable`.`userId` = `exchangeTable`.`userId`
                              WHERE `exchangeTable`.`userId` != '0' ".$searchSql."order By `exchangeTable`.`createTime` DESC,`exchangeTable`.`exchangeState` ASC");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row['exchangeStateDisplay'] = $exchangeState[$row['exchangeState']];
          $list[] = $row;
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }

      echo json_encode(["message"=>"success","data"=>$list]);
      exit();
    }

    //转账完成
    if(isset($data['isFinish']) && $data['isFinish'] !== ""){
      $exchangeId=$data['exchangeId'];
      $stmt = $pdo->prepare("UPDATE `exchangeTable` SET `exchangeState` = '1' WHERE `exchangeId` = '$exchangeId'");
      $stmt->execute();
      if($stmt != null && $stmt->rowCount() > 0){
        echo json_encode(["message"=>"success"]);
        exit();
      }
      echo json_encode(["message"=>"fail"]);
      exit();
    }

    if(isset($data['isCancel']) && $data['isCancel'] !== ""){
      $exchangeId=$data['exchangeId'];
      $exchangePrice=$data['exchangePrice'];
      $stmt = $pdo->prepare("UPDATE `exchangeTable` SET `exchangeState` = '2' WHERE `exchangeId` = '$exchangeId'");
      $stmt->execute();
      if($stmt != null && $stmt->rowCount() > 0){
        $stmt = $pdo->prepare("UPDATE `userTable` SET `serverPoints` = `serverPoints` + '$exchangePrice' WHERE `userId` = 
                              (SELECT `userId` FROM `exchangeTable` WHERE `exchangeId` = '$exchangeId')");
        $stmt->execute();
        echo json_encode(["message"=>"success"]);
        exit();
      }
      echo json_encode(["message"=>"fail"]);
      exit();
    }

    $userId=$data['userId'];
    $exchangePrice=$data['exchangePrice'];
    
    $stmt = $pdo->prepare("INSERT INTO `exchangeTable`(`userId`,`exchangePrice`) VALUES ('$userId','$exchangePrice')");
    $stmt->execute();
    if($stmt != null){
        $serverId = $pdo->lastInsertId();
        if($serverId != 0){
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

