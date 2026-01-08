<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';

// Fallback nếu không đi qua controller
if (!isset($items)) {
  Import::repositories(["BranchRepository"]);
  if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete") {
    $deleteId = $_POST["id"] ?? null;
    if ($deleteId) {
      BranchRepository::delete((int)$deleteId);
    }
    header("Location: /app/views/pages/admin/BrandManagement.php");
    exit;
  }
  $search = $_GET["q"] ?? null;
  $page = max(1, (int)($_GET["page"] ?? 1));
  $limit = max(1, (int)($_GET["limit"] ?? 10));
  $items = BranchRepository::all($search, $page, $limit);
  $total = BranchRepository::countAll($search);
}

$page = $page ?? 1;
$limit = $limit ?? 10;
$search = $search ?? null;
$total = $total ?? count($items ?? []);
$totalPages = max(1, (int)ceil($total / $limit));
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Quản lý Thương hiệu"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
  <div class="relative flex min-h-screen w-full">
    <?php Import::layout('AdminSidebar', ["active" => "brands"]); ?>

    <main class="flex-1 flex flex-col p-6 lg:p-8">
      <div class="w-full max-w-7xl mx-auto">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
          <h1 class="text-3xl font-bold text-primary">Quản lý Thương hiệu</h1>
          <a href="/app/views/pages/admin/CreateBrand.php" class="flex items-center justify-center gap-2 rounded-lg h-10 bg-primary text-white text-sm font-bold px-4 shadow-sm hover:bg-primary/90 transition-colors">
            <span class="material-symbols-outlined text-xl">add</span>
            <span class="truncate">Thêm Thương hiệu</span>
          </a>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
          <div class="w-full md:w-1/2 lg:w-1/3">
            <label class="flex flex-col w-full">
              <form class="flex w-full items-stretch rounded-lg h-10 bg-white border border-gray-200" method="GET" action="/app/views/pages/admin/BrandManagement.php">
                <div class="text-gray-500 flex items-center justify-center pl-3">
                  <span class="material-symbols-outlined text-xl">search</span>
                </div>
                <input
                  class="form-input flex w-full min-w-0 flex-1 rounded-r-lg border-none bg-transparent h-full placeholder:text-gray-500 px-3 text-sm"
                  placeholder="Tìm kiếm theo tên thương hiệu..."
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
                  <th class="px-6 py-3">Tên Thương hiệu</th>
                  <th class="px-6 py-3">Địa chỉ</th>
                  <th class="px-6 py-3 text-left">Mô tả</th> <th class="px-6 py-3 text-right">Hành động</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <?php foreach ($items as $brand): ?>
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-mono text-gray-600 whitespace-nowrap"><?= htmlspecialchars($brand->id) ?></td>
                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"><?= htmlspecialchars($brand->name) ?></td>
                    <td class="px-6 py-4 text-gray-600 whitespace-nowrap"><?= htmlspecialchars($brand->address ?? '') ?></td>
                    
                    <td class="px-6 py-4 text-gray-600">
                        <div class="line-clamp-2 max-w-sm" title="<?= htmlspecialchars($brand->description ?? '') ?>">
                            <?= htmlspecialchars($brand->description ?? '') ?>
                        </div>
                    </td>

                    <td class="px-6 py-4 text-right">
                      <div class="flex justify-end items-center gap-2">
                        <a href="/app/views/pages/admin/EditBrand.php?id=<?= urlencode($brand->id) ?>" class="p-2 rounded-lg text-yellow-600 hover:bg-yellow-500/10 transition-colors">
                          <span class="material-symbols-outlined text-xl">edit</span>
                        </a>
                        <form method="POST" action="/app/views/pages/admin/BrandManagement.php" onsubmit="return confirm('Bạn có chắc chắn xóa thương hiệu này?');">
                          <input type="hidden" name="action" value="delete" />
                          <input type="hidden" name="id" value="<?= htmlspecialchars($brand->id) ?>" />
                          <button class="p-2 rounded-lg text-red-600 hover:bg-red-500/10 transition-colors" type="submit">
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
        </div>

        <div class="flex items-center justify-between mt-6 px-1">
          <p class="text-sm text-gray-600">
            Hiển thị trang <span class="font-semibold text-gray-800"><?= $page ?></span> /
            <span class="font-semibold text-gray-800"><?= $totalPages ?></span>,
            tổng <span class="font-semibold text-gray-800"><?= (int)$total ?></span> kết quả
          </p>
          <div class="flex items-center gap-1">
            <?php
            $listUrl = "/app/views/pages/admin/BrandManagement.php";
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