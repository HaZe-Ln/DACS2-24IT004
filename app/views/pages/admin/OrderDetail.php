<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["AdminOrderController"]);

// Gọi Controller
$controller = new AdminOrderController();
$data = $controller->detail();

$order = $data['order'];
$items = $data['items'];

// Tính toán
$subtotal = 0;
foreach($items as $it) $subtotal += $it->product_total_price;
$shipping = 30000;
$total = $subtotal + $shipping;

// Helper hiển thị badge
function getStatusBadge($status) {
    switch ($status) {
        case 'completed': return ['bg-green-100 text-green-800', 'Hoàn tất'];
        case 'shipping':  return ['bg-blue-100 text-blue-800', 'Đang giao'];
        case 'confirmed': return ['bg-yellow-100 text-yellow-800', 'Đã xác nhận'];
        case 'unpaid':    return ['bg-gray-100 text-gray-800', 'Chưa thanh toán'];
        default:          return ['bg-gray-100 text-gray-800', $status];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Chi tiết đơn hàng #" . $order->id]); ?>

<body class="font-display bg-gray-50 text-gray-900">
    <div class="relative flex min-h-screen w-full">
        <?php Import::layout('AdminSidebar', ["active" => "orders"]); ?>

        <main class="flex-1 p-6 lg:p-8">
            <div class="max-w-5xl mx-auto">
                <!-- Header với nút Back -->
                <div class="mb-6">
                    <a href="/app/views/pages/admin/OrderManagement.php" 
                       class="inline-flex items-center gap-2 text-gray-600 hover:text-primary transition-colors mb-4">
                        <span class="material-symbols-outlined">arrow_back</span>
                        <span>Quay lại danh sách</span>
                    </a>
                    <h1 class="text-3xl font-bold text-primary">Chi tiết đơn hàng #<?= $order->id ?></h1>
                </div>

                <?php if($order): ?>
                    <!-- Thông tin đơn hàng -->
                    <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
                            <div>
                                <p class="text-gray-500 mb-1">Mã đơn hàng</p>
                                <p class="font-semibold text-gray-900">#<?= $order->id ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 mb-1">Ngày đặt hàng</p>
                                <p class="font-semibold text-gray-900">
                                    <?= date('d/m/Y H:i', strtotime($order->created_at)) ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-500 mb-1">Tổng thanh toán</p>
                                <p class="font-bold text-primary text-lg">
                                    <?= number_format($total, 0, ',', '.') ?>₫
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-500 mb-1">Trạng thái</p>
                                <?php [$badgeClass, $badgeLabel] = getStatusBadge($order->status_order); ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?= $badgeClass ?>">
                                    <?= $badgeLabel ?>
                                </span>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Thông tin giao hàng -->
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary">location_on</span>
                                        Địa chỉ giao hàng
                                    </h3>
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <p class="font-medium text-gray-900"><?= htmlspecialchars($order->address->address) ?></p>
                                        <p><?= htmlspecialchars($order->address->ward) ?></p>
                                        <p><?= htmlspecialchars($order->address->city) ?></p>
                                        <p class="flex items-center gap-1 mt-2">
                                            <span class="material-symbols-outlined text-base">call</span>
                                            <?= htmlspecialchars($order->address->phone) ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Thông tin thanh toán -->
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary">payment</span>
                                        Thanh toán
                                    </h3>
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <p>Phương thức: <span class="font-medium text-gray-900 uppercase"><?= htmlspecialchars($order->payment_method) ?></span></p>
                                        <p>Trạng thái: 
                                            <span class="font-medium <?= $order->status_payment === 'paid' ? 'text-green-600' : 'text-orange-600' ?>">
                                                <?= $order->status_payment === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form cập nhật trạng thái -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <form method="POST" action="/app/controllers/AdminOrderController.php?action=updateStatus" 
                                  class="flex items-end gap-4">
                                <input type="hidden" name="order_id" value="<?= $order->id ?>">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Cập nhật trạng thái đơn hàng
                                    </label>
                                    <select name="new_status" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                        <option value="confirmed" <?= $order->status_order === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                        <option value="shipping" <?= $order->status_order === 'shipping' ? 'selected' : '' ?>>Đang giao</option>
                                        <option value="completed" <?= $order->status_order === 'completed' ? 'selected' : '' ?>>Hoàn tất</option>
                                    </select>
                                </div>
                                <button type="submit" 
                                        class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                    Cập nhật
                                </button>
                            </form>
                        </div>
                    </section>

                    <!-- Danh sách sản phẩm -->
                    <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">shopping_bag</span>
                            Danh sách sản phẩm
                        </h2>
                        <div class="space-y-4">
                            <?php foreach ($items as $item): ?>
                                <?php $img = !empty($item->product_image) ? $item->product_image : 'https://via.placeholder.com/100'; ?>
                                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                                    <img src="<?= $img ?>" 
                                         class="w-20 h-20 object-cover rounded-md border border-gray-200" 
                                         alt="<?= htmlspecialchars($item->product_name) ?>">
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900"><?= htmlspecialchars($item->product_name) ?></p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Số lượng: <span class="font-medium text-gray-700"><?= $item->quantity ?></span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900">
                                            <?= number_format($item->product_total_price ?? 0, 0, ',', '.') ?>₫
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            <?= number_format($item->product_price ?? 0, 0, ',', '.') ?>₫ / cái
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Tổng kết -->
                            <div class="border-t border-gray-200 pt-4 space-y-2">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Tạm tính</span>
                                    <span class="font-semibold text-gray-900"><?= number_format($subtotal, 0, ',', '.') ?>₫</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Phí vận chuyển</span>
                                    <span class="font-semibold text-gray-900"><?= number_format($shipping, 0, ',', '.') ?>₫</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold text-primary pt-2 border-t border-gray-200">
                                    <span>Tổng cộng</span>
                                    <span><?= number_format($total, 0, ',', '.') ?>₫</span>
                                </div>
                            </div>
                        </div>
                    </section>
                
                <?php else: ?>
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                        <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">info</span>
                        <p class="text-gray-500">Không tìm thấy đơn hàng.</p>
                        <a href="/app/views/pages/admin/OrderManagement.php" 
                           class="inline-block mt-4 px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                            Quay lại danh sách
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>