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
    $serviceImage = json_encode($data['serviceImage'],JSON_UNESCAPED_UNICODE);
    $serviceName=$data['serviceName'];
    $serviceCategory=$data['serviceCategory'];
  	$serviceSubCategory = json_encode($data['serviceSubCategory'],JSON_UNESCAPED_UNICODE);
    $serviceDescription=$data['serviceDescription'];
    $serviceRequirement=$data['serviceRequirement'];
	  $serviceExtra = json_encode($data['serviceExtra'],JSON_UNESCAPED_UNICODE);
    $serviceFacility = json_encode($data['serviceFacility'],JSON_UNESCAPED_UNICODE);
    $servicePrice=$data['servicePrice'];
    $serviceTax=$data['serviceTax'];
    $serviceStock=$data['serviceStock'];
	  $serviceBlockDate = json_encode($data['serviceBlockDate'],JSON_UNESCAPED_UNICODE);
    $serviceAddress=$data['serviceAddress'];
    $serviceCity=$data['serviceCity'];
    $serviceProvince=$data['serviceProvince'];
    $servicePostal=$data['servicePostal'];
    $serviceHourseType=$data['serviceHourseType'];
    $servicePhone=$data['servicePhone'];
  	$servicePet = json_encode($data['servicePet'],JSON_UNESCAPED_UNICODE);
    $serviceType=$data['serviceType'];
    //-2 审核未通过 -1:草稿 0:未审核；1上架；2下架  
    $serviceState=-1;
    $serviceLanguage = json_encode($data['serviceLanguage'],JSON_UNESCAPED_UNICODE);
    $endDate=date('Y-m-t',strtotime($data['endDate']));
    $checkinTime=$data['checkinTime'];
    $checkoutTime=$data['checkoutTime'];

    //serviceType 0:寄样;1:日托;2:遛狗  
    $stmt = $pdo->prepare("INSERT INTO `serviceTable`(`userId`, `serviceImage`, `serviceName`, `serviceCategory`, `serviceSubCategory`,`serviceDescription`,`serviceRequirement`, `serviceExtra`, `serviceFacility`, `servicePrice`,`serviceTax`, `serviceStock`, `serviceBlockDate`, `serviceAddress`, `serviceCity`, `serviceProvince`,`servicePostal`,`serviceHourseType`, `servicePhone`, `servicePet`, `serviceType`, `serviceState`,`serviceLanguage`, `endDate`, `checkinTime`, `checkoutTime`)
                           VALUES ('$userId','$serviceImage','$serviceName','$serviceCategory','$serviceSubCategory','$serviceDescription','$serviceRequirement','$serviceExtra','$serviceFacility','$servicePrice','$serviceTax','$serviceStock','$serviceBlockDate','$serviceAddress','$serviceCity','$serviceProvince','$servicePostal','$serviceHourseType','$servicePhone','$servicePet','$serviceType','$serviceState','$serviceLanguage','$endDate','$checkinTime','$checkoutTime')");
    $stmt->execute();
    if($stmt != null){
        $serviceId = $pdo->lastInsertId();
        if($serviceId != 0){
          echo json_encode(["message"=>"success","data"=>["serviceId"=>$serviceId]]);
          exit();
        }
        echo json_encode(["message"=>"fail"]);
        exit();
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }
  }
