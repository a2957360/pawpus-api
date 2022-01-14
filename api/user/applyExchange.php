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

    $userId=$data['userId'];
    $exchangePrice=$data['exchangePrice'];
    $exchangeEmail=$data['exchangeEmail'];
    $exchangePassword=$data['exchangePassword'];

    $stmt = $pdo->prepare("SELECT `userEmail` From `userTable` WHERE `userId`='$userId' AND `userState` != 0 AND `userBlock` != 1");
    $stmt->execute();
    if($stmt != null){
      $row=$stmt->fetch(PDO::FETCH_ASSOC);
      $userEmail = $row['userEmail'];
    }
    if(!isset($userEmail)){
      echo json_encode(["message"=>"fail"]);
      exit();
    }

    $stmt = $pdo->prepare("INSERT INTO `exchangeTable`(`userId`,`exchangePrice`,`exchangeEmail`,`exchangePassword`) VALUES ('$userId','$exchangePrice','$exchangeEmail','$exchangePassword')");
    $stmt->execute();
    if($stmt != null){
        $serverId = $pdo->lastInsertId();
        if($serverId != 0){
            $stmt = $pdo->prepare("UPDATE `userTable` SET `serverPoints` = `serverPoints` - '$exchangePrice' WHERE `userId` = '$userId'");
            $stmt->execute();
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
