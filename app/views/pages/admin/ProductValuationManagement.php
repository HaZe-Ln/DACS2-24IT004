<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["AdminProductValuationController"]);

$controller = new AdminProductValuationController();
$data = $controller->index();

$valuations = $data['valuations'];
$page = $data['page'];
$totalPages = $data['totalPages'];
$search = $data['search'];
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Quản lý Đánh giá"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
    <div class="relative flex min-h-screen w-full">
        <?php Import::layout('AdminSidebar', ["active" => "valuations"]); ?>

        <main class="flex-1 p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <header class="mb-8">
                    <h1 class="text-3xl font-bold text-primary">Quản lý Đánh giá</h1>
                    <p class="text-gray-500 mt-1">Xem và kiểm duyệt đánh giá từ khách hàng.</p>
                </header>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-200 bg-gray-50/50">
                        <form method="GET" action="" class="flex w-full md:w-1/2">
                            <div class="relative w-full">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                    <span class="material-symbols-outlined">search</span>
                                </span>
                                <input 
                                    name="q" 
                                    value="<?= htmlspecialchars($search) ?>" 
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm" 
                                    placeholder="Tìm theo nội dung, tên khách hoặc tên sản phẩm..."
                                >
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">ID</th>
                                    <th class="px-6 py-3 font-semibold">Sản phẩm</th>
                                    <th class="px-6 py-3 font-semibold">Khách hàng</th>
                                    <th class="px-6 py-3 font-semibold">Đánh giá</th>
                                    <th class="px-6 py-3 font-semibold text-right">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (empty($valuations)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            Không có đánh giá nào.
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($valuations as $val): ?>
                                <tr class="bg-white hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-mono text-gray-500">#<?= $val['id'] ?></td>
                                    
                                    <td class="px-6 py-4 max-w-xs">
                                        <a href="/app/views/pages/ProductDetail.php?id=<?= $val['product_id'] ?>" target="_blank" class="font-medium text-primary hover:underline line-clamp-2">
                                            <?= htmlspecialchars($val['product_name']) ?>
                                        </a>
                                        <div class="text-xs text-gray-400 mt-1">
                                            <?= date('d/m/Y H:i', strtotime($val['created_at'])) ?>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($val['user_name']) ?></div>
                                        <div class="text-xs text-gray-400">Đơn hàng #<?= $val['order_id'] ?></div>
                                    </td>

                                    <td class="px-6 py-4 max-w-sm">
                                        <div class="flex items-center gap-1 text-orange-400 mb-1">
                                            <?php for($i=1; $i<=5; $i++): ?>
                                                <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' <?= $i <= $val['star_rate'] ? 1 : 0 ?>">star</span>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="text-gray-700 text-sm italic">"<?= htmlspecialchars($val['content']) ?>"</p>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <form method="POST" action="/app/controllers/AdminProductValuationController.php?action=delete" 
                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này không?');">
                                            <input type="hidden" name="id" value="<?= $val['id'] ?>">
                                            <button type="submit" 
                                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" 
                                                    title="Xóa đánh giá">
                                                <span class="material-symbols-outlined text-xl">delete</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                    <div class="flex items-center justify-between p-4 border-t border-gray-200 bg-gray-50">
                        <span class="text-sm text-gray-600">
                            Trang <span class="font-semibold"><?= $page ?></span> / <?= $totalPages ?>
                        </span>
                        
                        <div class="inline-flex items-center -space-x-px text-sm">
                            <?php $prev = $page > 1 ? $page - 1 : 1; ?>
                            <a href="?page=<?= $prev ?>&q=<?= urlencode($search) ?>" class="px-3 h-8 flex items-center justify-center border border-gray-300 rounded-l-lg bg-white hover:bg-gray-100 text-gray-500">
                                <span class="material-symbols-outlined text-base">chevron_left</span>
                            </a>
                            
                            <?php $next = $page < $totalPages ? $page + 1 : $totalPages; ?>
                            <a href="?page=<?= $next ?>&q=<?= urlencode($search) ?>" class="px-3 h-8 flex items-center justify-center border border-gray-300 rounded-r-lg bg-white hover:bg-gray-100 text-gray-500">
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