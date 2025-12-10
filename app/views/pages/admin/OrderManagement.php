<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["AdminOrderController"]);

// Gọi Controller để lấy dữ liệu
$controller = new AdminOrderController();
$data = $controller->index();

// Extract dữ liệu từ controller
$orders = $data['orders'];
$page = $data['page'];
$totalPages = $data['totalPages'];
$search = $data['search'];
$status = $data['status'];

// Helper hiển thị badge trạng thái
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
<?php Import::layout('Head', ["title" => "Quản lý Đơn hàng"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
    <div class="relative flex min-h-screen w-full">
        <?php Import::layout('AdminSidebar', ["active" => "orders"]); ?>

        <main class="flex-1 p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <header class="mb-8">
                    <h1 class="text-3xl font-bold text-primary">Quản lý Đơn hàng</h1>
                    <p class="text-gray-500 mt-1">Xem, tìm kiếm, và quản lý tất cả các đơn hàng tại đây.</p>
                </header>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <form method="GET" action="/app/views/pages/admin/OrderManagement.php" class="flex flex-col w-full">
                            <?php if($status !== 'all'): ?>
                                <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
                            <?php endif; ?>
                            
                            <label class="flex flex-col w-full md:w-1/2">
                                <div class="flex w-full items-stretch rounded-lg h-10 bg-gray-50 border border-gray-200 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
                                    <div class="text-gray-500 flex items-center justify-center pl-3">
                                        <span class="material-symbols-outlined text-xl">search</span>
                                    </div>
                                    <input 
                                        name="q"
                                        value="<?= htmlspecialchars($search) ?>"
                                        class="form-input flex w-full min-w-0 flex-1 border-none bg-transparent h-full placeholder:text-gray-500 px-3 text-sm focus:ring-0" 
                                        placeholder="Tìm theo Mã ĐH (#123) hoặc Tên khách..." 
                                    />
                                </div>
                            </label>
                        </form>
                    </div>

                    <div class="p-4 flex flex-wrap items-center gap-3 border-b border-gray-200 bg-gray-50/50">
                        <div class="flex items-center gap-4 flex-wrap w-full">
                            <span class="text-sm font-medium text-gray-600">Lọc theo trạng thái:</span>
                            <div class="flex gap-2 flex-wrap">
                                <?php 
                                    $statuses = [
                                        'all' => 'Tất cả',
                                        'confirmed' => 'Đã xác nhận',
                                        'shipping' => 'Đang giao',
                                        'completed' => 'Hoàn tất'
                                    ];
                                    $baseLink = "/app/views/pages/admin/OrderManagement.php?q=" . urlencode($search);
                                ?>
                                <?php foreach ($statuses as $key => $label): ?>
                                    <?php 
                                        $isActive = ($status === $key);
                                        $classes = $isActive 
                                            ? "bg-primary text-white shadow-sm" 
                                            : "bg-white border border-gray-200 text-gray-700 hover:bg-gray-50";
                                    ?>
                                    <a href="<?= $baseLink . "&status=" . $key ?>" 
                                       class="flex h-8 shrink-0 items-center justify-center px-4 rounded-lg text-sm font-medium transition-colors <?= $classes ?>">
                                        <?= $label ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">Mã Đơn Hàng</th>
                                    <th class="px-6 py-3 font-semibold">Tên Khách Hàng</th>
                                    <th class="px-6 py-3 font-semibold">Ngày Tạo</th>
                                    <th class="px-6 py-3 font-semibold">Thanh toán</th>
                                    <th class="px-6 py-3 font-semibold">Trạng Thái</th>
                                    <th class="px-6 py-3 text-right font-semibold">Hành Động</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                            Không tìm thấy đơn hàng nào.
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($orders as $order): ?>
                                    <?php 
                                        [$badgeClass, $badgeLabel] = getStatusBadge($order->status_order);
                                        $paymentStatus = $order->status_payment === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán';
                                        $paymentClass = $order->status_payment === 'paid' ? 'text-green-600' : 'text-orange-600';
                                    ?>
                                <tr class="bg-white hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-mono font-medium text-primary whitespace-nowrap">
                                        #<?= htmlspecialchars($order->id) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($order->user->name ?? 'N/A') ?></div>
                                        <div class="text-xs text-gray-400"><?= htmlspecialchars($order->user_email ?? '') ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">
                                        <?= date('d/m/Y', strtotime($order->created_at ?? 'now')) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-semibold <?= $paymentClass ?>">
                                            <?= $paymentStatus ?>
                                        </div>
                                        <div class="text-xs text-gray-400 uppercase mt-0.5"><?= htmlspecialchars($order->payment_method) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badgeClass ?>">
                                            <?= $badgeLabel ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="/app/views/pages/admin/OrderDetail.php?id=<?= urlencode($order->id) ?>" 
                                               class="p-2 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" 
                                               title="Xem chi tiết">
                                                <span class="material-symbols-outlined text-xl">visibility</span>
                                            </a>
                                            
                                            <form method="POST" 
                                                  action="/app/controllers/AdminOrderController.php?action=delete" 
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa đơn hàng #<?= $order->id ?>? Hành động này không thể hoàn tác.');">
                                                <input type="hidden" name="id" value="<?= $order->id ?>">
                                                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                                                <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
                                                <input type="hidden" name="page" value="<?= $page ?>">
                                                <button type="submit" 
                                                        class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" 
                                                        title="Xóa đơn hàng">
                                                    <span class="material-symbols-outlined text-xl">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                    <div class="flex items-center justify-between p-4 border-t border-gray-200 bg-gray-50">
                        <span class="text-sm text-gray-600">
                            Trang <span class="font-semibold text-gray-900"><?= $page ?></span> / <span class="font-semibold text-gray-900"><?= $totalPages ?></span>
                        </span>
                        
                        <?php 
                            $prevPage = $page > 1 ? $page - 1 : 1;
                            $nextPage = $page < $totalPages ? $page + 1 : $totalPages;
                        ?>
                        
                        <div class="inline-flex items-center -space-x-px text-sm">
                            <a href="<?= $baseLink . "&page=" . $prevPage . "&status=" . $status ?>" 
                               class="flex items-center justify-center px-3 h-8 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700 <?= $page <= 1 ? 'pointer-events-none opacity-50' : '' ?>">
                                <span class="material-symbols-outlined text-base">chevron_left</span>
                            </a>
                            
                            <span class="flex items-center justify-center px-3 h-8 text-primary bg-primary/10 border border-primary z-10 font-medium">
                                <?= $page ?>
                            </span>

                            <a href="<?= $baseLink . "&page=" . $nextPage . "&status=" . $status ?>" 
                               class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700 <?= $page >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>">
                                <span class="material-symbols-outlined text-base">chevron_right</span>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>