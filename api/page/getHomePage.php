<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  if ($_SERVER["REQUEST_METHOD"] == "GET") {

      $pageNumber="0";
      $componentId=$data['componentId'];
      $searchSql .= isset($componentId)?" AND `componentId`=".$componentId:"";

      $pageList = array();
      $stmt = $pdo->prepare("SELECT * From `pageLayoutTable` WHERE `pageNumber`='$pageNumber' AND `pageState` = '0' ".$searchSql);
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row["pageStateText"] = $row["pageState"]==0?"显示":"隐藏";
          // 0:图片；1：产品；2：服务
          switch ($row['componentType']) {
            case '1':
                $tmparray = array();
                $itemList = json_decode($row["componentContent"],true);
                $itemList = implode(",", $itemList);
                $substmt = $pdo->prepare("SELECT *,min(`itemOptionTable`.`itemOptionPrice`) AS `price`,IFNULL(min(`saleOption`.`itemOptionSalePrice`),0) AS `salePrice` From `itemTable`
                                          LEFT JOIN `itemOptionTable`  ON `itemOptionTable`.`itemId` = `itemTable`.`itemId` AND `itemOptionTable`.`itemOptionState` = '0'
                                          LEFT JOIN `itemOptionTable` `saleOption` ON `saleOption`.`itemId` = `itemTable`.`itemId` 
                                          AND `saleOption`.`itemOptionState` = '0' AND `saleOption`.`itemOptionSalePrice` > 0 
                                          WHERE `itemTable`.`itemId` IN ($itemList) GROUP BY `itemTable`.`itemId` order by field(`itemTable`.`itemId`,$itemList)");
                $substmt->execute();
                if($substmt != null){
                  while($subrow=$substmt->fetch(PDO::FETCH_ASSOC)){
                    $subrow["itemImage"] = json_decode($subrow["itemImage"], true);
                    $subrow["itemCategory"] = json_decode($subrow["itemCategory"], true);
                    $subrow["itemSubCategory"] = json_decode($subrow["itemSubCategory"], true);
                    $subrow["itemOption"] = json_decode($subrow["itemOption"], true);
                    $tmparray[] = $subrow;
                  }
                }
                // echo $row["componentTitle"].PHP_EOL;
                // var_dump($tmparray);
                $row["componentContent"] = $tmparray;
              break;
            case '2':
                $tmparray = array();
                $itemList = json_decode($row["componentContent"],true);
                $itemList = implode(",", $itemList);
                $substmt = $pdo->prepare("SELECT `serviceTable`.*,`categoryTable`.`categoryName`,`userTable`.`userName`,`userTable`.`userImage`,`userTable`.`serverLevel` From `serviceTable`
                                          LEFT JOIN `userTable` ON `userTable`.`userId` = `serviceTable`.`userId`
                                          LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `serviceTable`.`serviceCategory`
                                          WHERE `serviceTable`.`serviceId` IN ($itemList) order by field(`serviceTable`.`serviceId`,$itemList)");
                $substmt->execute();
                if($substmt != null){
                  while($subrow=$substmt->fetch(PDO::FETCH_ASSOC)){
                    $subrow["categoryName"] = json_decode($subrow["categoryName"], true); 
                    $subrow["serviceSubCategory"] = json_decode($subrow["serviceSubCategory"], true);
                    $subrow["serviceExtra"] = json_decode($subrow["serviceExtra"], true);
                    $subrow["serviceImage"] = json_decode($subrow["serviceImage"], true);
                    $tmparray[] = $subrow;
                  }
                }
                $row["componentContent"] = $tmparray;
              break;
            default:
              $row["componentContent"] = json_decode($row["componentContent"],true);
              break;
          }
          $pageList[] = $row;
        }
      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      }
      // $pageList = quick_sort($pageList);
      echo json_encode(["message"=>"success","data"=>$pageList]);
      exit();
  }
