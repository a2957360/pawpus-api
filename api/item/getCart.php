<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $language=$data['language'];
    //查询
    $userId=$_GET['userId'];

    if(!isset($userId) || $userId== "" || $userId== 0){
      echo json_encode(["message"=>"no user"]);
      exit();
    }

    $cartList = array();
    //产品
    $itemList = array();
    //返回
    $dataList = array();
    //用来搜索产品和规格
    $itemString = array();
    $optionString = array();
    $subTotal = 0;
    $tax = 0;
    $total = 0;
    $deliverPrice = 0;
    $coupon = 0;

    //获取购物车信息
    $stmt = $pdo->prepare("SELECT * From `cartTable` WHERE `userId` = '$userId'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        //获取coupon
        $couponCode=$row['couponCode'];
        $deliverPrice=$row['deliverPrice'];
        $cartId=$row['cartId'];
        $cartList = $row['itemList'] != ""?json_decode($row['itemList'],true):array();
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    if(isset($couponCode)){
      //获取coupon信息
      $today = date("y-m-d");
      $stmt = $pdo->prepare("SELECT * From `couponTable` WHERE `couponCode` = '$couponCode' AND `avaiableTimes` > 0 AND `expireDate` >= '$today' AND `couponType`='0'");
      $stmt->execute();
      if($stmt != null){
        $row=$stmt->fetch(PDO::FETCH_ASSOC);
        // echo "SELECT * From `couponTable` WHERE `couponCode` = '$couponCode' AND `avaiableTimes` > 0 AND `expireDate` <= '$today' AND `couponType`='0'";
        if(!isset($row['couponId'])){
          $stmt = $pdo->prepare("UPDATE `cartTable` SET `couponCode` = '' WHERE `userId` = '$userId'");
          $stmt->execute();
          $couponCode="";
          //没有相应的coupon
          // echo json_encode(["message"=>"fail"]);
          // exit();
        }else{
          //  0:满减;1:满折;  
          $discountType = $row['discountType'];
          $couponrequirePrice = $row['couponrequirePrice'];
          $couponValue = $row['couponValue'];
        }

      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      }
    }

    
    //获取购物车每个产品
    foreach ($cartList as $key => $value) {
      $dataList[$value['itemOptionId']] = $value;
      $itemString[] = $value['itemId'];
      $optionString[] = $value['itemOptionId'];
    }
    $itemString = array_unique($itemString);
    $itemString = implode(",", $itemString);
    $optionString = implode(",", $optionString);

    //获取产品
    $stmt = $pdo->prepare("SELECT `itemId`,`itemTitle`,`itemImage`,`itemTax` From `itemTable` WHERE `itemId` IN ($itemString) AND itemState = '0'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row["itemImage"] = json_decode($row["itemImage"], true); 
        $itemList[$row['itemId']] = $row;
      }
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }
    //获取规格
    $stmt = $pdo->prepare("SELECT * From `itemOptionTable` WHERE `itemOptionId` IN ($optionString)");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        //计算每个产品总价
        $itemQuantity= $dataList[$row['itemOptionId']]['itemQuantity'];
        $itemPrice = $row['itemOptionSalePrice'] == 0?$row['itemOptionPrice']:$row['itemOptionSalePrice'];
        $itemTax= $itemList[$row['itemId']]['itemTax'];

        $row['subTotal'] = round((float)$itemPrice * (float)$itemQuantity,2);
        $row['tax'] = round((float)$row['subTotal'] * (float)$itemTax * 0.01,2);
        $row['total'] = round((float)$row['subTotal'] + (float)$row['tax'],2);
        $subTotal += $row['subTotal'];
        $tax += $row['tax'];
        $total += $row['total'];
        //删除不需要部分
        unset($row['itemTax']);
        unset($row['itemOptionSku']);
        unset($row['itemOptionStock']);
        unset($row['itemOptionState']);
        unset($row['createTime']);

        //合并产品信息
        $row = array_merge($itemList[$row['itemId']],$row);
        // var_dump($itemList[$row['itemId']]);
        $dataList[$row['itemOptionId']] = array_merge($dataList[$row['itemOptionId']],$row);
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }
    if(isset($discountType)){
      if ($discountType == 0) {
        $coupon = $couponValue;
      }else{
        $coupon = round((float)$total * (float)$couponValue * 0.01,2);
      }
    }

    if($couponrequirePrice > $subTotal){
      $coupon = 0;
    }
    
    $total = round((float)$total + (float)$deliverPrice - (float)$coupon,2);
    $total = $total < 0?0:$total;
    $itemList = array_values($dataList);
    $dataList = ["subTotal"=>round($subTotal,2),
                 "tax"=>round($tax,2),
                 "total"=>round($total,2),
                 "deliverPrice"=>$deliverPrice,
                 "coupon"=>$coupon,
                 "couponCode"=>$couponCode,
                 "cartId"=>$cartId,
                 "itemList"=>$itemList];
    $itemList = json_encode($itemList,JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare("UPDATE `cartTable` SET 
                      `itemList` = '$itemList',`subTotal` = '$subTotal',`tax`='$tax',`total`='$total',`deliverPrice`='$deliverPrice',`coupon`='$coupon'
                      WHERE `userId` = '$userId'");
    $stmt->execute();
    // if($stmt->rowCount() > 0){
    //   echo json_encode(["message"=>"success","data"=>$dataList]);
    //   exit();
    // }

    // echo json_encode(["message"=>"error"]);
    // exit();
      echo json_encode(["message"=>"success","data"=>$dataList]);
      exit();
  }
