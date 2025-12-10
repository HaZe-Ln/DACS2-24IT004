<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["DashboardRepository"]);
Import::middlewares(["Authentication"]);

class AdminDashboardController
{
    public function index()
    {
        // 1. Check quyền Admin
        $currentUser = Authentication::getAuthentication();
        if (!$currentUser || $currentUser->role !== 'admin') {
            header("Location: /app/views/pages/auth/SignIn.php"); exit;
        }

        // 2. Lấy số liệu Thống kê Tổng quan
        $revenue = DashboardRepository::getTotalRevenue();
        $orders  = DashboardRepository::getTotalOrders();
        $users   = DashboardRepository::getTotalUsers();
        $products= DashboardRepository::getTotalProducts();
        $posts   = DashboardRepository::getTotalPosts();

        // Cấu trúc Cards cho View
        $metrics = [
            [
                "label" => "Tổng doanh thu", 
                "icon" => "payments", 
                "value" => number_format($revenue, 0, ',', '.') . "đ", 
                "trend" => "Thực tế", 
                "trend_color" => "text-green-600"
            ],
            [
                "label" => "Tổng đơn hàng", 
                "icon" => "shopping_cart", 
                "value" => number_format($orders), 
                "trend" => "Tổng số", 
                "trend_color" => "text-blue-600"
            ],
            [
                "label" => "Khách hàng", 
                "icon" => "group", 
                "value" => number_format($users), 
                "trend" => "User", 
                "trend_color" => "text-gray-600"
            ],
            [
                "label" => "Sản phẩm", 
                "icon" => "inventory", 
                "value" => number_format($products), 
                "trend" => "Active", 
                "trend_color" => "text-purple-600"
            ],
            [
                "label" => "Bài viết", 
                "icon" => "article", 
                "value" => number_format($posts), 
                "trend" => "Blog", 
                "trend_color" => "text-orange-600"
            ],
        ];

        // 3. Xử lý Biểu đồ Doanh thu (Line/Area Chart)
        $chartDataRaw = DashboardRepository::getRevenueChartData(15); // 15 ngày gần nhất
        $labels = [];
        $data = [];
        foreach ($chartDataRaw as $item) {
            $labels[] = date('d/m', strtotime($item['date'])); // Format ngày/tháng
            $data[] = $item['revenue'];
        }
        $revenueSeries = [
            "labels" => $labels,
            "data" => $data
        ];

        // 4. Xử lý Biểu đồ Trạng thái (Doughnut Chart)
        $statusRaw = DashboardRepository::getOrderStatusData();
        $statusLabels = [];
        $statusData = [];
        $statusColors = [];
        
        // Mapping màu sắc cho trạng thái
        $colorMap = [
            'completed' => '#4CAF50', // Xanh lá
            'shipping'  => '#2196F3', // Xanh dương
            'confirmed' => '#FFC107', // Vàng
            'unpaid'    => '#9E9E9E', // Xám
            'cancelled' => '#F44336'  // Đỏ
        ];
        
        // Mapping tên hiển thị
        $nameMap = [
            'completed' => 'Hoàn tất',
            'shipping'  => 'Đang giao',
            'confirmed' => 'Đã xác nhận',
            'unpaid'    => 'Chưa thanh toán',
            'cancelled' => 'Đã hủy'
        ];

        foreach ($statusRaw as $item) {
            $st = $item['status_order'];
            $statusLabels[] = $nameMap[$st] ?? ucfirst($st);
            $statusData[] = $item['count'];
            $statusColors[] = $colorMap[$st] ?? '#000000';
        }

        $orderStatus = [
            "labels" => $statusLabels,
            "data" => $statusData,
            "colors" => $statusColors
        ];

        return [
            'metrics' => $metrics,
            'revenueSeries' => $revenueSeries,
            'orderStatus' => $orderStatus
        ];
    }
}