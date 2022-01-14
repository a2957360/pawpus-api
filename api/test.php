<?php
include("../include/jwt.php");

http_response_code(200);
header('content-type:application/json;charset=utf8');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers,Authorization, X-Requested-With");

  $dsn = "mysql:host=zojie.net;dbname=zojie218_new";
  $sqlusername = 'u0ddv84qcbcud';
  $sqlpassword = 'Finestudio123@';
  $pdo = new PDO($dsn, $sqlusername, $sqlpassword);
  if(!$pdo){
      die("can't connect".mysql_error());//如果链接失败输出错误
  }

// $payload_test=array('iss'=>'admin',
//                     'iat'=>time(),
//                     'exp'=>time()+7200,
//                     // 'nbf'=>time(),
//                     'userId'=>'1',
//                     'sub'=>'www.admin.com',
//                     'jti'=>md5(uniqid('JWT').time())
//                     );
// $token_test=Jwt::getToken($payload_test);
// echo $token_test;
// $token = str_replace("Bearer ", "", $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
// $getPayload_test=Jwt::verifyToken($token,2);
// var_dump($getPayload_test);


//   include("../include/sql.php");
//   include("../include/conf/config.php");

//   http_response_code(200);
//   header('content-type:application/json;charset=utf8');
//   header('Access-Control-Allow-Origin: *');
//   header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
//   header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
  
//   $data = file_get_contents('php://input');
//   $data = json_decode($data,true);

// if ($_SERVER["REQUEST_METHOD"] == "POST") {

//   $input = $data['data'];
//   foreach ($input as $key => $value) {
//     $parentCategoryId=$value['parentCategoryId'];
//     $categoryName = json_encode($value['categoryName'],JSON_UNESCAPED_UNICODE);
//     $categoryImage=$value['categoryImage'];
//     $categoryType=$value['categoryType'];
//     //添加
//     $stmt = $pdo->prepare("INSERT INTO `categoryTable`(`parentCategoryId`,`categoryName`,`categoryImage`,`categoryType`) VALUES ('$parentCategoryId','$categoryName','$categoryImage','$categoryType')");
//     $stmt->execute();
//     if($stmt != null){
//       $categoryId = $pdo->lastInsertId();
//       $stmt = $pdo->prepare("UPDATE `categoryTable` SET `categoryOrder` = '$categoryId' WHERE `categoryId` = '$categoryId'");
//       $stmt->execute();
//     }
//   }
//   echo json_encode(["message"=>"success","data"=>$list]);
//   exit();
// }
