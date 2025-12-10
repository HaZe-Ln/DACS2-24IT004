<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["CartRepository"]);
Import::helpers(["Request"]);
Import::middlewares(["Authentication"]);

class CartController
{
    /**
     * Hiển thị trang giỏ hàng và xử lý Cập nhật/Xóa
     */
    public function index()
    {
        // 1. Kiểm tra đăng nhập
        $user = Authentication::getAuthentication();
        if (!$user) {
            header("Location: /app/views/pages/auth/SignIn.php");
            exit;
        }

        $message = null;

        // 2. Xử lý hành động POST từ form (Cập nhật số lượng / Xóa)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $cartItemId = $_POST['cart_item_id'] ?? 0;

            if ($action === 'update_qty') {
                $qty = (int)($_POST['quantity'] ?? 1);
                CartRepository::updateQuantity($cartItemId, $qty);
                $message = ["type" => "success", "text" => "Đã cập nhật số lượng."];
            } 
            elseif ($action === 'remove_item') {
                CartRepository::remove($cartItemId);
                $message = ["type" => "success", "text" => "Đã xóa sản phẩm khỏi giỏ."];
            }
        }

        // 3. Lấy dữ liệu từ Repository
        $cartItems = CartRepository::getItems($user->id);
        
        // 4. Tính tổng tiền
        $totalAmount = 0;
        foreach ($cartItems as $item) {
            if (isset($item->product)) {
                $price = $item->product->price_current;
                $qty = $item->quantity;
                $totalAmount += ($price * $qty);
            }
        }

        // 5. Trả dữ liệu về View
        return [
            'cartItems'   => $cartItems,
            'totalAmount' => $totalAmount,
            'message'     => $message
        ];
    }

    /**
     * Hàm thêm vào giỏ (Xử lý Form Submit từ ProductCard)
     */
    public function add()
    {
        $user = Authentication::getAuthentication();
        if (!$user) {
            header("Location: /app/views/pages/auth/SignIn.php");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            // Lấy URL hiện tại (Ví dụ: /Product.php?page=10)
            $redirectUrl = $_POST['redirect_url'] ?? '/app/views/pages/Product.php';

            if ($productId > 0) {
                CartRepository::add($user->id, $productId, $quantity);
            }
            
            // 1. Kiểm tra URL đã có dấu ? chưa để nối thêm tham số
            $separator = (parse_url($redirectUrl, PHP_URL_QUERY) == NULL) ? '?' : '&';
            
            // 2. Tạo Anchor (Neo) để trình duyệt cuộn tới đúng sản phẩm đó
            $anchor = "#product-card-" . $productId;

            // 3. Redirect: URL cũ + &added=1 + #product-card-123
            header("Location: " . $redirectUrl . $separator . "added=1" . $anchor);
            exit;
        }
    }
    public function getCount()
    {
        header('Content-Type: application/json');
        
        $user = Authentication::getAuthentication();
        if (!$user) {
            echo json_encode(['count' => 0]);
            exit;
        }

        $cartItems = CartRepository::getItems($user->id);
        $totalItems = 0;
        foreach ($cartItems as $item) {
            $totalItems += $item->quantity;
        }

        echo json_encode(['count' => $totalItems]);
        exit;
    }
}

// ========== SỬA PHẦN ROUTING Ở CUỐI FILE ==========
if (isset($_GET['action'])) {
    $controller = new CartController();
    $action = $_GET['action'];
    
    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->add();
    } elseif ($action === 'getCount') {  // ← THÊM DÒNG NÀY
        $controller->getCount();          // ← THÊM DÒNG NÀY
    }        
}

