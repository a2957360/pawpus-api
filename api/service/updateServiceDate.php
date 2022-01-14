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

    $serviceId=$data['serviceId'];
	  $serviceBlockDate = json_encode($data['serviceBlockDate'],JSON_UNESCAPED_UNICODE);
    $endDate=date('Y-m-t',strtotime($data['endDate']));
    $checkinTime=$data['checkinTime'];
    $checkoutTime=$data['checkoutTime'];

    //serviceType 0:寄样;1:日托;2:遛狗  
    $stmt = $pdo->prepare("UPDATE `serviceTable` SET `endDate`='$endDate',`serviceBlockDate`='$serviceBlockDate',`checkinTime`='$checkinTime',`checkoutTime`='$checkoutTime'
                           WHERE `serviceId`='$serviceId'");
    $stmt->execute();
    if($stmt != null){
        if($stmt->rowCount() != 0){
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
