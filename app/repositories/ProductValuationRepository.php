<?php
Import::configs(["db/Query"]);
Import::models(["ProductValuation", "Product", "Order"]);

class ProductValuationRepository
{
    // Lấy danh sách sản phẩm cần đánh giá của User
    public static function getUnvaluedItems($userId)
    {
        $pdo = PDODatabase::getInstance()->getConnection();
        
        $sql = "
            SELECT 
                oi.order_id, 
                oi.product_id, 
                oi.product_name, 
                oi.product_price,
                oi.product_total_price,
                pi.url as product_image,
                o.created_at as order_date
            FROM orderitems oi
            JOIN orders o ON oi.order_id = o.id
            LEFT JOIN (
                SELECT product_id, MIN(url) as url 
                FROM productimages 
                GROUP BY product_id
            ) pi ON oi.product_id = pi.product_id
            WHERE o.user_id = :uid 
              AND o.status_order = 'completed'
              AND NOT EXISTS (
                  SELECT 1 FROM productvaluations pv 
                  WHERE pv.order_id = oi.order_id 
                  AND pv.product_id = oi.product_id
              )
            ORDER BY o.created_at DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lưu đánh giá
    public static function create($data)
    {
        try {
            $val = new ProductValuation();
            $val->order_id = $data['order_id'];
            $val->product_id = $data['product_id'];
            $val->star_rate = $data['star_rate'];
            $val->content = $data['content'];
            
            return Query::from("productvaluations")->save($val);
        } catch (Exception $e) {
            error_log("Valuation Error: " . $e->getMessage());
            return false;
        }
    }

    // Lấy thông tin 1 món hàng để hiện lên form
    public static function getValuableItem($userId, $orderId, $productId)
    {
        $pdo = PDODatabase::getInstance()->getConnection();
        
        $sql = "
            SELECT 
                oi.order_id, 
                oi.product_id, 
                oi.product_name, 
                pi.url as product_image,
                o.created_at as order_date
            FROM orderitems oi
            JOIN orders o ON oi.order_id = o.id
            LEFT JOIN (
                SELECT product_id, MIN(url) as url 
                FROM productimages 
                GROUP BY product_id
            ) pi ON oi.product_id = pi.product_id
            WHERE o.user_id = :uid 
              AND o.id = :oid
              AND oi.product_id = :pid
              AND o.status_order = 'completed'
              AND NOT EXISTS (
                  SELECT 1 FROM productvaluations pv 
                  WHERE pv.order_id = oi.order_id 
                  AND pv.product_id = oi.product_id
              )
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':uid' => $userId,
            ':oid' => $orderId,
            ':pid' => $productId
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy danh sách đã đánh giá của User
    public static function getValuedItems($userId)
    {
        $pdo = PDODatabase::getInstance()->getConnection();
        
        $sql = "
            SELECT 
                pv.*,
                p.name as product_name,
                pi.url as product_image,
                o.created_at as order_date
            FROM productvaluations pv
            JOIN orders o ON pv.order_id = o.id
            JOIN products p ON pv.product_id = p.id
            LEFT JOIN (
                SELECT product_id, MIN(url) as url 
                FROM productimages 
                GROUP BY product_id
            ) pi ON p.id = pi.product_id
            WHERE o.user_id = :uid
            ORDER BY pv.id DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // [ĐÃ SỬA LỖI Ở HÀM NÀY]
    public static function getByProductId($productId)
    {
        $pdo = PDODatabase::getInstance()->getConnection();
        
        // Đã xóa dòng "u.avatar as user_avatar" để tránh lỗi column not found
        $sql = "
            SELECT 
                pv.*,
                u.name as user_name,
                pv.created_at as review_date
            FROM productvaluations pv
            JOIN orders o ON pv.order_id = o.id
            JOIN users u ON o.user_id = u.id
            WHERE pv.product_id = :pid
            ORDER BY pv.id DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':pid' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function paginateAdmin($page, $limit, $search = "")
    {
        $pdo = PDODatabase::getInstance()->getConnection();
        $offset = ($page - 1) * $limit;
        
        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            // [SỬA LỖI] Đổi :s thành :s1, :s2, :s3 riêng biệt
            $where .= " AND (p.name LIKE :s1 OR u.name LIKE :s2 OR pv.content LIKE :s3)";
            $params[':s1'] = "%$search%";
            $params[':s2'] = "%$search%";
            $params[':s3'] = "%$search%";
        }

        // Join bảng Orders -> Users để lấy tên người đánh giá
        // Join bảng Products để lấy tên sản phẩm
        $sql = "SELECT pv.*, p.name as product_name, p.id as product_id, u.name as user_name 
                FROM productvaluations pv
                JOIN products p ON pv.product_id = p.id
                JOIN orders o ON pv.order_id = o.id
                JOIN users u ON o.user_id = u.id
                WHERE $where
                ORDER BY pv.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // [ADMIN] Đếm tổng số đánh giá (để phân trang)
    public static function countAdmin($search = "")
    {
        $pdo = PDODatabase::getInstance()->getConnection();
        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            // [SỬA LỖI] Tương tự hàm trên
            $where .= " AND (p.name LIKE :s1 OR u.name LIKE :s2 OR pv.content LIKE :s3)";
            $params[':s1'] = "%$search%";
            $params[':s2'] = "%$search%";
            $params[':s3'] = "%$search%";
        }

        $sql = "SELECT COUNT(*) as total 
                FROM productvaluations pv
                JOIN products p ON pv.product_id = p.id
                JOIN orders o ON pv.order_id = o.id
                JOIN users u ON o.user_id = u.id
                WHERE $where";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // [ADMIN] Xóa đánh giá (Ví dụ đánh giá spam/tục tĩu)
    public static function delete($id)
    {
        try {
            $pdo = PDODatabase::getInstance()->getConnection();
            $stmt = $pdo->prepare("DELETE FROM productvaluations WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            return false;
        }
    }
}