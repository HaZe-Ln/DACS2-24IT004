<?php
Import::configs(["db/Query"]);

class DashboardRepository
{
    /**
     * 1. Lấy các chỉ số tổng quan (Card trên cùng)
     */
    public static function getTotalRevenue()
    {
        // Tính tổng tiền từ bảng orderitems của các đơn hàng đã HOÀN THÀNH
        $pdo = PDODatabase::getInstance()->getConnection();
        $sql = "SELECT SUM(oi.product_total_price) as revenue 
                FROM orders o
                JOIN orderitems oi ON o.id = oi.order_id
                WHERE o.status_order = 'completed'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Cộng thêm phí ship (giả sử 30k/đơn) cho các đơn hoàn thành
        // Hoặc nếu bạn lưu phí ship trong DB thì query cột đó. 
        // Ở đây mình tính theo giá sản phẩm thuần túy cho chính xác.
        return $result['revenue'] ?? 0;
    }

    public static function getTotalOrders()
    {
        $result = Query::from("orders")->select(["COUNT(*) as total"])->get();
        return $result['total'] ?? 0;
    }

    public static function getTotalUsers()
    {
        // Chỉ đếm user thường, không đếm admin
        $result = Query::from("users")
            ->select(["COUNT(*) as total"])
            ->where(["role = 'user'"])
            ->get();
        return $result['total'] ?? 0;
    }

    public static function getTotalProducts()
    {
        $result = Query::from("products")
            ->select(["COUNT(*) as total"])
            ->where(["deleted_at IS NULL"])
            ->get();
        return $result['total'] ?? 0;
    }

    public static function getTotalPosts()
    {
        $result = Query::from("posts")
            ->select(["COUNT(*) as total"])
            ->where(["deleted_at IS NULL"])
            ->get();
        return $result['total'] ?? 0;
    }

    /**
     * 2. Lấy dữ liệu biểu đồ Doanh thu (Mặc định 15 ngày gần nhất)
     */
    public static function getRevenueChartData($days = 15)
    {
        $pdo = PDODatabase::getInstance()->getConnection();
        
        // Query Group By Ngày
        $sql = "SELECT DATE(o.created_at) as date, SUM(oi.product_total_price) as revenue
                FROM orders o
                JOIN orderitems oi ON o.id = oi.order_id
                WHERE o.status_order = 'completed' 
                  AND o.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(o.created_at)
                ORDER BY date ASC";
                
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 3. Lấy dữ liệu biểu đồ Trạng thái đơn hàng (Hình tròn)
     */
    public static function getOrderStatusData()
    {
        $pdo = PDODatabase::getInstance()->getConnection();
        
        $sql = "SELECT status_order, COUNT(*) as count 
                FROM orders 
                GROUP BY status_order";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}