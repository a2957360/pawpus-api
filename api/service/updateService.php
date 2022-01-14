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
    // $serviceType=$data['serviceType'];
    //-2 审核未通过 -1:草稿 0:未审核；1上架；2下架  
    $serviceState=-1;
    $serviceLanguage = json_encode($data['serviceLanguage'],JSON_UNESCAPED_UNICODE);
    $endDate=date('Y-m-t',strtotime($data['endDate']));
    $checkinTime=$data['checkinTime'];
    $checkoutTime=$data['checkoutTime'];


    //serviceType 0:寄样;1:日托;2:遛狗  
    $stmt = $pdo->prepare("UPDATE `serviceTable` SET `serviceImage`='$serviceImage',`serviceName`='$serviceName',`serviceCategory`='$serviceCategory',
                          `serviceSubCategory`='$serviceSubCategory',`serviceDescription`='$serviceDescription',`serviceRequirement`='$serviceRequirement',`serviceExtra`='$serviceExtra',
                          `serviceFacility`='$serviceFacility',`servicePrice`='$servicePrice',`serviceTax`='$serviceTax',`serviceStock`='$serviceStock',`serviceBlockDate`='$serviceBlockDate',
                          `serviceAddress`='$serviceAddress',`serviceCity`='$serviceCity',`serviceProvince`='$serviceProvince',`servicePostal`='$servicePostal',
                          `serviceHourseType`='$serviceHourseType',`servicePhone`='$servicePhone',`servicePet`='$servicePet',`serviceState`='-1',`serviceLanguage`='$serviceLanguage',
                          `endDate`='$endDate',`checkinTime`='$checkinTime',`checkoutTime`='$checkoutTime'
                           WHERE `serviceId`='$serviceId' AND `userId`='$userId'");
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
