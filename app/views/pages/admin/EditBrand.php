<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["BranchRepository"]);

$brandId = $_GET["id"] ?? null;
$brand = $brandId ? BranchRepository::findById($brandId) : null;
$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && $brand) {
  $name = trim($_POST["name"] ?? "");
  $address = trim($_POST["address"] ?? "");
  $desc = trim($_POST["description"] ?? "");
  if ($name === "") {
    $error = "Tên thương hiệu không được để trống.";
  } else {
    BranchRepository::update($brandId, [
      "name" => $name,
      "address" => $address,
      "description" => $desc,
      "image_url" => null, // logo không còn dùng
    ]);
    header("Location: /app/views/pages/admin/BrandManagement.php");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="vi" class="light">
<?php Import::layout('Head', ["title" => "Chỉnh sửa Thương hiệu"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
  <div class="relative flex min-h-screen">
    <?php Import::layout('AdminSidebar', ["active" => "brands"]); ?>

    <main class="flex-1 p-6 lg:p-10">
      <div class="max-w-5xl mx-auto">
        <div class="flex flex-wrap justify-between gap-3 mb-8">
          <div class="flex flex-col gap-1 min-w-64">
            <h1 class="text-3xl font-bold tracking-tight">Chỉnh sửa Thương hiệu</h1>
            <p class="text-sm text-gray-500">
              <?= $brand ? "Cập nhật thương hiệu #" . htmlspecialchars($brand->id) : "Không tìm thấy thương hiệu" ?>
            </p>
          </div>
          <div class="flex items-center gap-3">
            <a href="/app/views/pages/admin/BrandManagement.php" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
              Hủy
            </a>
            <button form="brand-edit-form" class="px-4 py-2 text-sm font-medium text-white bg-primary border border-transparent rounded-lg shadow-sm hover:bg-primary/90" <?= $brand ? "" : "disabled" ?>>
              Lưu Thay đổi
            </button>
          </div>
        </div>

        <form id="brand-edit-form" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 space-y-8" method="POST" action="/app/views/pages/admin/EditBrand.php?id=<?= urlencode($brandId) ?>">
          <?php if (!empty($error)): ?>
            <p class="text-sm text-red-600"><?= htmlspecialchars($error) ?></p>
          <?php endif; ?>

          <div class="flex flex-col gap-2">
            <label class="text-base font-medium" for="brand-name">Tên thương hiệu</label>
            <input
              id="brand-name"
              name="name"
              class="form-input h-12 rounded-lg border border-gray-300 focus:ring-primary focus:border-primary"
              placeholder="Nhập tên thương hiệu"
              value="<?= htmlspecialchars($brand->name ?? '') ?>"
              <?= $brand ? "" : "disabled" ?>
            />
          </div>

          <div class="flex flex-col gap-2">
            <label class="text-base font-medium" for="brand-address">Địa chỉ</label>
            <input
              id="brand-address"
              name="address"
              class="form-input h-12 rounded-lg border border-gray-300 focus:ring-primary focus:border-primary"
              placeholder="Nhập địa chỉ (nếu có)"
              value="<?= htmlspecialchars($brand->address ?? '') ?>"
              <?= $brand ? "" : "disabled" ?>
            />
          </div>

          <div class="flex flex-col gap-2">
            <label class="text-base font-medium" for="brand-description">Mô tả</label>
            <textarea
              id="brand-description"
              name="description"
              rows="4"
              class="form-textarea rounded-lg border border-gray-300 focus:ring-primary focus:border-primary"
              placeholder="Mô tả ngắn gọn"
              <?= $brand ? "" : "disabled" ?>
            ><?= htmlspecialchars($brand->description ?? '') ?></textarea>
          </div>

          <!-- Logo section removed -->
        </form>
      </div>
    </main>
  </div>
</body>
</html>
