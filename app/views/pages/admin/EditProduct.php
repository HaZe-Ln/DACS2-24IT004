<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["ProductController"]);
Import::repositories(["ProductRepository", "ProductCategoryRepository", "BranchRepository"]);

$error = null;
$productId = $_GET["id"] ?? null;
$product = $productId ? ProductRepository::getById($productId) : null;
$categories = ProductCategoryRepository::all(null, 1, 200);
$branches = BranchRepository::all(null, 1, 200);

function uploadProductImagesEdit(): array
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

if ($_SERVER["REQUEST_METHOD"] === "POST" && $product) {
  $payload = [
    "name" => $_POST["name"] ?? "",
    "description" => $_POST["description"] ?? "",
    "price_current" => $_POST["price_current"] ?? 0,
    "price_original" => $_POST["price_original"] ?? 0,
    "discount_percent" => $_POST["discount_percent"] ?? 0,
    "quantity" => $_POST["quantity"] ?? 0,
    "product_category_id" => $_POST["product_category_id"] ?? null,
    "branch_id" => $_POST["branch_id"] ?? null,
  ];
  $newImages = uploadProductImagesEdit();
  // nếu không upload mới thì giữ danh sách cũ
  if (!empty($newImages)) {
    $payload["images"] = $newImages;
  } else {
    $payload["images"] = array_map(fn($img) => $img->url ?? null, $product->images ?? []);
  }

  try {
    $pc = new ProductController();
    $pc->updateProduct($productId, $payload);
    header("Location: /app/views/pages/admin/ProductManagement.php");
    exit;
  } catch (\Exception $e) {
    $error = $e->getMessage();
  }
}
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Sửa sản phẩm"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
  <div class="relative flex min-h-screen w-full flex-row overflow-x-hidden">
    <?php Import::layout('AdminSidebar', ["active" => "products"]); ?>

    <main class="flex-1 p-6 lg:p-10">
      <div class="max-w-7xl mx-auto">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
          <div class="flex flex-col">
            <h1 class="text-3xl font-bold text-primary">Sửa sản phẩm</h1>
            <p class="text-sm text-gray-500">ID #<?= htmlspecialchars($productId ?? '—') ?></p>
          </div>
          <div class="flex items-center gap-3">
            <a class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50" href="/app/views/pages/admin/ProductManagement.php">Hủy</a>
            <button form="product-form" class="px-4 py-2 text-sm font-medium text-white bg-primary border border-transparent rounded-lg shadow-sm hover:bg-primary/90">Lưu thay đổi</button>
          </div>
        </div>

        <?php if (!empty($error)): ?>
          <p class="text-sm text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if (!$product): ?>
          <p class="text-sm text-red-600">Không tìm thấy sản phẩm.</p>
        <?php else: ?>

          <form id="product-form" method="POST" action="/app/views/pages/admin/EditProduct.php?id=<?= urlencode($productId) ?>" enctype="multipart/form-data">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
              <div class="lg:col-span-2 flex flex-col gap-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                  <div class="p-6 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900">Thông tin chung</h2>
                    <div class="space-y-4">
                      <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-gray-800">Tên sản phẩm</span>
                        <input name="name" value="<?= htmlspecialchars($product->name ?? '') ?>" class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm" placeholder="Ví dụ: Đàn Guitar Acoustic" required />
                      </label>
                      <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-gray-800">Mô tả</span>
                        <textarea name="description" class="form-textarea w-full rounded-lg border border-gray-300 bg-white min-h-40 px-4 py-3 text-sm" placeholder="Nhập mô tả chi tiết cho sản phẩm..."><?= htmlspecialchars($product->description ?? '') ?></textarea>
                      </label>
                    </div>
                  </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                  <div class="p-6 space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900">Giá &amp; Tồn kho</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-gray-800">Giá gốc (VND)</span>
                        <input name="price_original" value="<?= htmlspecialchars($product->price_original ?? 0) ?>" class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm" type="number" min="0" step="0.01" placeholder="0" />
                      </label>
                      <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-gray-800">Giá bán (VND)</span>
                        <input name="price_current" value="<?= htmlspecialchars($product->price_current ?? 0) ?>" class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm" type="number" min="0" step="0.01" placeholder="0" />
                      </label>
                      <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-gray-800">% Giảm giá</span>
                        <input name="discount_percent" value="<?= htmlspecialchars($product->discount_percent ?? 0) ?>" class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm" type="number" min="0" max="100" step="1" placeholder="0" />
                      </label>
                      <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-gray-800">Số lượng tồn kho (SKU)</span>
                        <input name="quantity" value="<?= htmlspecialchars($product->quantity ?? 0) ?>" class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm" type="number" min="0" placeholder="100" />
                      </label>
                    </div>
                  </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                  <div class="p-6 space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900">Hình ảnh sản phẩm</h2>
                    <?php $currentImages = $product->images ?? []; ?>
                    <?php if (!empty($currentImages)): ?>
                      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-4">
                        <?php foreach ($currentImages as $index => $img): ?>
                          <?php $url = $img->url ?? ''; ?>
                          <div class="relative block rounded-lg border border-gray-200 overflow-hidden group">
                            <div class="w-full aspect-square bg-cover bg-center" style="background-image: url('<?= htmlspecialchars($url) ?>');"></div>
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition"></div>
                            <div class="absolute top-2 left-2 bg-white/90 rounded-full p-1 shadow flex items-center gap-1">
                              <input type="radio" name="main_image" value="<?= htmlspecialchars($url) ?>" <?= ($product->images[0]->url ?? '') === $url ? 'checked' : '' ?> title="Chọn ảnh chính" />
                              <span class="text-[10px] text-gray-700">Chính</span>
                            </div>
                            <label class="absolute top-2 right-2 bg-white/90 rounded-full p-1 shadow cursor-pointer flex items-center justify-center">
                              <span class="material-symbols-outlined text-red-500 text-sm">delete</span>
                              <input type="checkbox" name="remove_images[]" value="<?= htmlspecialchars($url) ?>" class="sr-only" />
                            </label>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      <p class="text-sm text-gray-500">Chưa có ảnh.</p>
                    <?php endif; ?>
                    <div class="border border-dashed border-gray-300 rounded-lg p-4 bg-gray-50">
                      <p class="text-sm text-gray-600 mb-2">Upload thêm ảnh (ảnh hiện tại sẽ được giữ, trừ khi bạn chọn Xóa).</p>
                      <p class="text-xs text-gray-500 mb-2">Chọn ảnh chính bằng radio ở danh sách hiện có; nếu upload mới và muốn chọn làm chính, chọn radio sau khi lưu.</p>
                      <input type="file" name="images[]" accept="image/*" multiple class="text-sm text-gray-700" />
                    </div>
                  </div>
                </div>
              </div>

              <div class="lg:col-span-1 flex flex-col gap-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                  <div class="p-6 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900">Phân loại</h2>
                    <div class="space-y-4">
                      <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-gray-800">Danh mục</span>
                        <select name="product_category_id" class="form-select w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm">
                          <option value="">Chọn danh mục</option>
                          <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat->id) ?>" <?= ($product->product_category_id ?? null) == $cat->id ? 'selected' : '' ?>><?= htmlspecialchars($cat->name) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </label>
                      <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-gray-800">Thương hiệu</span>
                        <select name="branch_id" class="form-select w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm">
                          <option value="">Chọn thương hiệu</option>
                          <?php foreach ($branches as $b): ?>
                            <option value="<?= htmlspecialchars($b->id) ?>" <?= ($product->branch_id ?? null) == $b->id ? 'selected' : '' ?>><?= htmlspecialchars($b->name) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>
