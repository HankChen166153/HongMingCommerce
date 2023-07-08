<?php

namespace App\Controllers;

use \Core\View;
use \Core\JWTCodec;
use \App\Models\User;
use App\Models\Item;
use App\Models\Order;
use App\Util;;

use Core\Auth;
use Core\Database;
use Exception;
use PDO;
use Symfony\Component\HttpFoundation\Request;

/**
 * Posts controller
 *
 * PHP version 5.4
 */
class ShoppingCartController extends \Core\Controller
{

    public function itemList()
    {
        $success = false;
        $errorCode = 0;
        $message = "";
        $data = null;

        // ------實作分頁------
        $input = (array) json_decode(file_get_contents("php://input"), true);

        // $item_id = $input["item_id"];

        // 顯示商品
        $item = new Item();
        $item = $item->getItemlist($message);

        $data = $item;
        // ------實作分頁------
        
        $success = true;
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: *");
        header("Access-Control-Allow-Headers: Origin, Methods, Content-Type, Authorization");

        echo Util::response_data($success, $errorCode, $message, $data);
        exit;
    }

    public function fetchItem(Request $request)
    {
        $success = false;
        $errorCode = 0;
        $message = "";
        $data = null;

        $userId = $request->request->get('user_id');
        // echo "userId: " . $userId . "\n";

        $input = (array) json_decode(file_get_contents("php://input"), true);


        // 檢查是否帶參數
        if (!isset($input["item_id"])) {
            header("Content-Type: application/json");
            $message = "param require: item_id";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }

        // 檢查是否為整數
        if (!is_int($input["item_id"])) {
            header("Content-Type: application/json");
            $message = "type error";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }
        $item_id = $input["item_id"];

        // 查詢商品
        $item = new Item();
        $item = $item->getItemById($item_id);

        // 檢查是否存在
        //empty($item) isset is_null
        if (empty($item)) {
            header("Content-Type: application/json");
            $message = "item doesn't exist";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }
        $data = $item;

        $success = true;
        header("Content-Type: application/json");

        echo Util::response_data($success, $errorCode, $message, $data);
        exit;
    }

    public function userOrder()
    {
        $success = false;
        $errorCode = 0;
        $message = "";
        $data = null;

        $input = (array) json_decode(file_get_contents("php://input"), true);

        $user_id = $input["user_id"];
        $order_id = $input["order_id"];
        // 查詢會員訂單
        $item = new Item();
        $item = $item->getUserOrderById($user_id, $order_id);

        $data = $item;

        $success = true;
        header("Content-Type: application/json");

        echo Util::response_data($success, $errorCode, $message, $data);
        exit;
    }


