<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::models(['Order', 'OrderItem', 'Product', 'Address',"User"]);
Import::configs(["db/Query"]);

class OrderRepository
{
    // Tạo đơn hàng mới
    public static function createOrder($userId, $addressId, $paymentMethod)
    {
        try {
            $pdo = PDODatabase::getInstance()->getConnection();
            
            $sql = "INSERT INTO orders (user_id, address_id, payment_method, status_payment, status_order, created_at) 
                    VALUES (:uid, :aid, :method, 'unpaid', 'confirmed', NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':uid', $userId);
            $stmt->bindValue(':aid', $addressId);
            $stmt->bindValue(':method', $paymentMethod);
            
            if ($stmt->execute()) {
                return $pdo->lastInsertId();
            } else {
                $err = $stmt->errorInfo();
                error_log("SQL Error createOrder: " . print_r($err, true));
                return false;
            }
        } catch (Exception $e) {
            error_log("Exception createOrder: " . $e->getMessage());
            return false;
        }
    }

    // Tạo chi tiết đơn hàng
    public static function createOrderItem($orderId, $item)
    {
        try {
            $p = $item->product;
            $totalPrice = $p->price_current * $item->quantity;

            $pdo = PDODatabase::getInstance()->getConnection();
            
            $sql = "INSERT INTO orderitems (order_id, product_id, product_name, product_slug, product_description, quantity, product_price, product_total_price) 
                    VALUES (:oid, :pid, :name, :slug, :desc, :qty, :price, :total)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':oid', $orderId);
            $stmt->bindValue(':pid', $p->id);
            $stmt->bindValue(':name', $p->name);
            $stmt->bindValue(':slug', $p->slug);
            $stmt->bindValue(':desc', $p->description ?? '');
            $stmt->bindValue(':qty', $item->quantity);
            $stmt->bindValue(':price', $p->price_current);
            $stmt->bindValue(':total', $totalPrice);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Exception createOrderItem: " . $e->getMessage());
            return false;
        }
    }

    // Lấy đơn hàng theo ID
    public static function getOrderById($orderId)
    {
        $row = Query::from("orders o")
            ->select(["o.*", "a.address", "a.city", "a.ward", "a.phone"])
            ->joins(["addresss a ON o.address_id = a.id"])
            ->where(["o.id = :id"])
            ->bindValue([":id" => $orderId])
            ->get();

        if (!$row) return null;

        $order = new Order();
        $order->id = $row['id'];
        $order->payment_method = $row['payment_method'];
        $order->status_order = $row['status_order'];
        $order->status_payment = $row['status_payment'];
        $order->created_at = $row['created_at'];
        
        $addr = new Address();
        $addr->address = $row['address'];
        $addr->city = $row['city'];
        $addr->ward = $row['ward'];
        $addr->phone = $row['phone'];
        $order->address = $addr;

        return $order;
    }

    // Lấy chi tiết đơn hàng
    public static function getOrderItems($orderId)
    {
        $rows = Query::from("orderitems")
            ->where(["order_id = :oid"])
            ->bindValue([":oid" => $orderId])
            ->getAll();

        $items = [];
        foreach ($rows as $row) {
            $item = new OrderItem();
            $item->product_id = $row['product_id'];
            $item->product_name = $row['product_name'];
            $item->quantity = $row['quantity'];
            $item->product_price = $row['product_price'];
            $item->product_total_price = $row['product_total_price'];
            
            // Lấy ảnh hiển thị
            $img = Query::from("productimages")->where(["product_id=:pid"])->bindValue([":pid"=>$row['product_id']])->limit(1)->get();
            $item->product_image = $img['url'] ?? 'https://via.placeholder.com/150'; 

            $items[] = $item;
        }
        return $items;
    }

    // Lấy đơn hàng theo User ID
    public static function getOrdersByUserId($userId)
    {
        $pdo = PDODatabase::getInstance()->getConnection();
        
        $sql = "SELECT o.*, SUM(oi.product_total_price) as total_amount 
                FROM orders o 
                LEFT JOIN orderitems oi ON o.id = oi.order_id 
                WHERE o.user_id = :uid 
                GROUP BY o.id 
                ORDER BY o.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':uid', $userId);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $orders = [];
        foreach ($rows as $row) {
            $order = new Order();
            $order->id = $row['id'];
            $order->status_order = $row['status_order'];
            $order->created_at = $row['created_at'];
            
            $productTotal = $row['total_amount'] ?? 0;
            $order->total_amount = $productTotal + 30000;

            $orders[] = $order;
        }
        return $orders;
    }

    // PHÂN TRANG cho Admin
    public static function paginate($page, $limit, $search = "", $status = "all")
    {
        $pdo = PDODatabase::getInstance()->getConnection();
        $offset = ($page - 1) * $limit;

        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            // [SỬA LỖI] Đặt tên tham số riêng biệt (:searchName, :searchEmail) thay vì dùng chung
            $where .= " AND (o.id = :searchNum OR u.name LIKE :searchName OR u.email LIKE :searchEmail)";
            
            $params[':searchNum']   = is_numeric($search) ? $search : -1;
            $params[':searchName']  = "%$search%";
            $params[':searchEmail'] = "%$search%"; // Bind thêm lần nữa cho email
        }

        if ($status !== 'all') {
            $where .= " AND o.status_order = :status";
            $params[':status'] = $status;
        }

        $sql = "SELECT o.*, u.name as user_name, u.email as user_email 
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE $where
                ORDER BY o.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        
        // Bind các tham số trong mảng
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        
        // Bind Limit và Offset thủ công
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $orders = [];
        foreach ($rows as $row) {
            $order = new Order();
            $order->id = $row['id'];

            $order->user = new User(); 
            $order->user->name = $row['user_name'] ?? 'Khách lẻ (Unknown)';
            $order->user->email = $row['user_email'] ?? ''; 

            $order->payment_method = $row['payment_method'];
            $order->status_order = $row['status_order'];
            $order->status_payment = $row['status_payment'];
            $order->created_at = $row['created_at'];
            
            $orders[] = $order;
        }
        return $orders;
    }

    // 2. SỬA HÀM COUNT (Logic tương tự)
    public static function count($search = "", $status = "all")
    {
        $pdo = PDODatabase::getInstance()->getConnection();

        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            // [SỬA LỖI] Tách tham số tương tự hàm paginate
            $where .= " AND (o.id = :searchNum OR u.name LIKE :searchName OR u.email LIKE :searchEmail)";
            
            $params[':searchNum']   = is_numeric($search) ? $search : -1;
            $params[':searchName']  = "%$search%";
            $params[':searchEmail'] = "%$search%";
        }

        if ($status !== 'all') {
            $where .= " AND o.status_order = :status";
            $params[':status'] = $status;
        }

        $sql = "SELECT COUNT(*) as total 
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE $where";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    // XÓA đơn hàng
    public static function delete($orderId)
    {
        try {
            $pdo = PDODatabase::getInstance()->getConnection();
            
            // Xóa chi tiết đơn hàng trước
            $sql1 = "DELETE FROM orderitems WHERE order_id = :oid";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->bindValue(':oid', $orderId);
            $stmt1->execute();
            
            // Sau đó xóa đơn hàng
            $sql2 = "DELETE FROM orders WHERE id = :oid";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->bindValue(':oid', $orderId);
            return $stmt2->execute();
        } catch (Exception $e) {
            error_log("Exception delete order: " . $e->getMessage());
            return false;
        }
    }

    // CẬP NHẬT trạng thái đơn hàng
    public static function updateOrderStatus($orderId, $newStatus)
    {
        try {
            $pdo = PDODatabase::getInstance()->getConnection();
            
            $sql = "UPDATE orders SET status_order = :status WHERE id = :oid";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':status', $newStatus);
            $stmt->bindValue(':oid', $orderId);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Exception updateOrderStatus: " . $e->getMessage());
            return false;
        }
    }
}