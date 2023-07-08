<?php

namespace App\Models;

use App\Util;
use PDO;

class Item extends \Core\Model
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getItemById(int $item_id)
    {
        try {
            // $db = static::getDB(); //連接db
            $db = $this->_db;//使用交易時要改寫成這樣
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    //設定 Error Mode
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $sql = "SELECT * FROM items WHERE item_id = :item_id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT); //將sql進行編譯，此寫法是防sql injection
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getItemlist()
    {
        try {
            // $db = static::getDB(); //連接db
            $db = $this->_db;
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    //設定 Error Mode
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $sql = "SELECT * FROM items";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC); //因為要抓不只一筆資料，用fetchall
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getUserOrderById(int $user_id, string $order_id)
    {
        try {
            // $db = static::getDB();
            $db = $this->_db;
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $sql = "SELECT 
                user_orders.user_id,
                user_orders.order_id,
                user_orders.status,
                item_trade_orders.count_sum,
                item_trade_orders.price_sum,
                items.item_id,
                items.item_name,
                items.price,
                items.img_item
            FROM user_orders
            left join item_trade_orders
            on user_orders.order_id = item_trade_orders.order_id
            left join items
            on item_trade_orders.item_id = items.item_id
            WHERE user_orders.order_id = 1";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':order_id', $order_id, PDO::PARAM_STR);
            $stmt->execute();

            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $orderList = array();

            $a = [1, 2, 3, 4, 5];
            // echo json_encode($a[1]);
            // return $orders;


            foreach ($orders as $key => $order) {
                // echo "bbb: " . json_encode($order['order_id']) ."\n";
                // echo "order: " . json_encode($order) . "\n";

                if (!array_key_exists($order['order_id'], $orderList)) {
                    $orderList[$order['order_id']]['order_id'] = $order['order_id'];
                    $orderList[$order['order_id']]['status'] = $order['status'];
                    $orderList[$order['order_id']]['items'] = [];
                }
                $data = [
                    'item_id' => $order['item_id'],
                    'item_name' => $order['item_name'],
                    'price' => $order['price'],
                    'img_item' => $order['img_item'],
                    'count_sum' => $order['count_sum'],
                    'price_sum' => $order['price_sum']
                ];
                array_push($orderList[$order['order_id']]['items'], $data);
                
                // ,'item_name','price','img_item','count_sum','price_sum'
                // echo "order: " . json_encode($order) . "\n";
                // echo "key: " . json_encode($key) . "\n";
            }
            $orderList = array_values($orderList);
            return $orderList;

        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getItemsById(array $data)
    {
        try {
            // $db = static::getDB();
            $db = $this->_db;
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $in = str_repeat('?,', count($data) - 1) . '?';
            // echo "in: " . $in . "\n";
            // echo gettype($in) . "\n";
            $sql = "SELECT * FROM `items` WHERE item_id IN (" . $in . ")";//多筆資料
            $stmt = $db->prepare($sql);
            $stmt->execute($data);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function updatePointsById(int $user_id, int $afterPoint)
    {
        try {
            // $db = static::getDB();
            $db = $this->_db;
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $sql="SELECT `points` FROM `users` WHERE user_id=:user_id FOR UPDATE";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            $sql = "UPDATE users
            SET points=:points
            WHERE user_id=:user_id
            ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':points', $afterPoint, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function updateItemsCountById(array $afterItemsArr)
    {
        try {

            foreach ($afterItemsArr as $key => $value) {
                $updateItemsCount = $value['count']; // 扣完的數量
                $item_id = $value['item_id'];
                // $db = static::getDB();
                $db = $this->_db;
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                $sql="SELECT `count` FROM `items` WHERE item_id=:item_id FOR UPDATE";//防數量不夠

                $stmt = $db->prepare($sql);
                $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                $stmt->execute();

                $sql = "UPDATE `items`
                SET count=:count
                WHERE item_id=:item_id
                ";

                $stmt = $db->prepare($sql);
                $stmt->bindValue(':count', $updateItemsCount, PDO::PARAM_INT);
                $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                $stmt->execute();
                
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return $result;
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function insertTradeLogById(array $cart, array $currentItemsArr, int $userId)
    {
        try {
            // $db = static::getDB();
            $db = $this->_db;
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $order_id = Util::generateID_V3(10);

            $sql = "INSERT INTO `user_orders` (`order_id`, `status`, `user_id`)
            VALUES(:order_id, :status_1, :userId)";
            // $sql = "INSERT INTO `user_orders` (`order_id`, `status`, `user_id`)
            // VALUES('". $order_id ."'," . 0 . "," . $userId . ")";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':order_id', $order_id, PDO::PARAM_STR);
            $stmt->bindValue(':status_1', 0, PDO::PARAM_INT);
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // echo "order_id: " . $order_id . "\n";
            

            foreach ($cart as $key => $value) {
                $item_id = $value['item_id'];
                $count_sum = $value['count_sum'];
                $price_sum = $value['count_sum'] * $currentItemsArr[$value['item_id']]['price'];

                $sql = "INSERT INTO `item_trade_orders` (`item_id`, `count_sum`, `price_sum`, `order_id`)
                VALUES(:item_id, :count_sum, :price_sum, :order_id)";
                
                $stmt = $db->prepare($sql);
                $stmt->bindValue('item_id', $item_id, PDO::PARAM_INT);
                $stmt->bindValue(':count_sum', $count_sum, PDO::PARAM_INT);
                $stmt->bindValue('price_sum', $price_sum, PDO::PARAM_INT);
                $stmt->bindValue('order_id', $order_id, PDO::PARAM_STR);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                
            }
                        
            return $order_id;
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
