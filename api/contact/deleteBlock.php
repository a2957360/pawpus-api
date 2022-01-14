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

    //判断用户是否存在
    //获取用户的联系人
    $stmt = $pdo->prepare("SELECT `userTable`.*,`contactTable`.`blockList` From `userTable`
                           LEFT JOIN `contactTable` ON `contactTable`.`userId` = `userTable`.`userId`
                           WHERE `userTable`.`userId`='$userId'");
    $stmt->execute();
    if($stmt != null){
      $row=$stmt->fetch(PDO::FETCH_ASSOC);
      $blockList = json_decode($row["blockList"], true); 
      if(!isset($row['userId'])){
        echo json_encode(["message"=>"fail"]);
        exit();
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    //判断block别人的用户是否存在
    //获取block别人的用户的联系人
    $stmt = $pdo->prepare("SELECT `userTable`.*,`contactTable`.`contactList` From `userTable`
                           LEFT JOIN `contactTable` ON `contactTable`.`userId` = `userTable`.`userId`
                           WHERE `userTable`.`userId`='$contactUserId'");
    $stmt->execute();
    if($stmt != null){
      $row=$stmt->fetch(PDO::FETCH_ASSOC);
      $contactList = json_decode($row["contactList"], true); 
      //没有用户或者联系人列表里面没有需要block的用户
      if(!isset($row['userId']) || !isset($contactList[$userId])){
        echo json_encode(["message"=>"fail"]);
        exit();
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    //删除
    $blockList = array_splice($blockList,array_search($contactUserId, $blockList)-1,1);
    $blockList = json_encode($blockList,JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare("UPDATE `contactTable` SET `blockList` = '$blockList' WHERE `userId`='$userId'");
    $stmt->execute();
    if($stmt != null){
        $petId = $pdo->lastInsertId();
        $rowcount = $stmt->rowCount();
        if($petId != 0 || $rowcount != 0){
          $contactList[$userId]['isBlock'] = '0';
          $stmt = $pdo->prepare("UPDATE `contactTable` SET `contactList` = '$contactList' WHERE `contactUserId`='$contactUserId'");
          $stmt->execute();
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