    public function checkOut(Request $request)
    {
        $success = false;
        $errorCode = 0;
        $message = "";
        $data = null;
        

        $userId = $request->request->get('user_id');
        // echo "userId: " . $userId . "\n";
        
        // $userId = 1;
        // $input = (array) json_decode(file_get_contents("php://input"), true);
        $input = $request->toArray();
        
        // 1-1判斷屬性cart是否存在
        if (!isset($input["cart"])) {
            header("Content-Type: application/json");
            $message = "attribute cart does not exist";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }

        // 1-2檢查cart是否array
        if (!is_array($input["cart"])) {
            header("Content-Type: application/json");
            $message = "param require: array";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }
        //1-3判斷購物車為空的情形(空陣列)時要報錯
        if (count($input["cart"]) <= 0) {
            header("Content-Type: application/json");
            $message = "shoppingcart empty";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }
        /*
          [
          "1" => 2,
          "2" => 3
          ]
          
          array_key($cart) => [1 ,2]
         */

        /**
         * ["1" => ['price' => 500, 'count'=> 3]]
         */
        $cart = array();
        $currentItemsArr = array();
        $tradeLogArr = array();
        foreach ($input["cart"] as $key => $value) {
            $cart[$value["item_id"]] = $value['count_sum'];
        }

        try {
            $db = Database::getDB();
            $db->setAttribute(PDO::ATTR_AUTOCOMMIT, 0); // 設置為手動提交模式
            $db->beginTransaction();

        //2-1.sql從items table撈出資料(where item_id in...)(向資料庫查詢)
        $currentItems = new Item();
        $currentItems = $currentItems->getItemsById(array_keys($cart));

        // $data = $currentItems;

        // 2-2.從table items取出的資料，用foreach來組裝key value  item_id當成key
        foreach ($currentItems as $key => $value) {
            $currentItemsArr[$value['item_id']] = [
                'price' => $value['price'],
                'count' => $value['count']
            ];
        }
        // $data=$currentItemsArr;
        $afterItemsArr = array();
        $totalPrice = 0;

        // 3-1.從cart用foreach迴圈撈出做到:檢查item_id是否存在
        foreach ($input['cart'] as $key => $value) {
            if (!array_key_exists($value['item_id'], $currentItemsArr)) {
                header("Content-Type: application/json");
                $message = "item_id: " . $value['item_id'] . " doesn't exist";
                $errorCode = 999;
                echo Util::response_data($success, $errorCode, $message, $data);
                exit;
            }
            // 3-2.判斷商品數量是否足夠(現有商品數 < 買家購買數)
            // echo json_encode($currentItemsArr[$value['item_id']]['count']),"\n";
            if ($currentItemsArr[$value['item_id']]['count'] < $value['count_sum']) {
                header("Content-Type: application/json");
                $message = "item_id: " . $value['item_id'] . " stuff not enough";
                $errorCode = 999;
                echo Util::response_data($success, $errorCode, $message, $data);
                exit;
            }
            // 3-3.計算該商品總金額(商品單價*買家購買數)
            $price_sum = $currentItemsArr[$value['item_id']]['price'] * $value['count_sum'];
            
            // 3-4.用變數抓原本商品數，扣掉購買商品數，update回去
            $afterItemsNum = $currentItemsArr[$value['item_id']]['count'] - $value['count_sum'];

            $tmpItemArr = array(
                "item_id" => $value['item_id'],
                "count" => $afterItemsNum
            );
            
            // 利用迴圈計算總金額 totalPrice
            $totalPrice = $totalPrice + $price_sum;
            // $totalPrice += $price_sum;

            /* 4-1.update商品數量塞回去(註:商城商品數和用戶購買數不要搞混)
            利用迴圈與 array_push() 把 tmpItemArr 塞進 afterItemsArr */
            array_push($afterItemsArr, $tmpItemArr);
        }

        //4-2.判斷剩餘金額足不足夠
        $user = new User();
        $user = $user->getUserById($userId);
        if ($totalPrice > $user["points"]) {
            header("Content-Type: application/json");
            $message = "userPoints not enough";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }

        //5-1.扣除消費點數
        $afterPoint = $user["points"] - $totalPrice;

        // 5-2.寫交易明細 update使用者(user)點數 itemtradeorder
        $userPoints = new Item();
        $userPoints = $userPoints->updatePointsById($userId, $afterPoint);

        // 5-3.update商品數量
        $updateItemsCount = new Item();
        $updateItemsCount = $updateItemsCount->updateItemsCountById($afterItemsArr);

        //6.產生交易紀錄 同時'insert'兩張表格item_trade_orders user_orders
        $insertTradeLog = new Item();
        $insertTradeLog = $insertTradeLog->insertTradeLogById($input['cart'], $currentItemsArr, $userId);
        
        // 交易提交
        $db->commit();
        $data = $insertTradeLog;
        } catch (\Exception $e) {
            $db->rollBack();
            header("Content-Type: application/json");
            $message = $e->getMessage();
            $errorCode = 999;

            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }
        

        $success = true;
        header("Content-Type: application/json");
        echo Util::response_data($success, $errorCode, $message, $data);
        exit;
    }

    public function tradeLog() {
        $success = false;
        $errorCode = 0;
        $message = "";
        $data = null;



        $success = true;
        header("Content-Type: application/json");
        echo Util::response_data($success, $errorCode, $message, $data);
        exit;
    }
}
