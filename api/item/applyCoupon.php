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
    $couponCode=$data['couponCode'];

    if(!isset($userId) || $userId== "" || $userId== 0){
      echo json_encode(["message"=>"no user"]);
      exit();
    }

    //获取
    $cartList = array();
    $stmt = $pdo->prepare("SELECT * From `cartTable` WHERE `userId` = '$userId'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $cartList = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    $today = date("y-m-d");
    $stmt = $pdo->prepare("SELECT *,(SELECT count(*) FROM `itemOrderTable` WHERE `couponCode` = '$couponCode') AS `couponTime` From `couponTable`
                           WHERE `couponCode` = '$couponCode' AND `expireDate` >= '$today' AND `couponType`='0'");
    $stmt->execute();
    if($stmt != null){
      $row=$stmt->fetch(PDO::FETCH_ASSOC);
      if(!isset($row['couponId'])){
        //没有相应的coupon
        echo json_encode(["message"=>"fail"]);
        exit();
      }
      if($row['couponTime'] >= $row['avaiableTimes']){
        //没有相应的coupon
        echo json_encode(["message"=>"no time"]);
        exit();
      }
      //  0:满减;1:满折;  
      $discountType = $row['discountType'];
      $couponrequirePrice = $row['couponrequirePrice'];
      $couponValue = $row['couponValue'];

    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    if($cartList['subTotal'] < $couponrequirePrice){
      //没有满足coupon要求
      echo json_encode(["message"=>"fail"]);
      exit();
    }

    
    //解决id自增问题
    $stmt = $pdo->prepare("UPDATE `cartTable` SET `couponCode`='$couponCode'
                      WHERE `userId` = '$userId'");
    $stmt->execute();
    if($stmt->rowCount() == 0){
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    // echo json_encode(["message"=>"success","data"=>$cartList]);
    echo json_encode(["message"=>"success"]);
    exit();

  }
