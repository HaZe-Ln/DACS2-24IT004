<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["ProductRepository", "ProductCategoryRepository", "BranchRepository"]);
Import::configs(["db/Query"]); // Import Query để xử lý xóa nhanh nếu cần

// --- 1. XỬ LÝ POST (Xóa sản phẩm) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete") {
    $deleteId = $_POST["id"] ?? null;
    if ($deleteId) {
        // Xóa sản phẩm khỏi DB (Xóa ảnh liên quan nếu cần thiết trong logic mở rộng)
        Query::from("products")->delete((int)$deleteId);
    }
    // Refresh trang để cập nhật danh sách
    header("Location: /app/views/pages/admin/ProductManagement.php");
    exit;
}

// --- 2. XỬ LÝ GET (Lọc & Phân trang) ---
$page = max(1, (int)($_GET["page"] ?? 1));
$limit = 10; // Số lượng hiển thị mỗi trang

// Lấy tham số lọc
$search = trim($_GET["q"] ?? "");
$categoryId = $_GET["category_id"] ?? "";
$branchId = $_GET["branch_id"] ?? "";
$priceMin = $_GET["price_min"] ?? "";
$priceMax = $_GET["price_max"] ?? "";
$status = $_GET["status"] ?? "all";

// Chuẩn bị bộ lọc cho Repository
$filters = [
    'search'           => $search, // Lưu ý: Repository cần hỗ trợ key này nếu muốn tìm theo tên
    'product_category' => $categoryId,
    'branch'           => $branchId,
    'price_min'        => $priceMin,
    'price_max'        => $priceMax,
    'status'           => $status
];

// --- 3. LẤY DỮ LIỆU TỪ REPOSITORY ---
// Lấy danh sách sản phẩm
$items = ProductRepository::paginate($page, $limit, $filters);

// Đếm tổng số để tính phân trang
$total = ProductRepository::count($filters);
$totalPages = ceil($total / $limit);

// Lấy dữ liệu cho các thẻ Select (Dropdown)
$categories = ProductCategoryRepository::all(null, 1, 100); // Lấy tối đa 100 danh mục
$branches = BranchRepository::all(null, 1, 100);       // Lấy tối đa 100 thương hiệu

