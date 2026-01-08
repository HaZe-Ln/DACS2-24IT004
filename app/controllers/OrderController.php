<?php
// Tắt hiển thị lỗi để giao diện sạch đẹp (khi chạy thật)
// ini_set('display_errors', 0); 

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
// Import đầy đủ
Import::repositories(["CartRepository", "UserRepository", "OrderRepository","ProductRepository"]); 
Import::models(["Order", "OrderItem"]);
Import::middlewares(["Authentication"]);

class OrderController
{
    // 1. Trang Thanh toán
   public function checkout()
    {
        $user = Authentication::getAuthentication();
        if (!$user) { header("Location: /app/views/pages/auth/SignIn.php"); exit; }

        $addresses = UserRepository::getAddressesByUserId($user->id);
        
        // --- LOGIC MỚI: CHECK MUA NGAY ---
        $isDirect = isset($_GET['direct']) && $_GET['direct'] == 'true';
        $cartItems = [];

        if ($isDirect) {
            // TRƯỜNG HỢP 1: MUA NGAY (Tạo CartItem giả lập)
            $productId = $_GET['product_id'] ?? 0;
            $quantity  = $_GET['quantity'] ?? 1;
            
            // Lấy thông tin sản phẩm
            $product = ProductRepository::getById($productId);
            
            if ($product) {
                // Tạo object CartItem thủ công để View hiển thị được
                $item = new CartItem();
                $item->product = $product;
                $item->quantity = $quantity;
                $item->product_id = $productId; // Lưu để dùng sau
                
                $cartItems[] = $item; // Giỏ hàng ảo chỉ có 1 món
            }
        } else {
            // TRƯỜNG HỢP 2: MUA TỪ GIỎ HÀNG THẬT (Code cũ)
            $cartItems = CartRepository::getItems($user->id);
            if (empty($cartItems)) { 
                header("Location: /app/views/pages/Product.php"); 
                exit; 
            }
        }

        // Tính toán tiền (Dùng chung cho cả 2 trường hợp)
        $subtotal = 0;
        foreach ($cartItems as $item) {
            if (isset($item->product)) {
                $subtotal += $item->product->price_current * $item->quantity;
            }
        }
        $shippingFee = 30000;
        $total = $subtotal + $shippingFee;

        return [
            'user' => $user,
            'addresses' => $addresses,
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'total' => $total,
            'isDirect' => $isDirect // Truyền biến này ra View
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
                
                // Kiểm tra cờ Direct
                $isDirect = $_POST['is_direct'] ?? false;

                if ($addressId == 0) {
                    echo "<script>alert('Vui lòng chọn địa chỉ giao hàng!'); window.history.back();</script>";
                    exit;
                }

                // A. CHUẨN BỊ ITEM ĐỂ LƯU
                $orderItemsToSave = [];

                if ($isDirect) {
                    // Nếu mua ngay -> Lấy thông tin từ form ẩn, KHÔNG lấy từ DB Cart
                    $productId = $_POST['direct_product_id'];
                    $quantity  = $_POST['direct_quantity'];
                    
                    $product = ProductRepository::getById($productId);
                    if ($product) {
                        // Tạo object giả lập để hàm createOrderItem hiểu
                        $item = new CartItem();
                        $item->product = $product;
                        $item->quantity = $quantity;
                        $orderItemsToSave[] = $item;
                    }
                } else {
                    // Nếu mua thường -> Lấy từ Cart DB
                    $orderItemsToSave = CartRepository::getItems($user->id);
                }

                if (empty($orderItemsToSave)) { 
                    throw new Exception("Không có sản phẩm để thanh toán.");
                }

                // B. Tạo Đơn hàng
                $orderId = OrderRepository::createOrder($user->id, $addressId, $paymentMethod);
                if (!$orderId) throw new Exception("Lỗi tạo đơn hàng.");

                // C. Tạo Chi tiết đơn hàng
                foreach ($orderItemsToSave as $item) {
                    OrderRepository::createOrderItem($orderId, $item);
                }

                // D. Xóa giỏ hàng (QUAN TRỌNG)
                if (!$isDirect) {
                    // Chỉ xóa giỏ hàng nếu đây là đơn mua từ giỏ. 
                    // Mua ngay thì KHÔNG xóa giỏ cũ của người ta.
                    CartRepository::clear($user->id);
                }

                header("Location: /app/views/pages/OrderConfirmation.php?order_id=" . $orderId);
                exit;
            }
        } catch (Exception $e) {
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
    
    public function cancel()
    {
        // 1. Kiểm tra đăng nhập
        $user = Authentication::getAuthentication();
        if (!$user) { 
            header("Location: /app/views/pages/auth/SignIn.php"); 
            exit; 
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = $_POST['order_id'] ?? 0;
            
            // 2. Gọi Repository để hủy
            $isCancelled = OrderRepository::cancelByUser($orderId, $user->id);
            
            if ($isCancelled) {
                // Thành công: Quay lại trang danh sách đơn hàng
                echo "<script>alert('Đã hủy đơn hàng thành công!'); window.location.href='/app/views/pages/User.php?tab=orders';</script>";
            } else {
                // Thất bại (Do Admin đã xác nhận rồi hoặc lỗi khác)
                echo "<script>alert('Không thể hủy đơn hàng này. Có thể đơn đã được xác nhận hoặc đang giao.'); window.history.back();</script>";
            }
            exit;
        }
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
    if ($action === 'cancel') { // Thêm dòng này
        $controller->cancel();
    }    
}