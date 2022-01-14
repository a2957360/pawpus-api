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

    $userId=$data['userId'];
    $cartId=$data['cartId'];
    $storeId='0';
    $itemOrderComment=$data['itemOrderComment'];
    $address = json_encode($data['address'],JSON_UNESCAPED_UNICODE);

    //从购物车获取item数据
    $stmt = $pdo->prepare("SELECT * From `cartTable` WHERE `cartId` = '$cartId' AND `userId` = '$userId'");
    $stmt->execute();
    if($stmt != null){
      $row=$stmt->fetch(PDO::FETCH_ASSOC);
        if(!isset($row['itemList']) || !isset($row['subTotal'])|| !isset($row['total'])){
          echo json_encode(["message"=>"no cart"]);
          exit();
        }
        $itemList = $row['itemList'];
        $subTotal=$row['subTotal'];
        $tax=$row['tax'];
        $total=$row['total'];
        $deliverPrice=$row['deliverPrice'];
        $coupon=$row['coupon'];
        $couponCode=$row['couponCode'];
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }

    $stmt = $pdo->prepare("INSERT INTO `itemOrderTable`(`userId`, `storeId`, `itemList`, `couponCode`, `subTotal`, `tax`, `deliverPrice`, `coupon`, `total`, `refundPrice`, 
                            `paymentType`, `address`, `deliverTrack`, `itemOrderComment`, `orderState`)
                           VALUES ('$userId','$storeId','$itemList','$couponCode','$subTotal','$tax','$deliverPrice','$coupon','$total',
                           '0','','$address','','$itemOrderComment','0')");
    $stmt->execute();
    if($stmt != null){
        $itemOrderId = $pdo->lastInsertId();
        if($itemOrderId != 0){
          $orderNo = "PP".date("ymdHis").$itemOrderId;
          $stmt = $pdo->prepare("UPDATE `itemOrderTable` SET `orderNo` = '$orderNo' WHERE `itemOrderId` = '$itemOrderId'");
          $stmt->execute();
          $stmt = $pdo->prepare("DELETE FROM `cartTable` WHERE `cartId` = '$cartId'");
          $stmt->execute();
          $data['orderNo'] = $orderNo;
          $data['itemOrderId'] = $itemOrderId;
          echo json_encode(["message"=>"success","data"=>$data]);
          exit();
        }
        echo json_encode(["message"=>"fail"]);
        exit();
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }
  }
