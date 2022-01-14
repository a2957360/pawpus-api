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
    $petName=$data['petName'];
    $petImage = json_encode($data['petImage'],JSON_UNESCAPED_UNICODE);
    $petType=json_encode($data['petType'],JSON_UNESCAPED_UNICODE);
    $petVariety=$data['petVariety'];
    $petAge=$data['petAge'];
    $petGender=$data['petGender'];
    $isOperated=$data['isOperated'];
    $petWeight=$data['petWeight'];
    $petDescription=$data['petDescription'];
    $petPortfolio=json_encode($data['petPortfolio'],JSON_UNESCAPED_UNICODE);

    $petPercentage = 0;
    foreach ($data as $key => $value) {
      $petPercentage += isset($data[$key])?1:0;
    }
    $petPercentage = round($petPercentage/count($data),2) * 100;


    $stmt = $pdo->prepare("INSERT INTO `petTable`(`userId`, `petName`, `petImage`, `petType`, `petVariety`,`petAge`,`petGender`,`isOperated`,`petWeight`,`petDescription`, `petPortfolio`, `petPercentage`)
                           VALUES ('$userId','$petName','$petImage','$petType','$petVariety','$petAge','$petGender','$isOperated','$petWeight','$petDescription','$petPortfolio','$petPercentage')");
    $stmt->execute();
    if($stmt != null){
        $petId = $pdo->lastInsertId();
        if($petId != 0){
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
