<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["OrderRepository"]);
Import::models(["Order"]);

class AdminOrderController
{
    // 1. Lấy danh sách đơn hàng với phân trang
    public function index()
    {
        $page = max(1, (int)($_GET["page"] ?? 1));
        $limit = 10;
        $search = trim($_GET["q"] ?? "");
        $status = $_GET["status"] ?? "all";

        $orders = OrderRepository::paginate($page, $limit, $search, $status);
        $totalRecords = OrderRepository::count($search, $status);
        $totalPages = ceil($totalRecords / $limit);

        return [
            'orders' => $orders,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalRecords' => $totalRecords,
            'search' => $search,
            'status' => $status,
            'limit' => $limit
        ];
    }

    // 2. Xóa đơn hàng
    public function delete()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $deleteId = $_POST["id"] ?? null;
            if ($deleteId) {
                OrderRepository::delete((int)$deleteId);
            }
            // Redirect về trang quản lý với các tham số tìm kiếm hiện tại
            $search = $_POST['search'] ?? '';
            $status = $_POST['status'] ?? 'all';
            $page = $_POST['page'] ?? 1;
            
            $redirect = "/app/views/pages/admin/OrderManagement.php?page=$page&q=" . urlencode($search) . "&status=$status";
            header("Location: $redirect");
            exit;
        }
    }

    // 3. Lấy chi tiết đơn hàng
    public function detail()
    {
        $orderId = $_GET['id'] ?? 0;
        
        if (!$orderId) {
            header("Location: /app/views/pages/admin/OrderManagement.php");
            exit;
        }

        $order = OrderRepository::getOrderById($orderId);
        $items = OrderRepository::getOrderItems($orderId);

        if (!$order) {
            header("Location: /app/views/pages/admin/OrderManagement.php");
            exit;
        }

        return [
            'order' => $order,
            'items' => $items
        ];
    }

    // 4. Cập nhật trạng thái đơn hàng
    public function updateStatus()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $orderId = $_POST["order_id"] ?? null;
            $newStatus = $_POST["new_status"] ?? null;
            
            if ($orderId && $newStatus) {
                OrderRepository::updateOrderStatus((int)$orderId, $newStatus);
            }
            
            // Redirect về chi tiết đơn hàng
            header("Location: /app/views/pages/admin/OrderDetail.php?id=$orderId");
            exit;
        }
    }
}

// XỬ LÝ ROUTING
if (isset($_GET['action'])) {
    $controller = new AdminOrderController();
    $action = $_GET['action'];
    
    switch($action) {
        case 'delete':
            $controller->delete();
            break;
        case 'updateStatus':
            $controller->updateStatus();
            break;
    }
}