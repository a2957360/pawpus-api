<?php
include("../../include/sql.php");
include("../../include/conf/config.php");

require __DIR__ . '/../twilio-php/src/Twilio/autoload.php';
use Twilio\Rest\Client;
// Include the bundled autoload from the Twilio PHP Helper Library
http_response_code(200);
header('content-type:application/json;charset=utf8');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");


$serverPhone = $_GET['userPhone'];
$language = $_GET['language'];

$serverPhone=str_replace("-", " ", $serverPhone);

//language setting
$msgList = MESSAGE_LIST;
$msg = $msgList[$language];
$checknumber = mt_rand(100000,999999);

$stmt = $pdo->prepare("SELECT count(*) as `num` From `serverTable` WHERE `serverPhone` = '$serverPhone'");
$stmt->execute();
$order = 0;
if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        if($row['num'] > 0){
            echo json_encode(["message"=>"exist phone"]);
            exit();
        }
    }
}else{
    echo json_encode(["message"=>"database error"]);
    exit();
}

//判断是否短时间发送
// $stmt = $pdo->prepare("SELECT `createTime` From `textTable` WHERE `userPhone` = '$serverPhone' AND `textType` = '1'");
// $stmt->execute();
// $order = 0;
// if($stmt != null){
//     $row=$stmt->fetch(PDO::FETCH_ASSOC);
//     if($row['createTime'] != null){
//         $createTime = $row['createTime'];
//         $one = strtotime($createTime);//开始时间 时间戳
//         $tow = time();//结束时间 时间戳
//         $cle = $tow - $one;
//         $cle = round($cle/60,2);
//         if($cle < 2){
//             echo json_encode(["message"=>"wait more time"]);
//             exit();
//         }
//     }
// }else{
//     echo json_encode(["message"=>"database error"]);
//     exit();
// }

$stmt = $pdo->prepare("UPDATE `textTable` SET `code` = '$checknumber' WHERE `userPhone` = '$serverPhone' AND `textType` = '1'");
$stmt->execute();
if($stmt->rowCount() == 0){
    //textType '0：用户；1：服务者'
    $stmt = $pdo->prepare("INSERT INTO `textTable`(`userPhone`,`code`,`textType`) 
                        VALUES ('$serverPhone','$checknumber','1')");
    $stmt->execute();
    $emailId = $pdo->lastInsertId();
}

$account_sid = TWILLO_SID;
$auth_token = TWILLO_TOKEN;
$twilio_number = TWILLO_NUMBER;

$client = new Client($account_sid, $auth_token);
try {
    $client->messages->create(
        // Where to send a text message (your cell phone?)
        $serverPhone,
        array(
            'from' => $twilio_number,
            'body' => $msg.$checknumber
        )
    );
} catch (Exception $e) {
    echo $e;
    echo json_encode(["message"=>"twilio error"]);
    exit();
}
$data = ["verificationCode"=>$checknumber];
echo json_encode(["message"=>"success","data"=>$data]);
?>

