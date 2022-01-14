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
  $momentId=$data['momentId'];
  $userId=$data['userId'];

  //获取点赞信息
  $momentLike = array();
  $stmt = $pdo->prepare("SELECT `momentLike` From `momentTable` WHERE `momentId`='$momentId'");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $momentLike = json_decode($row["momentLike"], true); 
      $momentLike = $momentLike == null?[]:$momentLike;
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }
  //判断是否已经点赞
  $key = array_search((string)$userId,$momentLike);
  if ($key !== false) {
    unset($momentLike[$key]);
    $momentLike = array_values($momentLike);
    $momentLike = json_encode($momentLike,JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare("UPDATE `momentTable` SET `momentLike`='$momentLike'WHERE `momentId`='$momentId'");
    $stmt->execute();
  }else{
    $momentLike[] = $userId;
    $momentLike = json_encode($momentLike,JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare("UPDATE `momentTable` SET `momentLike`='$momentLike'WHERE `momentId`='$momentId'");
    $stmt->execute();
  }
  
  $momentLike = json_decode($momentLike, true); 
  echo json_encode(["message"=>"success","data"=>$momentLike]);
  exit();
}
