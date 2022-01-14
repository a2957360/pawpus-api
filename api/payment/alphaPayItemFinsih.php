<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");
  include("sendNotifition.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  $data = file_get_contents('php://input');
  $data = json_decode($data,true);

	  $order_id=$data['partner_order_id'];

    $stmt = $pdo->prepare("INSERT INTO `testAlphaPay`(`Isupdate`) VALUES ('$order_id')");
    $stmt->execute();

	//查询订单状态
    $partner_code=ALPHAPAY_PARTNER_CODE;
    $credential_code=ALPHAPAY_CREDENTIAL_CODE;
    $redirect=ALPHAPAY_REDIRECT_URL;

    $time=(int)(microtime(true)*1000);
    $nonce_str=rand(00000000000,99999999999);

    $valid_string = $partner_code."&".$time."&".$nonce_str."&".$credential_code;
    // $sign=strtolower(bin2hex(hash('sha256', $valid_string)));
    $sign=strtolower(hash('sha256', $valid_string));
    // echo $sign;

    $url = "https://pay.alphapay.ca/api/v1.0/gateway/partners/".$partner_code."/orders/".$order_id."?time=".$time."&nonce_str=".$nonce_str."&sign=".$sign;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json','Content-Length: ' . strlen($data_string))); 
    $result = curl_exec($ch);
    $result = json_decode($result, true);
    // date_default_timezone_set("America/Toronto");
    // var_dump(date_default_timezone_get()." ".date("Y-m-d H:i:s"));
    // var_dump($result['result_code']);
    if($result['result_code'] != "PAY_SUCCESS"){
      echo json_encode(["message"=>"error"]);
      exit();
    }
    //修改订单状态
    $stmt = $pdo->prepare("UPDATE `itemOrderTable` SET `orderState`='1', `paymentType` = 'AlphaPay' WHERE `orderNo` = '$order_id' AND `orderState` = '0'");
    // $stmt = $pdo->prepare("UPDATE `orderTable` SET `orderState`='1',`orderToken` = '$order_id' WHERE `orderNo` = '$order_id' AND `orderState` = '0'");
    $stmt->execute();
    $orderList = array();
    $stmt = $pdo->prepare("SELECT `itemOrderTable`.* From `itemOrderTable` 
                          WHERE `orderNo` = '$order_id'");
    $stmt->execute();
    $sqldriverNumber = 0;
    if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $itemList = json_decode($row["itemList"], true);
        }
    }else{
          echo json_encode(["message"=>"database error"]);
          exit();
    }
    
    //给商品添加销量
    foreach ($itemList as $key => $value) {
      $itemId = $value['itemId'];
      $itemQuantity = (int)$value['itemQuantity'];
      $stmt = $pdo->prepare("UPDATE `itemTable` SET `itemSaleNumber` = `itemSaleNumber`+'$itemQuantity' WHERE `itemId` = '$itemId'");
      $stmt->execute();
    }

    //二维码跳转
    // $time=(int)(microtime(true)*1000);
    // $nonce_str=rand(00000000000,99999999999);

    // $valid_string = $partner_code."&".$time."&".$nonce_str."&".$credential_code;
    // // $sign=strtolower(bin2hex(hash('sha256', $valid_string)));
    // $sign=strtolower(hash('sha256', $valid_string));
    // // echo $sign;

    // $url = "https://pay.alphapay.ca/api/v1.0/gateway/partners/".$partner_code."/orders/".$order_id."/pay?redirect=".$redirect."&time=".$time."&nonce_str=".$nonce_str."&sign=".$sign;
    // $ch = curl_init($url);
    // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json','Content-Length: ' . strlen($data_string),'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36"')); 
    // $result = curl_exec($ch);
    // $result = json_decode($result, true);
    // var_dump($result);
?>

