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
  
  //获取开始到结束之间所有日期
  function periodDate($startDate, $endDate){
    $startTime = strtotime($startDate);
    $endTime = strtotime($endDate);
    $arr = array();
    while ($startTime <= $endTime){
        $arr[] = date('Y-m-d', $startTime);
        $startTime = strtotime('+1 day', $startTime);
    }
    return $arr;
  }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //网站url
    $websiteLink = WEBSITE_LINK;

    $userId=$data['userId'];
    $userPhone=$data['userPhone'];
    $serverId=$data['serverId'];
    $serviceId=$data['serviceId'];
    $serviceOrderPetInfo = json_encode($data['serviceOrderPetInfo'],JSON_UNESCAPED_UNICODE);
    $serviceOrderPetCard = json_encode($data['serviceOrderPetCard'],JSON_UNESCAPED_UNICODE);
    $servicePetNumber = $data['servicePetNumber'];
    //删除宠物信息
    $serviceInfo = $data['serviceInfo'];
    // unset($serviceInfo['petList']);
    $serviceInfo = json_encode($serviceInfo,JSON_UNESCAPED_UNICODE);
    // 自己生成serviceOrderDays
    // $serviceOrderDays = json_encode($data['serviceOrderDays'],JSON_UNESCAPED_UNICODE);
    $orderStartDate=$data['orderStartDate'];
    $orderEndDate=$data['orderEndDate'];
    $serviceOrderExtra = json_encode($data['serviceOrderExtra'],JSON_UNESCAPED_UNICODE);
    $serviceComment=$data['serviceComment'];
    $serviceOrderRentPrice=$data['serviceOrderRentPrice'];
    $serviceOrderExtraPrice=$data['serviceOrderExtraPrice'];
    $serviceOrderTaxPrice=$data['serviceOrderTaxPrice'];
    $serviceOrderCouponPrice=$data['serviceOrderCouponPrice'];
    $clientChargeFee=$data['clientChargeFee'];
    $serverChargeFee=$data['serverChargeFee'];
    $serviceOrderTotalPrice=$data['serviceOrderTotalPrice'];
    $serviceOrderTotalPyament=$data['serviceOrderTotalPyament'];

    //判断有没有重复日期
    $list = array();
    $stmt = $pdo->prepare("SELECT * From `serviceOrderTable` WHERE `serviceId` = '$serviceId'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row["serviceOrderDays"] = json_decode($row["serviceOrderDays"], true);
        foreach ($row["serviceOrderDays"] as $key => $value) {
          $list[$value] = isset($list[$value])?(int)$list[$value] + (int)$row["servicePetNumber"]:(int)$list[$value];
        }
      }
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }

    // $serviceInfo =array();
    //获取服务信息
    $stmt = $pdo->prepare("SELECT `serviceTable`.*,`categoryTable`.`categoryName` From `serviceTable`
                          LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `serviceTable`.`serviceCategory`
                          WHERE `serviceId` = '$serviceId' AND `serviceState` = '1'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        //保存服务信息 删除不需要部分
        // $serviceInfo = $row;
        // unset($serviceInfo['serviceId']);
        // unset($serviceInfo['userId']);
        // unset($serviceInfo['serviceCategory']);
        // unset($serviceInfo['serviceSubCategory']);
        // unset($serviceInfo['serviceSubCategory']);
        $row["categoryName"] = json_decode($row["categoryName"], true); 
        $serviceStock = $row["serviceStock"];
        $row["serviceBlockDate"] = json_decode($row["serviceBlockDate"], true);
        foreach ($row["serviceBlockDate"] as $key => $value) {
          $list[$value] = 'block';
        }
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }
    
    if($serviceStock == null){
      echo json_encode(["message"=>"no service"]);
      exit();
    }

    //判断服务是否还有空位
    $serviceOrderDays = periodDate($orderStartDate,$orderEndDate);
    foreach ($serviceOrderDays as $key => $value) {
      $totalnumber = (int)$list[$value] + (int)$servicePetNumber;
      if((isset($list[$value]) && $list[$value] === 'block') || $totalnumber > $serviceStock){
        echo json_encode(["message"=>"date selected"]);
        exit(); 
      }
    }

    $serviceOrderDays = json_encode($serviceOrderDays,JSON_UNESCAPED_UNICODE);

    //清除5分钟之前的数据
    $todaymin5 = date('Y-m-d H:i:s', strtotime("-10 minute"));
    $stmt = $pdo->prepare("DELETE FROM `serviceOrderTable` WHERE `creatTime` < '$todaymin5' AND `orderState` = '0'");
    $stmt->execute();

    //serviceType 0:寄样;1:日托;2:遛狗  
    $stmt = $pdo->prepare("INSERT INTO `serviceOrderTable`(`userId`,`userPhone`, `serverId`, `serviceId`, `serviceOrderPetInfo`, `serviceOrderPetCard`, `servicePetNumber`,
                             `serviceInfo`, `serviceOrderDays`,`orderStartDate`,`orderEndDate`,`serviceOrderExtra`, `serviceComment`, `serviceOrderRentPrice`, 
                            `serviceOrderExtraPrice`, `serviceOrderTaxPrice`, `serviceOrderCouponPrice`, `clientChargeFee`, `serverChargeFee`, `serviceOrderTotalPrice`, `serviceOrderTotalPyament`)
                           VALUES ('$userId','$userPhone','$serverId','$serviceId','$serviceOrderPetInfo','$serviceOrderPetCard','$servicePetNumber','$serviceInfo','$serviceOrderDays','$orderStartDate','$orderEndDate','$serviceOrderExtra','$serviceComment','$serviceOrderRentPrice','$serviceOrderExtraPrice','$serviceOrderTaxPrice',
                           '$serviceOrderCouponPrice','$clientChargeFee','$serverChargeFee','$serviceOrderTotalPrice','$serviceOrderTotalPyament')");
    $stmt->execute();
    if($stmt != null){
        $serviceOrderId = $pdo->lastInsertId();
        if($serviceOrderId != 0){
          $serviceOrderNo = "PP".date("ymdHis").$serviceOrderId;
          $stmt = $pdo->prepare("UPDATE `serviceOrderTable` SET `serviceOrderNo` = '$serviceOrderNo'
                                WHERE `serviceOrderId` = '$serviceOrderId'");
          $stmt->execute();
          $data['serviceOrderNo'] = $serviceOrderNo;
          $data['serviceOrderId'] = $serviceOrderId;
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
