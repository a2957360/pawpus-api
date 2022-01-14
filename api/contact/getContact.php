<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");
  require_once "../sendemail.php";

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  $data = file_get_contents('php://input');
  $data = json_decode($data,true);

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userList=$data['userList'];

    $tmpcontactList =array(); 
    $contactString = implode(",", $userList);
    $stmt = $pdo->prepare("SELECT `userImage`,`userName`,`userId` From `userTable` WHERE `userId` IN ($contactString)");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $tmpcontactList[] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    echo json_encode(["message"=>"success","data"=>$tmpcontactList]);
    exit();


  }
