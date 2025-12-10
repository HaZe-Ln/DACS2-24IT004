<?php
Import::models(['ProductValuation', 'User']);
Import::configs(["db/Query"]);

class ProductValuationRepository
{
    /**
     * Kiểm tra xem User có quyền đánh giá sản phẩm này không.
     * Logic: Tìm đơn hàng (đã hoàn thành/hoặc confirmed) chứa sản phẩm này 
     * mà User chưa viết đánh giá cho đơn đó.
     * * @return int|false Trả về order_id nếu được phép, false nếu không.
     */
    public static function getEligibleOrderId($userId, $productId)
    {
        $pdo = PDODatabase::getInstance()->getConnection();

        // Query: 
        // 1. Tìm trong bảng orders (o) và orderitems (oi)
        // 2. Của user hiện tại và product hiện tại
        // 3. TRỪ ĐI những order_id đã tồn tại trong bảng productvaluations (pv) với cùng product_id
        $sql = "SELECT o.id 
                FROM orders o
                JOIN orderitems oi ON o.id = oi.order_id
                WHERE o.user_id = :uid 
                  AND oi.product_id = :pid
                  -- Tùy chọn: Chỉ cho phép đánh giá đơn đã hoàn thành
                  -- AND o.status_order = 'completed' 
                  AND o.id NOT IN (
                      SELECT order_id 
                      FROM productvaluations 
                      WHERE product_id = :pid
                  )
                ORDER BY o.created_at DESC 
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':uid', $userId);
        $stmt->bindValue(':pid', $productId);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['id'] : false;
    }

    /**
     * Lưu đánh giá mới
     */
    public static function create($data)
    {
        $val = new ProductValuation();
        $val->star_rate = $data['star_rate'];
        $val->content = $data['content'];
        $val->order_id = $data['order_id'];
        $val->product_id = $data['product_id'];

        return Query::from("productvaluations")->save($val);
    }

    /**
     * Lấy danh sách đánh giá của 1 sản phẩm
     */
   public static function getByProductId($productId)
    {
        // BƯỚC 1: Tạo đối tượng Query và Bind dữ liệu trước
        $queryObject = Query::from("productvaluations pv")
            ->select(["pv.*", "u.name as user_name", "u.avatar as user_avatar"])
            ->joins(["orders o ON pv.order_id = o.id", "users u ON o.user_id = u.id"])
            ->where(["pv.product_id = :pid"])
            ->bindValue([":pid" => $productId]); // <--- Bind ở đây

        // BƯỚC 2: Sinh ra chuỗi SQL để sắp xếp
        $sql = $queryObject->toQuery("ORDER BY pv.id DESC");

        // BƯỚC 3: Thực thi
        return $queryObject->getAll($sql);
    }
    public static function checkReviewed($orderId, $productId)
    {
        $result = Query::from("productvaluations")
            ->select(["COUNT(*) as total"]) // Dùng select count
            ->where(["order_id = :oid", "product_id = :pid"])
            ->bindValue([":oid" => $orderId, ":pid" => $productId])
            ->get();
            
        return ($result && $result['total'] > 0);
    }
}