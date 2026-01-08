<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
// Gọi CartController
Import::controllers(["CartController"]);

$controller = new CartController();
$data = $controller->index();

// Hứng dữ liệu từ Controller
$cartItems   = $data['cartItems'];
$totalAmount = $data['totalAmount'];
$message     = $data['message'];
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Giỏ hàng"]); ?>

<body class="font-display bg-background-light text-text-light">
  <?php Import::layout("UserNavigation") ?>
  
  <?php Import::component('Loader'); ?>
  <?php Import::component('Notification', ['message' => $message]); ?>

  <main class="flex-1 py-10 px-4 sm:px-6 md:px-8 min-h-screen">
    <div class="max-w-6xl mx-auto">
        
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Giỏ hàng của bạn</h1>

        <?php if (empty($cartItems)): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center flex flex-col items-center">
                <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">shopping_cart_off</span>
                <h2 class="text-xl font-bold text-gray-700 mb-2">Giỏ hàng đang trống</h2>
                <p class="text-gray-500 mb-6">Bạn chưa thêm sản phẩm nào vào giỏ hàng.</p>
                <a href="/app/views/pages/Product.php" class="inline-flex items-center justify-center bg-primary text-white px-6 py-3 rounded-lg font-bold hover:bg-primary/90 transition-transform hover:scale-105">
                    Tiếp tục mua sắm
                </a>
            </div>
        <?php else: ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-4">
                    <?php foreach ($cartItems as $item): ?>
                <?php Import::component('CartItemCard', ['item' => $item]); ?>
                    <?php endforeach; ?>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 sticky top-24">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-4 border-b border-gray-100">Thông tin đơn hàng</h3>
                        
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Tạm tính:</span>
                            <span class="font-medium text-gray-900"><?= number_format($totalAmount, 0, ',', '.') ?>₫</span>
                        </div>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-gray-600">Phí vận chuyển:</span>
                            <span class="text-green-600 text-sm font-medium">Chưa Tính Phí</span>
                        </div>
                        
                        <div class="border-t border-gray-100 pt-4 mb-6">
                            <div class="flex justify-between items-end">
                                <span class="text-lg font-bold text-gray-800">Tổng cộng:</span>
                                <span class="text-2xl font-bold text-primary"><?= number_format($totalAmount, 0, ',', '.') ?>₫</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 text-right">(Đã bao gồm VAT)</p>
                        </div>

                        <a href="/app/views/pages/Checkout.php" class="flex w-full py-3 bg-primary hover:bg-primary/90 text-white justify-center font-bold rounded-lg shadow-md transition-all hover:shadow-lg hover:scale-[1.02]">
                            Tiến hành thanh toán
                        </a>
                        
                        <a href="/app/views/pages/Product.php" class="block w-full text-center mt-3 text-sm text-gray-500 hover:text-primary transition-colors hover:underline">
                            Tiếp tục xem sản phẩm
                        </a>
                    </div>
                </div>

            </div>
        <?php endif; ?>

    </div>
  </main>

  <?php Import::layout("Footer") ?>

  <script>
    function showLoader() {
        const loader = document.getElementById('global-loader');
        if(loader) {
            loader.classList.remove('hidden');
            loader.classList.add('flex');
        }
    }

    function closeToast() {
        const toast = document.getElementById('toast-notification');
        if (toast) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 500);
        }
    }
    // Tự động tắt thông báo sau 3 giây
    setTimeout(() => closeToast(), 3000);
  </script>
  <?php Import::component('SocialWidget'); ?>
</body>
</html>