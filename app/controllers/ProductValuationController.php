<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["ProductValuationRepository"]);
Import::middlewares(["Authentication"]);

class ProductValuationController
{
    public function submit()
    {
        $user = Authentication::getAuthentication();
        if (!$user) {
            header("Location: /app/views/pages/auth/SignIn.php");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'] ?? 0;
            $starRate = $_POST['star_rate'] ?? 5;
            $content = $_POST['content'] ?? '';

            // 1. Kiểm tra lại quyền (Bảo mật phía Server)
            $orderId = ProductValuationRepository::getEligibleOrderId($user->id, $productId);

            if ($orderId) {
                // 2. Lưu đánh giá
                ProductValuationRepository::create([
                    'product_id' => $productId,
                    'order_id'   => $orderId,
                    'star_rate'  => $starRate,
                    'content'    => $content
                ]);
                
                // Redirect về trang chi tiết sản phẩm kèm thông báo
                header("Location: /app/views/pages/ProductDetail.php?id=$productId&review_success=1");
            } else {
                echo "<script>alert('Bạn không có quyền đánh giá sản phẩm này (Chưa mua hoặc đã đánh giá rồi).'); window.history.back();</script>";
            }
        }
    }
}

// Routing đơn giản
if (isset($_GET['action']) && $_GET['action'] == 'submit') {
    (new ProductValuationController())->submit();
}