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
    $serviceOrderPetInfo = $data['serviceOrderPetInfo'];
    $orderStartDate=$data['orderStartDate'];
    $orderEndDate=$data['orderEndDate'];
    $serviceOrderExtra = $data['serviceOrderExtra'];

    $serviceOrderRentPrice=0;
    $serviceOrderExtraPrice=0;
    $serviceOrderTaxPrice=0;
    $serviceOrderCouponPrice=0;
    $serviceOrderTotalPrice=0;

    //获取config信息 configType 0：提成
    $config = array();
    $stmt = $pdo->prepare("SELECT `configName`,`configValue` From `configTable` 
                            WHERE `configType` = '0'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
	    $config[$row['configName']] = $row['configValue'];
	  }
    }

    $stmt = $pdo->prepare("SELECT * From `serviceTable` 
                            WHERE `serviceTable`.`serviceState` = '1' AND `serviceId` = '$serviceId'");
    $stmt->execute();
    if($stmt != null){
      $row=$stmt->fetch(PDO::FETCH_ASSOC);
      if(!isset($row['serviceId'])){
        echo json_encode(["message"=>"no service"]);
        exit();
      }
      //税
      $serviceTax = $row['serviceTax'];
    }
    //入住天数
    $duration=floor((strtotime($orderEndDate)-strtotime($orderStartDate))/86400);
    //房费
    foreach ($serviceOrderPetInfo as $key => $value) {
      $serviceOrderRentPrice += round((float)$value['price'] * (float)$value['number'] * $duration,2);
    }
    //额外服务费
    foreach ($serviceOrderExtra as $key => $value) {
      $serviceOrderExtraPrice += round((float)$value['price'] * (float)$value['number'] * $duration,2);
    }
    //税
    // $serviceOrderTaxPrice = round(($serviceOrderRentPrice + $serviceOrderExtraPrice) * (float)$serviceTax * 0.01, 2);
    $serviceOrderTaxPrice = round(($serviceOrderRentPrice + $serviceOrderExtraPrice) * 13 * 0.01, 2);
    //加税总价
    $serviceOrderTotalPrice = $serviceOrderRentPrice + $serviceOrderExtraPrice + $serviceOrderTaxPrice;
    //计算顾客需要额外的费用
    $clientChargeFee = round($serviceOrderTotalPrice * $config['clientRate'] * 0.01,2);
    $clientChargeFee = $clientChargeFee > $config['clientMax']?(float)$config['clientMax']:$clientChargeFee;
    //计算房东的服务费用
    $serverChargeFee = round($serviceOrderTotalPrice * $config['serverRate'] * 0.01,2);
    //计算顾客最终需要支付的费用
    $serviceOrderTotalPyament = round($clientChargeFee + $serviceOrderTotalPrice,2);

    $data = ["serviceOrderRentPrice"=>$serviceOrderRentPrice,
            "serviceOrderExtraPrice"=>$serviceOrderExtraPrice,
            "serviceOrderTaxPrice"=>$serviceOrderTaxPrice,
            "clientChargeFee"=>$clientChargeFee,
            "serverChargeFee"=>$serverChargeFee,
            "serviceOrderTotalPrice"=>$serviceOrderTotalPrice,
            "serviceOrderTotalPyament"=>$serviceOrderTotalPyament,
        ];
    echo json_encode(["message"=>"success","data"=>$data]);
    exit();

    // //清除5分钟之前的数据
    // $todaymin5 = date('Y-m-d H:i:s', strtotime("-10 minute"));
    // $stmt = $pdo->prepare("DELETE FROM `serviceOrderTable` WHERE `creatTime` < '$todaymin5' AND `orderState` = '0'");
    // $stmt->execute();

    // //serviceType 0:寄样;1:日托;2:遛狗  
    // $stmt = $pdo->prepare("INSERT INTO `serviceOrderTable`(`userId`, `serverId`, `serviceId`, `serviceOrderPetInfo`, `serviceOrderPetCard`, `servicePetNumber`,
    //                          `serviceInfo`, `serviceOrderDays`,`orderStartDate`,`orderEndDate`,`serviceOrderExtra`, `serviceOrderRentPrice`, 
    //                         `serviceOrderExtraPrice`, `serviceOrderTaxPrice`, `serviceOrderCouponPrice`, `serviceOrderTotalPrice`)
    //                        VALUES ('$userId','$serverId','$serviceId','$serviceOrderPetInfo','$serviceOrderPetCard','$servicePetNumber','$serviceInfo','$serviceOrderDays','$orderStartDate',
    //                        '$orderEndDate','$serviceOrderExtra','$serviceOrderRentPrice','$serviceOrderExtraPrice','$serviceOrderTaxPrice','$serviceOrderCouponPrice','$serviceOrderTotalPrice')");
    // $stmt->execute();
    // if($stmt != null){
    //     $serviceId = $pdo->lastInsertId();
    //     if($serviceId != 0){
    //       echo json_encode(["message"=>"success"]);
    //       exit();
    //     }
    //     echo json_encode(["message"=>"fail"]);
    //     exit();
    // }else{
    //   echo json_encode(["message"=>"database error"]);
    //   exit();
    // }
  }