?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Quản lý sản phẩm"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
  <div class="min-h-screen flex">
    <?php Import::layout('AdminSidebar', ["active" => "products"]); ?>

    <main class="flex-1 p-6 lg:p-8">
      <div class="max-w-7xl mx-auto">
        <div class="flex flex-wrap justify-between gap-4 mb-6 items-center">
          <h1 class="text-3xl font-bold text-primary">Quản lý sản phẩm</h1>
          <a href="/app/views/pages/admin/CreateProduct.php" class="flex items-center justify-center gap-2 rounded-lg h-10 bg-primary text-white text-sm font-bold px-4 shadow-sm hover:bg-primary/90">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1, 'wght' 600;">add</span>
            <span>Thêm sản phẩm</span>
          </a>
        </div>

        <form class="flex flex-col md:flex-row gap-4 mb-4 bg-white border border-gray-200 p-4 rounded-lg items-end" method="GET" action="/app/views/pages/admin/ProductManagement.php">
          <div class="flex-1">
            <label class="flex flex-col min-w-40 h-12 w-full">
              <div class="flex w-full items-stretch rounded-lg h-full bg-white border border-gray-200">
                <div class="text-gray-500 flex items-center justify-center pl-4">
                  <span class="material-symbols-outlined">search</span>
                </div>
                <input name="q" value="<?= htmlspecialchars($search) ?>" class="form-input flex w-full min-w-0 flex-1 text-gray-900 focus:outline-0 focus:ring-0 border-none bg-transparent h-full placeholder:text-gray-500 px-4 pl-2 text-base" placeholder="Tìm theo tên hoặc ID..." />
              </div>
            </label>
          </div>
          <select name="category_id" class="h-12 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-900">
            <option value="">Danh mục: Tất cả</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat->id) ?>" <?= ($categoryId == $cat->id) ? 'selected' : '' ?>><?= htmlspecialchars($cat->name) ?></option>
            <?php endforeach; ?>
          </select>
          <select name="branch_id" class="h-12 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-900">
            <option value="">Thương hiệu: Tất cả</option>
            <?php foreach ($branches as $b): ?>
              <option value="<?= htmlspecialchars($b->id) ?>" <?= ($branchId == $b->id) ? 'selected' : '' ?>><?= htmlspecialchars($b->name) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="flex items-center gap-2">
            <input name="price_min" value="<?= htmlspecialchars($priceMin) ?>" type="number" step="0.01" class="h-12 w-28 rounded-lg border border-gray-200 px-3 text-sm" placeholder="Giá từ" />
            <input name="price_max" value="<?= htmlspecialchars($priceMax) ?>" type="number" step="0.01" class="h-12 w-28 rounded-lg border border-gray-200 px-3 text-sm" placeholder="Đến" />
          </div>
          <select name="status" class="h-12 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-900">
            <option value="all" <?= $status === "all" ? "selected" : "" ?>>Trạng thái: Tất cả</option>
            <option value="published" <?= $status === "published" ? "selected" : "" ?>>Hiển thị</option>
            <option value="hidden" <?= $status === "hidden" ? "selected" : "" ?>>Ẩn</option>
          </select>
          <button class="h-12 px-4 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary/90">Lọc</button>
        </form>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
              <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr>
                  <th class="px-6 py-3">ID</th>
                  <th class="px-6 py-3">Ảnh</th>
                  <th class="px-6 py-3">Tên sản phẩm</th>
                  <th class="px-6 py-3">Thương hiệu</th>
                  <th class="px-6 py-3">Danh mục</th>
                  <th class="px-6 py-3">Giá</th>
                  <th class="px-6 py-3">Tồn kho</th>
                  <th class="px-6 py-3">Trạng thái</th>
                  <th class="px-6 py-3 text-right">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            Không tìm thấy sản phẩm nào phù hợp.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($items as $row): ?>
                  <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">#<?= htmlspecialchars($row->id) ?></td>
                    <td class="px-6 py-4">
                      <?php $firstImage = $row->productImages[0]->url ?? null; // Sửa lại key truy cập ảnh nếu cần (productImages hoặc images) ?>
                      <?php if ($firstImage): ?>
                        <div class="w-12 h-12 rounded-md bg-cover bg-center border border-gray-200" style="background-image: url('<?= htmlspecialchars($firstImage) ?>');"></div>
                      <?php else: ?>
                        <div class="w-12 h-12 rounded-md bg-gray-100 flex items-center justify-center text-xs text-gray-400 border border-gray-200">No img</div>
                      <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 font-semibold text-gray-900">
                        <div class="line-clamp-2" title="<?= htmlspecialchars($row->name) ?>">
                            <?= htmlspecialchars($row->name) ?>
                        </div>
                    </td>
                    <td class="px-6 py-4"><?= htmlspecialchars($row->branch->name ?? '—') ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($row->productCategory->name ?? '—') ?></td>
                    <td class="px-6 py-4 font-semibold text-gray-900"><?= number_format($row->price_current ?? 0, 0, ',', '.') ?> đ</td>
                    <td class="px-6 py-4"><?= number_format($row->quantity ?? 0) ?></td>
                    <td class="px-6 py-4">
                      <?php $isPublished = empty($row->deleted_at); ?>
                      <?php if ($isPublished): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Hiển thị</span>
                      <?php else: ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800">Ẩn</span>
                      <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                      <div class="flex justify-end gap-2">
                        <a href="/app/views/pages/admin/EditProduct.php?id=<?= urlencode($row->id) ?>" class="p-2 rounded-md hover:bg-gray-100 text-gray-500 transition-colors" title="Sửa">
                            <span class="material-symbols-outlined text-base">edit</span>
                        </a>
                        <form method="POST" action="/app/views/pages/admin/ProductManagement.php" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                          <input type="hidden" name="action" value="delete" />
                          <input type="hidden" name="id" value="<?= htmlspecialchars($row->id) ?>" />
                          <button class="p-2 rounded-md hover:bg-red-50 text-gray-500 hover:text-red-600 transition-colors" type="submit" title="Xóa">
                            <span class="material-symbols-outlined text-base">delete</span>
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
          <nav aria-label="Table navigation" class="flex items-center justify-between p-4 border-t border-gray-200">
            <span class="text-sm font-normal text-gray-600">
              Trang <span class="font-semibold text-gray-900"><?= $page ?></span> / <span class="font-semibold text-gray-900"><?= $totalPages ?></span> 
              &mdash; Tổng <span class="font-semibold text-gray-900"><?= (int)$total ?></span> sản phẩm
            </span>
            <?php
            // Tạo URL cơ bản giữ nguyên các tham số lọc
            $baseUrl = "/app/views/pages/admin/ProductManagement.php";
            $params = $_GET;
            unset($params['page']); // Bỏ page cũ đi để thay bằng page mới
            $queryString = http_build_query($params);
            $link = "{$baseUrl}?{$queryString}";
            ?>
            <ul class="inline-flex items-center -space-x-px text-sm">
              <li>
                <a class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700 <?= $page <= 1 ? 'pointer-events-none opacity-50' : '' ?>"
                  href="<?= $page > 1 ? "{$link}&page=" . ($page - 1) : '#' ?>">Trước</a>
              </li>
              <li>
                <span class="px-3 py-2 leading-tight text-primary bg-primary/10 border border-primary font-medium"> <?= $page ?> </span>
              </li>
              <li>
                <a class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700 <?= $page >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>"
                  href="<?= $page < $totalPages ? "{$link}&page=" . ($page + 1) : '#' ?>">Tiếp</a>
              </li>
            </ul>
          </nav>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>
</body>
</html>