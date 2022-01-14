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
    $language=$data['language'];
    //查询
    $userId=$data['userId'];
    $itemId=$data['itemId'];
    $itemOptionId=$data['itemOptionId'];
    $itemQuantity=$data['itemQuantity'];

    if(!isset($userId) || $userId== "" || $userId== 0){
      echo json_encode(["message"=>"no user"]);
      exit();
    }

    $cartList = array();
    $stmt = $pdo->prepare("SELECT * From `cartTable` WHERE `userId` = '$userId'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $cartList = $row['itemList'] != ""?json_decode($row['itemList'],true):array();
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }
    $itemNumber = 0;
    $isAdd = false;
    foreach ($cartList as $key => $value) {
      if($value['itemId'] == $itemId && $value['itemOptionId'] == $itemOptionId){
      	$isAdd = true;
        $cartList[$key]['itemQuantity'] = (int)$itemQuantity;
      }
      $itemNumber += (int)$cartList[$key]['itemQuantity'];
    }
    if(!$isAdd){
        echo json_encode(["message"=>"no item"]);
        exit();
    }

    $uploadcartList = json_encode($cartList,JSON_UNESCAPED_UNICODE);

    //解决id自增问题
    $stmt = $pdo->prepare("UPDATE `cartTable` SET 
                      `itemList` = '$uploadcartList',`itemNumber`='$itemNumber'
                      WHERE `userId` = '$userId'");
    $stmt->execute();
    if($stmt->rowCount() == 0){
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    echo json_encode(["message"=>"success","data"=>$cartList]);
    exit();

  }
