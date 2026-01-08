<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["OrderController"]);

$controller = new OrderController();
$data = $controller->detail();

$order = $data['order'];
$items = $data['items'];

$subtotal = 0;
foreach($items as $it) $subtotal += $it->product_total_price;
$shipping = 30000;
$total = $subtotal + $shipping;
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Chi tiết đơn hàng"]); ?>

<body class="font-display bg-background-light text-text-light">
  <?php Import::layout("UserNavigation") ?>

  <main class="flex-1 container mx-auto px-4 py-10">
    <div class="max-w-5xl mx-auto flex flex-col gap-8">
        <?php if($order): ?>
          <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 text-sm">
              <div>
                <p class="text-gray-500 mb-1">Mã đơn hàng</p>
                <p class="font-semibold text-text-light">#<?= $order->id ?></p>
              </div>
              <div>
                <p class="text-gray-500 mb-1">Ngày đặt hàng</p>
                <p class="font-semibold text-text-light">
                    <?= date('d/m/Y H:i', strtotime($order->created_at)) ?>
                </p>
              </div>
              <div>
                <p class="text-gray-500 mb-1">Tổng thanh toán</p>
                <p class="font-bold text-primary"><?= number_format($total, 0, ',', '.') ?>₫</p>
              </div>
              <div class="sm:col-span-2">
                <p class="text-gray-500 mb-2">Trạng thái đơn hàng</p>
                <?php Import::component('OrderStatusBadge', [
                    'status' => $order->status_order,
                    'size' => 'sm'
                ]); ?>
              </div>
              <div>
                <p class="text-gray-500 mb-2">Trạng thái thanh toán</p>
                <?php Import::component('OrderPaymentBadge', [
                    'status' => $order->status_payment,
                    'size' => 'sm'
                ]); ?>
              </div>
              <div class="sm:col-span-2 md:col-span-3">
                <p class="text-gray-500 mb-1">Địa chỉ giao hàng</p>
                <p class="font-semibold text-text-light">
                    <?= htmlspecialchars($order->address->address) ?>, 
                    <?= htmlspecialchars($order->address->ward) ?>, 
                    <?= htmlspecialchars($order->address->city) ?>
                    (ĐT: <?= htmlspecialchars($order->address->phone) ?>)
                </p>
              </div>
            </div>
          </section>

          <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-text-light mb-6">Danh sách sản phẩm</h2>
            <div class="space-y-6">
              <?php foreach ($items as $item): ?>
              <?php $img = !empty($item->product_image) ? $item->product_image : 'https://via.placeholder.com/100'; ?>
              <div class="flex items-center gap-4">
                <img src="<?= $img ?>" class="w-20 h-20 object-cover rounded-md border border-gray-100">
                <div class="flex-1">
                  <p class="font-semibold text-text-light"><?= htmlspecialchars($item->product_name) ?></p>
                  <p class="text-sm text-gray-500">Số lượng: <?= $item->quantity ?></p>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-text-light">
                      <?= number_format($item->product_total_price ?? 0, 0, ',', '.') ?>đ
                  </p>
                  <p class="text-sm text-gray-500">
                      <?= number_format($item->product_price ?? 0, 0, ',', '.') ?>đ / cái
                  </p>
                </div>
              </div>
              <div class="border-t border-gray-200"></div>
              <?php endforeach; ?>
              
              <div class="flex justify-between text-sm text-gray-600">
                <span>Tạm tính</span>
                <span class="font-semibold text-text-light"><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
              </div>
              <div class="flex justify-between text-sm text-gray-600">
                <span>Phí vận chuyển</span>
                <span class="font-semibold text-text-light"><?= number_format($shipping, 0, ',', '.') ?>đ</span>
              </div>
              <div class="flex justify-between text-lg font-bold text-primary">
                <span>Tổng cộng</span>
                <span><?= number_format($total, 0, ',', '.') ?>đ</span>
              </div>
            </div>
          </section>
        
        <?php else: ?>
            <p>Không tìm thấy đơn hàng.</p>
        <?php endif; ?>
    </div>
  </main>
  <?php Import::layout("Footer") ?>
  <?php Import::component('SocialWidget'); ?>
</body>
</html>