<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["AdminOrderController"]);


$controller = new AdminOrderController();
$data = $controller->edit(); 

$order = $data['order'];
$items = $data['items'];

$subtotal = 0;
foreach($items as $it) $subtotal += $it->product_total_price;
$total = $subtotal + 30000;

$userName  = $order->user->name ?? 'Khách lẻ';
$userEmail = $order->user->email ?? 'Không có email';
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Sửa đơn hàng #" . $order->id]); ?>

<body class="font-display bg-gray-50 text-gray-900">
  <div class="relative flex min-h-screen w-full">
    <?php Import::layout('AdminSidebar', ["active" => "orders"]); ?>

    <main class="flex-1 p-6 lg:p-10">
      <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
          <div>
            <h1 class="text-3xl font-bold text-primary flex items-center gap-3">
                Sửa đơn hàng #<?= $order->id ?>
                <?php Import::component('OrderStatusBadge', ['status' => $order->status_order]); ?>
            </h1>
            <div class="text-sm text-gray-500 mt-2 flex gap-2 items-center">
                <a href="/app/views/pages/admin/OrderManagement.php" class="hover:text-primary">Quản lý đơn hàng</a>
                <span>/</span>
                <span>Cập nhật thông tin</span>
            </div>
          </div>
          <div class="flex gap-3">
            <a href="/app/views/pages/admin/OrderManagement.php" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
              Hủy bỏ
            </a>
            <button form="edit-order-form" type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary border border-transparent rounded-lg shadow-sm hover:bg-primary/90 flex items-center gap-2 transition-colors">
              <span class="material-symbols-outlined text-[18px]">save</span>
              Lưu thay đổi
            </button>
          </div>
        </div>

        <form id="edit-order-form" method="POST" action="" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 flex flex-col gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-base font-bold text-gray-900">Thông tin chung</h2>
                    </div>
                    <div class="p-6 grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Mã đơn hàng</label>
                            <div class="font-mono text-gray-900 font-bold text-lg">#<?= $order->id ?></div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Ngày đặt hàng</label>
                            <div class="text-gray-900"><?= date('d/m/Y H:i', strtotime($order->created_at)) ?></div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Phương thức thanh toán</label>
                            <div class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 uppercase">
                                <?= $order->payment_method ?>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Tổng tiền</label>
                            <div class="text-primary font-bold text-lg"><?= number_format($total, 0, ',', '.') ?> ₫</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-base font-bold text-gray-900">Sản phẩm đã đặt</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3">Sản phẩm</th>
                                    <th class="px-6 py-3 text-center">SL</th>
                                    <th class="px-6 py-3 text-right">Đơn giá</th>
                                    <th class="px-6 py-3 text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($items as $item): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img src="<?= $item->product_image ?>" class="w-10 h-10 rounded object-cover border border-gray-200">
                                            <div>
                                                <p class="font-medium text-gray-900 line-clamp-1"><?= htmlspecialchars($item->product_name) ?></p>
                                                <p class="text-xs text-gray-400">Mã SP: #<?= $item->product_id ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-medium"><?= $item->quantity ?></td>
                                    <td class="px-6 py-4 text-right"><?= number_format($item->product_price, 0, ',', '.') ?>đ</td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900"><?= number_format($item->product_total_price, 0, ',', '.') ?>đ</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right font-medium text-gray-500">Phí vận chuyển:</td>
                                    <td class="px-6 py-3 text-right font-medium text-gray-900">30.000đ</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right font-bold text-gray-900">Tổng cộng:</td>
                                    <td class="px-6 py-3 text-right font-bold text-primary text-lg"><?= number_format($total, 0, ',', '.') ?>đ</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 flex flex-col gap-6">
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-blue-50/50">
                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">settings_backup_restore</span>
                            Cập nhật trạng thái
                        </h2>
                    </div>
                    <div class="p-5 space-y-4">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái xử lý</label>
                            <select name="status_order" class="form-select w-full border-gray-300 focus:border-primary focus:ring-primary rounded-lg text-sm h-10 shadow-sm">
                                <option value="unconfirmed" <?= $order->status_order === 'unconfirmed' ? 'selected' : '' ?>>Chờ xác nhận</option>
                                <option value="confirmed" <?= $order->status_order === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                <option value="shipping" <?= $order->status_order === 'shipping' ? 'selected' : '' ?>>Đang giao hàng</option>
                                <option value="completed" <?= $order->status_order === 'completed' ? 'selected' : '' ?>>Hoàn tất</option>
                                <option value="cancelled" <?= $order->status_order === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                            </select>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái thanh toán</label>
                            <select name="status_payment" class="form-select w-full border-gray-300 focus:border-primary focus:ring-primary rounded-lg text-sm h-10">
                                <option value="unpaid" <?= $order->status_payment === 'unpaid' ? 'selected' : '' ?>>Chưa thanh toán</option>
                                <option value="paid" <?= $order->status_payment === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-2 italic">
                                * Bạn có thể chuyển sang "Đã thanh toán" ngay khi nhận được tiền chuyển khoản.
                            </p>
                        </div>

                        <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200 text-xs text-gray-500">
                            <div class="flex justify-between items-center mb-1">
                                <span>Hiện tại:</span>
                                <?php Import::component('OrderPaymentBadge', ['status' => $order->status_payment, 'size' => 'sm']); ?>
                            </div>
                            <p class="italic mt-2 text-[11px]">
                                * Lưu ý: Khi chuyển trạng thái đơn hàng sang "Hoàn tất", hệ thống thường tự động đánh dấu "Đã thanh toán".
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500 text-xl">person_pin</span>
                            Thông tin nhận hàng
                        </h2>
                    </div>
                    <div class="p-5 space-y-5">
                        
                        <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold">
                                <?= strtoupper(substr($userName, 0, 1)) ?>
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-sm font-bold text-gray-900 truncate"><?= htmlspecialchars($userName) ?></p>
                                <p class="text-xs text-gray-500 truncate" title="<?= htmlspecialchars($userEmail) ?>"><?= htmlspecialchars($userEmail) ?></p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="field-group" data-field="phone">
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Số điện thoại</label>
                                <div class="relative">
                                    <input type="text" id="input-phone" value="<?= htmlspecialchars($order->address->phone) ?>" disabled
                                           class="form-input w-full border-gray-300 bg-gray-50 rounded-lg text-sm pl-9 pr-10 disabled:cursor-not-allowed">
                                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-[18px]">call</span>
                                    <button type="button" onclick="toggleEdit('phone', <?= $order->id ?>)" class="btn-edit absolute right-2 top-1/2 -translate-y-1/2 text-blue-500 hover:bg-blue-50 p-1 rounded"><span class="material-symbols-outlined text-[18px]">edit_square</span></button>
                                    <button type="button" onclick="saveField('phone', <?= $order->id ?>)" class="btn-save absolute right-2 top-1/2 -translate-y-1/2 text-green-600 hover:bg-green-50 p-1 rounded hidden"><span class="material-symbols-outlined text-[20px]">check_circle</span></button>
                                </div>
                            </div>

                            <div class="field-group" data-field="address">
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Địa chỉ</label>
                                <div class="relative">
                                    <input type="text" id="input-address" value="<?= htmlspecialchars($order->address->address) ?>" disabled
                                           class="form-input w-full border-gray-300 bg-gray-50 rounded-lg text-sm pl-9 pr-10 disabled:cursor-not-allowed">
                                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-[18px]">home_pin</span>
                                    <button type="button" onclick="toggleEdit('address', <?= $order->id ?>)" class="btn-edit absolute right-2 top-1/2 -translate-y-1/2 text-blue-500 hover:bg-blue-50 p-1 rounded"><span class="material-symbols-outlined text-[18px]">edit_square</span></button>
                                    <button type="button" onclick="saveField('address', <?= $order->id ?>)" class="btn-save absolute right-2 top-1/2 -translate-y-1/2 text-green-600 hover:bg-green-50 p-1 rounded hidden"><span class="material-symbols-outlined text-[20px]">check_circle</span></button>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <div class="field-group" data-field="ward">
                                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Phường/Xã</label>
                                    <div class="relative">
                                        <input type="text" id="input-ward" value="<?= htmlspecialchars($order->address->ward) ?>" disabled class="form-input w-full border-gray-300 bg-gray-50 rounded-lg text-sm px-3 pr-8 disabled:cursor-not-allowed">
                                        <button type="button" onclick="toggleEdit('ward', <?= $order->id ?>)" class="btn-edit absolute right-1 top-1/2 -translate-y-1/2 text-blue-500 p-1"><span class="material-symbols-outlined text-[16px]">edit_square</span></button>
                                        <button type="button" onclick="saveField('ward', <?= $order->id ?>)" class="btn-save absolute right-1 top-1/2 -translate-y-1/2 text-green-600 hidden p-1"><span class="material-symbols-outlined text-[18px]">check_circle</span></button>
                                    </div>
                                </div>
                                <div class="field-group" data-field="city">
                                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Quận/Tỉnh</label>
                                    <div class="relative">
                                        <input type="text" id="input-city" value="<?= htmlspecialchars($order->address->city) ?>" disabled class="form-input w-full border-gray-300 bg-gray-50 rounded-lg text-sm px-3 pr-8 disabled:cursor-not-allowed">
                                        <button type="button" onclick="toggleEdit('city', <?= $order->id ?>)" class="btn-edit absolute right-1 top-1/2 -translate-y-1/2 text-blue-500 p-1"><span class="material-symbols-outlined text-[16px]">edit_square</span></button>
                                        <button type="button" onclick="saveField('city', <?= $order->id ?>)" class="btn-save absolute right-1 top-1/2 -translate-y-1/2 text-green-600 hidden p-1"><span class="material-symbols-outlined text-[18px]">check_circle</span></button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </form>

      </div>
    </main>
  </div>
  
  <script>
    function toggleEdit(field, orderId) {
        const container = document.querySelector(`.field-group[data-field="${field}"]`);
        const input = container.querySelector('input');
        const btnEdit = container.querySelector('.btn-edit');
        const btnSave = container.querySelector('.btn-save');
        input.disabled = false;
        input.focus(); 
        input.classList.remove('bg-gray-50');
        btnEdit.classList.add('hidden');
        btnSave.classList.remove('hidden');
    }

    function saveField(field, orderId) {
        const container = document.querySelector(`.field-group[data-field="${field}"]`);
        const input = container.querySelector('input');
        const btnEdit = container.querySelector('.btn-edit');
        const btnSave = container.querySelector('.btn-save');
        const value = input.value;

        fetch('/app/controllers/AdminOrderController.php?action=ajaxUpdateAddress', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `order_id=${orderId}&field=${field}&value=${encodeURIComponent(value)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                input.disabled = true;
                input.classList.add('bg-gray-50');
                btnSave.classList.add('hidden');
                btnEdit.classList.remove('hidden');
                input.classList.add('ring-2', 'ring-green-500');
                setTimeout(() => input.classList.remove('ring-2', 'ring-green-500'), 1000);
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể cập nhật'));
            }
        });
    }
  </script>
</body>
</html>