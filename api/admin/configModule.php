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
    //获取当前语言
    $language=isset($data['language'])?$data['language']:$_POST['language'];

    //查询
    if(isset($data['isGet']) && $data['isGet'] !== ""){
      // $categoryType=$data['categoryType'];
      $configType=$data['configType'];
      // $searchSql .= isset($categoryType)?" AND `categoryType`='$categoryType'":"";
      $searchSql .= isset($configType)?" AND `configType`='$configType'":"";
      $list = array();
      $stmt = $pdo->prepare("SELECT * From `configTable` WHERE 1 ".$searchSql);
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $list[] = $row;
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }

      echo json_encode(["message"=>"success","data"=>$list]);
      exit();
    }

    //添加/修改
    $date = date('YmdHis');

    $configId=$data['configId'];
    $configValue=$data['configValue'];

    if(isset($configId) && $configId !== ""){
      $stmt = $pdo->prepare("UPDATE `configTable` SET `configValue` = '$configValue' WHERE `configId` = '$configId'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
      }
      exit();
    }

}

