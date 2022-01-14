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

  	$couponCode = $data['couponCode'];
  	$total = $data['total'];
  	$subTotal = $data['subTotal'];
	$today = date("y-m-d");
	$stmt = $pdo->prepare("SELECT * From `couponTable` WHERE `couponCode` = '$couponCode' AND `avaiableTimes` > 0 AND `expireDate` <= '$today' AND `couponType`='1'");
	$stmt->execute();
	if($stmt != null){
		$row=$stmt->fetch(PDO::FETCH_ASSOC);
		if(!isset($row['couponId'])){
		  echo json_encode(["message"=>"no coupon"]);
		  exit();
		}
		//  0:满减;1:满折;  
		$discountType = $row['discountType'];
		$couponrequirePrice = $row['couponrequirePrice'];
		$couponValue = $row['couponValue'];
		if($couponrequirePrice > $subTotal){
		  echo json_encode(["message"=>"low price"]);
		  exit();
		}
		if ($discountType == 0) {
			$coupon = $couponValue;
		}else{
			$coupon = round((float)$total * (float)$couponValue * 0.01,2);
		}
		echo json_encode(["message"=>"success","data"=>["couponrequirePrice"=>$couponrequirePrice,"discountType"=>$discountType,"couponValue"=>$couponValue,"coupon"=>(float)$coupon]]);
		exit();
	}else{
		echo json_encode(["message"=>"database error"]);
		exit();
	}
}