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

    date_default_timezone_set("UTC");
    function String2Hex($string){
        $hex='';
        for ($i=0; $i < strlen($string); $i++){
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }
  $orderId = $data['orderId'];
  $userId = $data['userId'];
  //0服务 1商品
  $orderType = $data['orderType'];
  if($orderType == 0){
    $sql = "SELECT `serviceOrderTotalPyament` AS `total`,`serviceOrderNo` AS `orderNo` From `serviceOrderTable` WHERE `serviceOrderId` = '$orderId' AND `userId` = '$userId'";
    $notify_url = ALPHAPAY_NOTIFY_URL."alphaPayServiceFinsih.php";
  }else{
    $sql = "SELECT `total`,`orderNo` From `itemOrderTable` WHERE `itemOrderId` = '$orderId' AND `userId` = '$userId'";
    $notify_url = ALPHAPAY_NOTIFY_URL."alphaPayItemFinsih.php";

  }
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $total = $row['total'];
      $orderNo = $row['orderNo'];
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }

  if(!isset($total)){
    echo json_encode(["message"=>"no order"]);  
    exit();
  }

    $partner_code=ALPHAPAY_PARTNER_CODE;
    $credential_code=ALPHAPAY_CREDENTIAL_CODE;

    //总价
    $trans_amount = (float)$total*100;
    $orderId = $orderNo;

    $time=(int)(microtime(true)*1000);
    $nonce_str=rand(00000000000,99999999999);

    $valid_string = $partner_code."&".$time."&".$nonce_str."&".$credential_code;
    // $sign=strtolower(bin2hex(hash('sha256', $valid_string)));
    $sign=strtolower(hash('sha256', $valid_string));
    // echo $sign;

    $url = "https://pay.alphapay.ca/api/v1.0/gateway/partners/".$partner_code."/orders/".$orderId."?time=".$time."&nonce_str=".$nonce_str."&sign=".$sign;
    $data = array("description"=>"Finestudio Payment",
                    "price"=>$trans_amount,
                    "currency"=>"CAD",
                    "channel"=>"Alipay",
                    "notify_url"=>$notify_url,
                    "operator"=>"dev01"
                    );
    $data_string = json_encode($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json','Content-Length: ' . strlen($data_string))); 
    $result = curl_exec($ch);
    $result = json_decode($result, true);
    $value = $result['qrcode_img']; //二维码内容 
    if($result['return_code'] == "SUCCESS"){
        echo json_encode(["message"=>"success","data"=>["qrCode"=>$value]]);
        exit();
    }
      echo json_encode(["message"=>"error"]);
      exit();
?>
