<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/helpers/Import.php";
Import::repositories(["ProductCategoryRepository"]);
// Nếu không được truyền từ controller, fallback lấy nhanh theo id để tránh lỗi
if (!isset($category)) {
  $categoryId = $_GET["id"] ?? null;
  $category = $categoryId ? ProductCategoryRepository::findById($categoryId) : null;
}
$categoryId = $categoryId ?? ($_GET["id"] ?? null);
$error = $error ?? null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && $category) {
  $name = trim($_POST["name"] ?? "");
  if ($name === "") {
    $error = "Tên danh mục không được để trống.";
  } else {
    ProductCategoryRepository::update($categoryId, ["name" => $name]);
    header("Location: /app/views/pages/admin/CategoryManagement.php");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="vi" class="light">
<?php Import::layout("Head", ["title" => "Chỉnh sửa Danh mục"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
  <div class="relative flex min-h-screen">
    <?php Import::layout("AdminSidebar", ["active" => "categories"]); ?>

    <main class="flex-1 p-6 lg:p-10">
      <div class="max-w-5xl mx-auto">
        <div class="flex flex-wrap justify-between gap-3 mb-8">
          <div class="flex flex-col gap-1 min-w-64">
            <h1 class="text-3xl font-bold tracking-tight">Chỉnh sửa Danh mục</h1>
            <p class="text-sm text-gray-500">
              <?= $category ? "Cập nhật thông tin danh mục #" . htmlspecialchars($category->id) : "Không tìm thấy danh mục" ?>
            </p>
          </div>
          <div class="flex items-center gap-3">
            <a href="/app/views/pages/admin/CategoryManagement.php" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
              Hủy
            </a>
            <button form="category-edit-form" class="px-4 py-2 text-sm font-medium text-white bg-primary border border-transparent rounded-lg shadow-sm hover:bg-primary/90" <?= $category ? "" : "disabled" ?>>
              Lưu Thay đổi
            </button>
          </div>
        </div>

        <form id="category-edit-form" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 space-y-8" method="POST" action="/app/views/pages/admin/EditCategory.php?id=<?= urlencode($categoryId) ?>">
          <?php if (!empty($error)): ?>
            <p class="text-sm text-red-600"><?= htmlspecialchars($error) ?></p>
          <?php endif; ?>
          <div class="flex flex-col gap-2">
            <label class="text-base font-medium" for="category-name">Tên danh mục</label>
            <input
              id="category-name"
              name="name"
              class="form-input h-12 rounded-lg border border-gray-300 focus:ring-primary focus:border-primary"
              placeholder="Nhập tên danh mục"
              value="<?= htmlspecialchars($category->name ?? '') ?>"
              <?= $category ? "" : "disabled" ?>
            />
          </div>

          <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <div>
              <p class="text-base font-medium">Trạng thái</p>
              <p class="text-sm text-gray-500">Bật để danh mục được hiển thị.</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" class="sr-only peer" <?= $category ? "checked" : "" ?> <?= $category ? "" : "disabled" ?> />
              <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-primary peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
            </label>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>
