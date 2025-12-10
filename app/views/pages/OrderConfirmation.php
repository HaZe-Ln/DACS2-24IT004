<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["OrderController"]);

// 1. Gọi Controller lấy dữ liệu
$controller = new OrderController();
$data = $controller->confirmation();

$order = $data['order'];
$items = $data['items'];

// Tính tổng tiền
$totalOrder = 0;
foreach($items as $it) $totalOrder += $it->product_total_price;
$shipping = 30000;
$finalTotal = $totalOrder + $shipping;
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Đặt hàng thành công"]); ?>

<body class="font-display bg-background-light text-text-light">
  <?php Import::layout("UserNavigation") ?>

  <main class="flex flex-col min-h-screen">
    <div class="px-4 sm:px-6 lg:px-8 py-12 md:py-16 flex-1">
      <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-xl border border-gray-200 p-8 md:p-12 text-center">
        
        <?php if ($order): ?>
            <div class="mb-6">
              <div class="w-20 h-20 mx-auto bg-green-100 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined text-green-500 !text-5xl">check</span>
              </div>
            </div>
            
            <h1 class="text-3xl md:text-4xl font-bold text-primary mb-3">Đặt hàng thành công!</h1>
            <p class="text-gray-600 text-lg mb-6">Cảm ơn bạn đã mua sắm tại HTAMusic.</p>

            <div class="text-left border-t border-b border-gray-200 py-6 my-8 space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-gray-500">Mã đơn hàng:</span>
                <span class="font-semibold text-gray-800">#<?= $order->id ?></span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-500">Trạng thái:</span>
                <span class="font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full text-sm">
                    <?= $order->status_order ?>
                </span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-500">Ngày đặt hàng:</span>
                <span class="font-semibold text-gray-800">
                    <?= date('d/m/Y H:i', strtotime($order->created_at)) ?>
                </span>
              </div>
              
              <div class="pt-4 mt-2 border-t border-gray-200 space-y-2">
                <?php foreach ($items as $item): ?>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-700 line-clamp-1">
                            <?= htmlspecialchars($item->product_name) ?> x<?= $item->quantity ?>
                        </span>
                        <span class="font-medium text-gray-900">
                            <?= number_format($item->product_total_price, 0, ',', '.') ?>₫
                        </span>
                    </div>
                <?php endforeach; ?>
                
                <div class="flex justify-between items-center text-sm pt-2 border-t border-dashed border-gray-200">
                  <span class="text-gray-600">Phí vận chuyển</span>
                  <span class="font-medium text-gray-900"><?= number_format($shipping, 0, ',', '.') ?>₫</span>
                </div>
                <div class="flex justify-between items-center text-lg mt-2">
                  <span class="text-gray-600">Tổng thanh toán:</span>
                  <span class="font-bold text-primary"><?= number_format($finalTotal, 0, ',', '.') ?>₫</span>
                </div>
              </div>
            </div>

            <p class="text-gray-500 mb-8">Chúng tôi sẽ sớm liên hệ để xác nhận đơn hàng.</p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
              <a class="w-full sm:w-auto inline-block bg-primary text-white font-semibold py-3 px-8 rounded-lg shadow-md hover:bg-primary/90 transition-colors" href="/app/views/pages/Product.php">
                Tiếp tục mua sắm
              </a>
              <a class="w-full sm:w-auto inline-block bg-gray-200 text-gray-800 font-semibold py-3 px-8 rounded-lg hover:bg-gray-300 transition-colors" href="/app/views/pages/OrderDetail.php?order_id=<?= $order->id ?>">
                Xem chi tiết đơn hàng
              </a>
            </div>

        <?php else: ?>
            <h1 class="text-2xl font-bold text-red-500">Không tìm thấy đơn hàng!</h1>
            <a href="/" class="underline text-gray-600">Quay về trang chủ</a>
        <?php endif; ?>

      </div>
    </div>
  </main>

  <?php Import::layout("Footer") ?>
</body>
</html>