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
    $contactUserId=$data['contactUserId'];
    $serviceId=$data['serviceId'];

    //判断用户是否存在
    //获取用户的联系人
    $stmt = $pdo->prepare("SELECT `userTable`.*,`contactTable`.`contactList` From `userTable`
                           LEFT JOIN `contactTable` ON `contactTable`.`userId` = `userTable`.`userId`
                           WHERE `userTable`.`userId`='$userId'");
    $stmt->execute();
    if($stmt != null){
      $row=$stmt->fetch(PDO::FETCH_ASSOC);
      $contactList = json_decode($row["contactList"], true); 
      if(!isset($row['userId'])){
        echo json_encode(["message"=>"fail"]);
        exit();
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    $sql = "";
    if(isset($contactList) && count($contactList)>0){
        //修改用户的联系人array
        $contactList[$contactUserId] = ["userId"=>$contactUserId,"serviceId"=>$serviceId,"isBlock"=>0];
        $contactList = json_encode($contactList,JSON_UNESCAPED_UNICODE);
        $sql = "UPDATE `contactTable` SET `contactList` = '$contactList' WHERE `userId`='$userId'";
    }else{
        //修改用户的联系人array
        $contactList[$contactUserId] = ["userId"=>$contactUserId,"serviceId"=>$serviceId,"isBlock"=>0];
        $contactList = json_encode($contactList,JSON_UNESCAPED_UNICODE);
        $sql = "INSERT INTO `contactTable`(`userId`, `contactList`)VALUES ('$userId','$contactList')";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    if($stmt != null){
        $petId = $pdo->lastInsertId();
        $rowcount = $stmt->rowCount();
        if($petId != 0 || $rowcount != 0){
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
