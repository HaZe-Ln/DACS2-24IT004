<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["ProductValuationRepository"]);
Import::middlewares(["Authentication"]);

class ProductValuationController
{
    // 1. Hiển thị form đánh giá (GET)
    public function create()
    {
        $user = Authentication::getAuthentication();
        if (!$user) { header("Location: /app/views/pages/auth/SignIn.php"); exit; }

        $orderId = $_GET['order_id'] ?? 0;
        $productId = $_GET['product_id'] ?? 0;

        // Gọi hàm getValuableItem vừa thêm bên Repository
        $item = ProductValuationRepository::getValuableItem($user->id, $orderId, $productId);

        // Nếu không tìm thấy (do đánh giá rồi, hoặc đơn chưa hoàn thành) -> Đá về trang User
        if (!$item) {
            echo "<script>alert('Sản phẩm không hợp lệ hoặc bạn đã đánh giá rồi!'); window.location.href='/app/views/pages/User.php?tab=valuations';</script>";
            exit;
        }

        return [
            'item' => $item,
            'user' => $user
        ];
    }

    // 2. Lưu đánh giá (POST)
    public function store()
    {
        $user = Authentication::getAuthentication();
        if (!$user) { header("Location: /app/views/pages/auth/SignIn.php"); exit; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'order_id' => $_POST['order_id'],
                'product_id' => $_POST['product_id'],
                'star_rate' => $_POST['star_rate'],
                'content' => $_POST['content'] ?? ''
            ];

            // Kiểm tra dữ liệu đầu vào
            if ($data['star_rate'] < 1 || $data['star_rate'] > 5) {
                echo "<script>alert('Vui lòng chọn số sao!'); window.history.back();</script>";
                exit;
            }

            // Gọi Repository để lưu
            if (ProductValuationRepository::create($data)) {
                // Thành công -> Quay lại danh sách và báo thành công
                header("Location: /app/views/pages/User.php?tab=valuations&msg=success");
            } else {
                echo "<script>alert('Lỗi hệ thống! Vui lòng thử lại.'); window.history.back();</script>";
            }
            exit;
        }
    }
}

// Xử lý Routing (Để form action trỏ vào được)
if (isset($_GET['action'])) {
    $controller = new ProductValuationController();
    if ($_GET['action'] === 'store') {
        $controller->store();
    }
}