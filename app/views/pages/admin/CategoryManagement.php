<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
// Import Controller mới
Import::controllers(["AdminProductCategoryController"]);

// 1. Gọi Controller
$controller = new AdminProductCategoryController();
$data = $controller->index();

// 2. Hứng dữ liệu
$items      = $data['items'];
$page       = $data['page'];
$totalPages = $data['totalPages'];
$search     = $data['search'];
$total      = $data['total'];
$limit      = $data['limit'];
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Quản lý danh mục"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
  <div class="relative flex min-h-screen w-full">
    <?php Import::layout('AdminSidebar', ["active" => "categories"]); ?>

    <main class="flex-1 flex flex-col p-6 lg:p-8">
      <div class="w-full max-w-7xl mx-auto">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
          <h1 class="text-3xl font-bold text-primary">Quản lý Danh mục</h1>
          <a href="/app/views/pages/admin/CreateCategory.php" class="flex items-center justify-center gap-2 rounded-lg h-10 bg-primary text-white text-sm font-bold px-4 shadow-sm hover:bg-primary/90 transition-colors">
            <span class="material-symbols-outlined text-xl">add</span>
            <span class="truncate">Thêm Danh mục</span>
          </a>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
          <div class="w-full md:w-1/2 lg:w-1/3">
            <label class="flex flex-col w-full">
              <form class="flex w-full items-stretch rounded-lg h-10 bg-white border border-gray-200" method="GET" action="/app/views/pages/admin/CategoryManagement.php">
                <div class="text-gray-500 flex items-center justify-center pl-3">
                  <span class="material-symbols-outlined text-xl">search</span>
                </div>
                <input
                  class="form-input flex w-full min-w-0 flex-1 rounded-r-lg border-none bg-transparent h-full placeholder:text-gray-500 px-3 text-sm"
                  placeholder="Tìm kiếm theo tên danh mục..."
                  name="q"
                  value="<?= htmlspecialchars($search ?? '') ?>"
                />
              </form>
            </label>
          </div>
        </div>

        <div class="w-full bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-600 uppercase">
                  <th class="px-6 py-3">ID</th>
                  <th class="px-6 py-3">Tên Danh mục</th>
                  <th class="px-6 py-3 text-center">Số sản phẩm</th>
                  <th class="px-6 py-3 text-right">Hành động</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            Không tìm thấy danh mục nào.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $cat): ?>
                      <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-mono text-gray-600 whitespace-nowrap">#<?= htmlspecialchars($cat->id) ?></td>
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"><?= htmlspecialchars($cat->name) ?></td>
                        
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?= $cat->product_count ?? 0 ?>
                            </span>
                        </td>

                        <td class="px-6 py-4 text-right">
                          <div class="flex justify-end items-center gap-2">
                            <a href="/app/views/pages/admin/EditCategory.php?id=<?= urlencode($cat->id) ?>" class="p-2 rounded-lg text-yellow-600 hover:bg-yellow-500/10 transition-colors">
                              <span class="material-symbols-outlined text-xl">edit</span>
                            </a>
                            <form method="POST" action="/app/views/pages/admin/CategoryManagement.php" onsubmit="return confirm('Bạn có chắc chắn xóa danh mục này?');">
                              <input type="hidden" name="action" value="delete" />
                              <input type="hidden" name="id" value="<?= htmlspecialchars($cat->id) ?>" />
                              <button class="p-2 rounded-lg text-red-600 hover:bg-red-500/10 transition-colors" type="submit">
                                <span class="material-symbols-outlined text-xl">delete</span>
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="flex items-center justify-between mt-6 px-1">
          <p class="text-sm text-gray-600">
            Hiển thị trang <span class="font-semibold text-gray-800"><?= $page ?></span> /
            <span class="font-semibold text-gray-800"><?= $totalPages ?></span>,
            tổng <span class="font-semibold text-gray-800"><?= (int)$total ?></span> kết quả
          </p>
          <div class="flex items-center gap-1">
            <?php
            $listUrl = "/app/views/pages/admin/CategoryManagement.php";
            $qs = $search ? "&q=" . urlencode($search) : "";
            ?>
            <a class="inline-flex items-center justify-center size-8 rounded-lg text-gray-500 hover:bg-primary/10 hover:text-primary transition-colors <?= $page <= 1 ? 'pointer-events-none opacity-40' : '' ?>"
              href="<?= $page > 1 ? "{$listUrl}?page=" . ($page - 1) . "&limit={$limit}{$qs}" : '#' ?>">
              <span class="material-symbols-outlined text-xl">chevron_left</span>
            </a>
            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg text-sm font-medium bg-primary text-white">
              <?= $page ?>
            </span>
            <a class="inline-flex items-center justify-center size-8 rounded-lg text-gray-500 hover:bg-primary/10 hover:text-primary transition-colors <?= $page >= $totalPages ? 'pointer-events-none opacity-40' : '' ?>"
              href="<?= $page < $totalPages ? "{$listUrl}?page=" . ($page + 1) . "&limit={$limit}{$qs}" : '#' ?>">
              <span class="material-symbols-outlined text-xl">chevron_right</span>
            </a>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>