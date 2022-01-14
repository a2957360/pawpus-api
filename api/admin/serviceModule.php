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
		//服务状态中文
		$serviceState = SERVICE_STATE;
		$serviceId=$data['serviceId'];
		$userId=$data['userId'];
		$searchSql .= isset($serviceId)?" AND `serviceTable`.`serviceId`=".$serviceId:"";
		$searchSql .= isset($userId)?" AND `serviceTable`.`userId`=".$userId:"";


		$list = array();
		$stmt = $pdo->prepare("SELECT `serviceTable`.*,`categoryTable`.`categoryName`,`userTable`.`userName` From `serviceTable` 
		                      LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `serviceTable`.`serviceCategory`
                          LEFT JOIN `userTable` ON `userTable`.`userId` = `serviceTable`.`userId`
		                      WHERE `serviceState` != '-1' ".$searchSql);
		$stmt->execute();
		if($stmt != null){
		while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["categoryName"] = json_decode($row["categoryName"], true); 
		  $row["serviceSubCategory"] = json_decode($row["serviceSubCategory"], true);
		  $row["serviceExtra"] = json_decode($row["serviceExtra"], true);
      $row["serviceFacility"] = json_decode($row["serviceFacility"], true);
		  $row["serviceImage"] = json_decode($row["serviceImage"], true);
		  $row["serviceStateDisplay"] = $serviceState[$row["serviceState"]];
      $row["serviceLanguage"] = json_decode($row["serviceLanguage"], true);
      $row["serviceLanguage"] = array_map('intval', $row["serviceLanguage"]);
		  $list[] = $row;
		}
		}else{
		  echo json_encode(["message"=>"database error"]);
		  exit();
		}

		echo json_encode(["message"=>"success","data"=>$list]);
		exit();
    }

    // //删除
    // if(isset($data['isDelete']) && isset($data['id'])){
    //   $id=$data['id'];
    //   foreach ($id as $key => $value) {
    //     $stmt = $pdo->prepare("DELETE FROM `homePage` WHERE `id` = '$value'");
    //     $stmt->execute();
    //   }
    //   echo json_encode(["message"=>"DELETE FROM `homePage` WHERE `id` = '$value'"]);
    //   exit();
    // }

    //审核/黑名单/恢复
    if(isset($data['isChangeState']) && isset($data['isChangeState'])){
      $userId=$data['userId'];
      $userState=$data['userState']; 
      $stmt = $pdo->prepare("UPDATE `userTable` SET `userState` = '$userState' WHERE `userId` = '$userId'");
      $stmt->execute();
      if($stmt != null){

      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      }
      echo json_encode(["message"=>"success"]);
      exit();
    }


    //添加/修改
    $date = date('YmdHis');

    $serviceId=$data['serviceId'];
    $userId=$data['userId'];
    $serviceImage = json_encode($data['serviceImage'],JSON_UNESCAPED_UNICODE);
    $serviceName=$data['serviceName'];
    $serviceCategory=$data['serviceCategory'];
    $serviceSubCategory = json_encode($data['serviceSubCategory'],JSON_UNESCAPED_UNICODE);
    $serviceDescription=$data['serviceDescription'];

    $serviceRequirement=$data['serviceRequirement'];

    $serviceExtra = json_encode($data['serviceExtra'],JSON_UNESCAPED_UNICODE);

    $serviceFacility = json_encode($data['serviceFacility'],JSON_UNESCAPED_UNICODE);

    $servicePrice=$data['servicePrice'];
    $serviceStock=$data['serviceStock'];
    $serviceBlockDate = json_encode($data['serviceBlockDate'],JSON_UNESCAPED_UNICODE);
    $serviceAddress=$data['serviceAddress'];
    $serviceCity=$data['serviceCity'];
    $serviceProvince=$data['serviceProvince'];

    $servicePostal=$data['servicePostal'];
    $serviceHourseType=$data['serviceHourseType'];

    $servicePhone=$data['servicePhone'];
    $serviceType=0;
    //-2 审核未通过 -1:草稿 0:未审核；1上架；2下架  
    $serviceState=$data['serviceState'];

    $serviceLanguage = json_encode($data['serviceLanguage'],JSON_UNESCAPED_UNICODE);
    
    $endDate=$data['endDate'];
    $checkinTime=$data['checkinTime'];
    $checkoutTime=$data['checkoutTime'];


    if(isset($serviceId) && $serviceId !== ""){
      //有subtitle
      // $stmt = $pdo->prepare("UPDATE `subPage` SET `title` = '$title' ,`subTitle` = '$subTitle' , `topTitle` = '$topTitle' , `image` = '$image' ,
      //                       `button` = '$button' , `buttonlink` = '$buttonlink', `leftImage` = '$leftImage', `rightImage` = '$rightImage', `content` = '$content', `map` = '$map', `address` = '$address' WHERE `id` = '$id'");
      $stmt = $pdo->prepare("UPDATE `serviceTable` SET `serviceImage` = '$serviceImage' , `serviceName` = '$serviceName' ,`serviceCategory` = '$serviceCategory' ,
                            `serviceSubCategory` = '$serviceSubCategory', `serviceDescription` = '$serviceDescription',`serviceRequirement` = '$serviceRequirement',
                             `serviceExtra` = '$serviceExtra', `serviceFacility` = '$serviceFacility', `servicePrice` = '$servicePrice', 
                            `serviceStock` = '$serviceStock', `serviceAddress` = '$serviceAddress', `serviceCity` = '$serviceCity', 
                            `serviceProvince` = '$serviceProvince',`servicePostal` = '$servicePostal',`serviceHourseType` = '$serviceHourseType', `servicePhone` = '$servicePhone', `serviceType` = '$serviceType', `serviceState` = '$serviceState' , `serviceLanguage` = '$serviceLanguage', `endDate` = '$endDate', 
                            `checkinTime` = '$checkinTime', `checkoutTime` = '$checkoutTime' 
                            WHERE `serviceId` = '$serviceId'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
      }
      exit();
    }

    //添加
    // $stmt = $pdo->prepare("INSERT INTO `serviceTable`(`userId`, `serviceImage`, `serviceName`, `serviceCategory`, `serviceSubCategory`,`serviceDescription`,`serviceRequirement`, 
    //                       `serviceExtra`,`serviceFacility`, `servicePrice`, 
    //                       `serviceStock`, `serviceBlockDate`, `serviceAddress`, `serviceCity`, `serviceProvince`,`servicePostal`,`serviceHourseType`, `servicePhone`, `serviceType`,
    //                        `serviceState`)
    //                        VALUES ('$userId','$serviceImage','$serviceName','$serviceCategory','$serviceSubCategory','$serviceDescription','$serviceExtra','$servicePrice','$serviceStock',
    //                        '$serviceBlockDate','$serviceAddress','$serviceCity','$serviceProvince','$servicePhone','$serviceType','$serviceState')");
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

