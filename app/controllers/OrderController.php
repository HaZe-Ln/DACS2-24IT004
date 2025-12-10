<?php
// Tắt hiển thị lỗi để giao diện sạch đẹp (khi chạy thật)
// ini_set('display_errors', 0); 

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
// Import đầy đủ
Import::repositories(["CartRepository", "UserRepository", "OrderRepository"]); 
Import::models(["Order", "OrderItem"]);
Import::middlewares(["Authentication"]);

class OrderController
{
    // 1. Trang Thanh toán
    public function checkout()
    {
        $user = Authentication::getAuthentication();
        if (!$user) { header("Location: /app/views/pages/auth/SignIn.php"); exit; }

        $cartItems = CartRepository::getItems($user->id);
        
        // Nếu giỏ hàng trống thì đá về trang sản phẩm
        if (empty($cartItems)) { 
            header("Location: /app/views/pages/Product.php"); 
            exit; 
        }

        $subtotal = 0;
        foreach ($cartItems as $item) {
            if (isset($item->product)) {
                $subtotal += $item->product->price_current * $item->quantity;
            }
        }
        $shippingFee = 30000;
        $total = $subtotal + $shippingFee;

        $addresses = UserRepository::getAddressesByUserId($user->id);

        return [
            'user' => $user,
            'addresses' => $addresses,
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'total' => $total
        ];
    }

    // 2. Xử lý Đặt hàng
    public function placeOrder()
    {
        try {
            $user = Authentication::getAuthentication();
            if (!$user) { header("Location: /app/views/pages/auth/SignIn.php"); exit; }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $addressId = $_POST['address_id'] ?? 0;
                $paymentMethod = $_POST['payment_method'] ?? 'cod';

                if ($addressId == 0) {
                    echo "<script>alert('Vui lòng chọn địa chỉ giao hàng!'); window.history.back();</script>";
                    exit;
                }

                $cartItems = CartRepository::getItems($user->id);
                if (empty($cartItems)) { 
                    header("Location: /app/views/pages/Product.php");
                    exit;
                }

                // A. Tạo Đơn hàng
                $orderId = OrderRepository::createOrder($user->id, $addressId, $paymentMethod);
                
                if (!$orderId) {
                    throw new Exception("Không thể tạo đơn hàng. Vui lòng thử lại.");
                }

                // B. Tạo Chi tiết đơn hàng
                foreach ($cartItems as $item) {
                    OrderRepository::createOrderItem($orderId, $item);
                }

                // C. Xóa giỏ hàng
                CartRepository::clear($user->id);

                // D. Chuyển hướng sang trang Cảm ơn
                header("Location: /app/views/pages/OrderConfirmation.php?order_id=" . $orderId);
                exit;
            }
        } catch (Exception $e) {
            // Nếu có lỗi, hiện thông báo đơn giản
            echo "<script>alert('Lỗi: " . $e->getMessage() . "'); window.history.back();</script>";
            exit;
        }
    }

    // 3. Lấy dữ liệu trang Xác nhận (Sửa lỗi Null ở đây)
    public function confirmation()
    {
        $orderId = $_GET['order_id'] ?? 0;
        
        // Gọi Repository lấy dữ liệu thật
        $order = OrderRepository::getOrderById($orderId);
        $items = OrderRepository::getOrderItems($orderId);

        return [
            'order' => $order, 
            'items' => $items
        ];
    }

    // 4. Trang Chi tiết đơn hàng
    public function detail()
    {
        return $this->confirmation(); 
    }
}

// XỬ LÝ ROUTING
if (isset($_GET['action'])) {
    $controller = new OrderController();
    $action = $_GET['action'];
    
    if ($action === 'placeOrder') {
        $controller->placeOrder();
    }
    // Các action khác nếu cần gọi trực tiếp
}