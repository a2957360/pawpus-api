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
    $languageList = LANGUAGE_LIST;

    //查询
    if(isset($data['isGet']) && $data['isGet'] !== ""){
  		$list = array();
  		$stmt = $pdo->prepare("SELECT * From `couponTable` WHERE 1 ".$searchSql);
  		$stmt->execute();
  		if($stmt != null){
    		while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row["couponTypeText"] = $row["couponType"] == 0 ?"商城":"服务";
          $row["discountTypeText"] = $row["discountType"] == 0 ?"满减":"满折";
    		  $list[] = $row;
    		}
  		}else{
  		  echo json_encode(["message"=>"database error"]);
  		  exit();
  		}

  		echo json_encode(["message"=>"success","data"=>$list]);
  		exit();
    }

    //删除
    if(isset($data['isDelete']) && isset($data['couponId'])){
      $couponId=$data['couponId'];
      foreach ($couponId as $key => $value) {
        $stmt = $pdo->prepare("DELETE FROM `couponTable` WHERE `couponId` = '$value'");
        $stmt->execute();
      }
      echo json_encode(["message"=>"success"]);
      exit();
    }


    //添加/修改
    $date = date('YmdHis');

    $couponId=$data['couponId'];
    //0:商城;1:服务;
    $couponType =$data['couponType'];
    //0:满减;1:满折
    $couponCode=$data['couponCode'];
    $couponrequirePrice=$data['couponrequirePrice'];
    $couponValue=$data['couponValue'];
    $avaiableTimes=$data['avaiableTimes'];
    $expireDate = $data['expireDate'];

    if(isset($couponId) && $couponId !== ""){
      $stmt = $pdo->prepare("UPDATE `couponTable` SET `couponType` = '$couponType' ,`couponCode` = '$couponCode' ,`couponrequirePrice` = '$couponrequirePrice' ,
                            `couponValue` = '$couponValue', `avaiableTimes` = '$avaiableTimes', `expireDate` = '$expireDate'
                            WHERE `couponId` = '$couponId'");
      $stmt->execute();
      if($stmt != null && $stmt->rowCount() > 0){
        echo json_encode(["message"=>"success"]);
      }else{
        echo json_encode(["message"=>"fail"]);
      }
      exit();
    }

    //添加
    $stmt = $pdo->prepare("INSERT INTO `couponTable`(`couponType`, `couponCode`, `couponrequirePrice`, `couponValue`, `avaiableTimes`,`expireDate`)
                           VALUES ('$couponType','$couponCode','$couponrequirePrice','$couponValue','$avaiableTimes','$expireDate')");
    $stmt->execute();
    if($stmt != null){
        $serviceId = $pdo->lastInsertId();
        if($serviceId != 0){
          echo json_encode(["message"=>"INSERT INTO `couponTable`(`couponType`, `couponCode`, `couponrequirePrice`, `couponValue`, `avaiableTimes`,`expireDate`)
                           VALUES ('$couponType','$couponCode','$couponrequirePrice','$couponValue','$avaiableTimes','$expireDate')"]);
          exit();
        }
        echo json_encode(["message"=>"fail"]);
        exit();
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }
}

