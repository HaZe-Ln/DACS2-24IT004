<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["ProductController"]);
Import::repositories(["ProductCategoryRepository", "BranchRepository"]);

$error = null;
$categories = ProductCategoryRepository::all(null, 1, 200);
$branches = BranchRepository::all(null, 1, 200);

function uploadProductImages(): array
{
  if (empty($_FILES["images"]) || !is_array($_FILES["images"]["name"])) {
    return [];
  }
  $urls = [];
  $files = $_FILES["images"];
  $uploadDir = $_SERVER["DOCUMENT_ROOT"] . "/uploads/products";
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
  }
  $allowed = ["image/jpeg", "image/png", "image/webp", "image/gif"];
  foreach ($files["name"] as $idx => $name) {
    if ($files["error"][$idx] !== UPLOAD_ERR_OK) continue;
    $tmp = $files["tmp_name"][$idx];
    $mime = mime_content_type($tmp);
    if (!in_array($mime, $allowed, true)) continue;
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $safe = uniqid("prod_", true) . "." . strtolower($ext);
    $dest = $uploadDir . "/" . $safe;
    if (move_uploaded_file($tmp, $dest)) {
      $urls[] = "/uploads/products/" . $safe;
    }
  }
  return $urls;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $payload = [
    "name" => $_POST["name"] ?? "",
    "description" => $_POST["description"] ?? "",
    "price_current" => $_POST["price_current"] ?? 0,
    "price_original" => $_POST["price_original"] ?? 0,
    "discount_percent" => $_POST["discount_percent"] ?? 0,
    "quantity" => $_POST["quantity"] ?? 0,
    "product_category_id" => $_POST["product_category_id"] ?? null,
    "branch_id" => $_POST["branch_id"] ?? null,
    "images" => uploadProductImages(),
  ];
  try {
    $pc = new ProductController();
    $pc->createProduct($payload);
    header("Location: /app/views/pages/admin/ProductManagement.php");
    exit;
  } catch (\Exception $e) {
    $error = $e->getMessage();
  }
}
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Thêm sản phẩm"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
  <div class="relative flex min-h-screen w-full flex-row overflow-x-hidden">
    <?php Import::layout('AdminSidebar', ["active" => "products"]); ?>

    <main class="flex-1 p-6 lg:p-10">
      <div class="max-w-7xl mx-auto">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
          <h1 class="text-3xl font-bold text-primary">Thêm sản phẩm mới</h1>
          <div class="flex items-center gap-3">
            <a class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50" href="/app/views/pages/admin/ProductManagement.php">Hủy</a>
            <button form="product-form" class="px-4 py-2 text-sm font-medium text-white bg-primary border border-transparent rounded-lg shadow-sm hover:bg-primary/90">Lưu Sản phẩm</button>
          </div>
        </div>

        <?php if (!empty($error)): ?>
          <p class="text-sm text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form id="product-form" method="POST" action="/app/views/pages/admin/CreateProduct.php" enctype="multipart/form-data">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 flex flex-col gap-8">
              <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 space-y-6">
                  <h2 class="text-lg font-semibold text-gray-900">Thông tin chung</h2>
                  <div class="space-y-4">
                    <label class="flex flex-col gap-2">
                      <span class="text-sm font-medium text-gray-800">Tên sản phẩm</span>
                      <input name="name" class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm" placeholder="Ví dụ: Đàn Guitar Acoustic" required />
                    </label>
                    <label class="flex flex-col gap-2">
                      <span class="text-sm font-medium text-gray-800">Mô tả</span>
                      <textarea name="description" class="form-textarea w-full rounded-lg border border-gray-300 bg-white min-h-40 px-4 py-3 text-sm" placeholder="Nhập mô tả chi tiết cho sản phẩm..."></textarea>
                    </label>
                  </div>
                </div>
              </div>

              <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 space-y-4">
                  <h2 class="text-lg font-semibold text-gray-900">Hình ảnh sản phẩm</h2>
                  <div class="border border-dashed border-gray-300 rounded-lg p-4 bg-gray-50">
                    <p class="text-sm text-gray-600 mb-2">Chọn nhiều ảnh (PNG, JPG, WEBP, GIF, tối đa 5MB mỗi ảnh). Ảnh đầu tiên sẽ là ảnh chính, bạn có thể chọn lại trong trang sửa.</p>
                    <input type="file" name="images[]" accept="image/*" multiple class="text-sm text-gray-700" />
                  </div>
                </div>
              </div>
            </div>

            <div class="lg:col-span-1 flex flex-col gap-8">
              <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 space-y-4">
                  <h2 class="text-lg font-semibold text-gray-900">Giá &amp; Tồn kho</h2>
                  <div class="grid grid-cols-1 gap-4">
                    <label class="flex flex-col gap-2">
                      <span class="text-sm font-medium text-gray-800">Giá gốc (VND)</span>
                      <input name="price_original" class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm" type="number" min="0" step="0.01" placeholder="0" />
                    </label>
                    <label class="flex flex-col gap-2">
                      <span class="text-sm font-medium text-gray-800">Giá bán (VND)</span>
                      <input name="price_current" class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm" type="number" min="0" step="0.01" placeholder="0" />
                    </label>
                    <label class="flex flex-col gap-2">
                      <span class="text-sm font-medium text-gray-800">% Giảm giá</span>
                      <input name="discount_percent" class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm" type="number" min="0" max="100" step="1" placeholder="0" />
                    </label>
                    <label class="flex flex-col gap-2">
                      <span class="text-sm font-medium text-gray-800">Số lượng tồn kho (SKU)</span>
                      <input name="quantity" class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm" type="number" min="0" placeholder="100" />
                    </label>
                  </div>
                </div>
              </div>

              <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 space-y-6">
                  <h2 class="text-lg font-semibold text-gray-900">Phân loại</h2>
                  <div class="space-y-4">
                    <label class="flex flex-col gap-2">
                      <span class="text-sm font-medium text-gray-800">Danh mục</span>
                      <select name="product_category_id" class="form-select w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm">
                        <option value="">Chọn danh mục</option>
                        <?php foreach ($categories as $cat): ?>
                          <option value="<?= htmlspecialchars($cat->id) ?>"><?= htmlspecialchars($cat->name) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </label>
                    <label class="flex flex-col gap-2">
                      <span class="text-sm font-medium text-gray-800">Thương hiệu</span>
                      <select name="branch_id" class="form-select w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm">
                        <option value="">Chọn thương hiệu</option>
                        <?php foreach ($branches as $b): ?>
                          <option value="<?= htmlspecialchars($b->id) ?>"><?= htmlspecialchars($b->name) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>
