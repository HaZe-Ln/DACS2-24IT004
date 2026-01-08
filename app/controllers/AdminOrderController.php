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
    public function edit()
    {
        $orderId = $_GET['id'] ?? 0;
        if (!$orderId) { header("Location: /app/views/pages/admin/OrderManagement.php"); exit; }

        // Lấy dữ liệu
        $order = OrderRepository::getOrderById($orderId);
        $items = OrderRepository::getOrderItems($orderId); // Lấy items chỉ để hiển thị

        if (!$order) { header("Location: /app/views/pages/admin/OrderManagement.php"); exit; }

        // Xử lý POST (Cập nhật)
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $data = [
                'phone'        => $_POST['phone'] ?? '',
                'address'      => $_POST['address'] ?? '',
                'ward'         => $_POST['ward'] ?? '',
                'city'         => $_POST['city'] ?? '',
                'status_order' => $_POST['status_order'] ?? 'confirmed',
                'status_payment' => $_POST['status_payment'] ?? 'unpaid'
            ];

            if (OrderRepository::update($orderId, $data)) {
                // Thành công -> Quay lại trang danh sách hoặc load lại trang edit
                header("Location: /app/views/pages/admin/OrderManagement.php");
                exit;
            } else {
                echo "<script>alert('Lỗi cập nhật đơn hàng!');</script>";
            }
        }

        return [
            'order' => $order,
            'items' => $items
        ];
    }
    // [MỚI] API xử lý cập nhật nhanh địa chỉ qua Ajax
    public function ajaxUpdateAddress()
    {
        // Set header JSON để JS nhận diện đúng
        header('Content-Type: application/json');

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $orderId = $_POST['order_id'] ?? 0;
            $field   = $_POST['field'] ?? '';
            $value   = trim($_POST['value'] ?? '');

            if (!$orderId || !$field) {
                echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
                exit;
            }

            $result = OrderRepository::updateAddressField($orderId, $field, $value);

            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi Database']);
            }
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
        case 'ajaxUpdateAddress': 
            $controller->ajaxUpdateAddress();
            break;    
    }
}