<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/helpers/Import.php";
Import::repositories(["ProductCategoryRepository"]);
$error = $error ?? null; // có thể được set từ controller

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = trim($_POST["name"] ?? "");
  if ($name === "") {
    $error = "Tên danh mục không được để trống.";
  } else {
    ProductCategoryRepository::create(["name" => $name]);
    header("Location: /app/views/pages/admin/CategoryManagement.php");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="vi" class="light">
<?php Import::layout("Head", ["title" => "Thêm Danh mục"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
  <div class="relative flex min-h-screen">
    <?php Import::layout("AdminSidebar", ["active" => "categories"]); ?>

    <main class="flex-1 p-6 lg:p-10">
      <div class="max-w-5xl mx-auto">
        <div class="flex flex-wrap justify-between gap-3 mb-8">
          <div class="flex flex-col gap-1 min-w-64">
            <h1 class="text-3xl font-bold tracking-tight">Thêm Danh mục Mới</h1>
            <p class="text-sm text-gray-500">Điền thông tin chi tiết để tạo một danh mục mới.</p>
          </div>
          <div class="flex items-center gap-3">
            <a href="/app/views/pages/admin/CategoryManagement.php" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
              Hủy
            </a>
            <button form="category-create-form" class="px-4 py-2 text-sm font-medium text-white bg-primary border border-transparent rounded-lg shadow-sm hover:bg-primary/90">
              Lưu Danh mục
            </button>
          </div>
        </div>

        <form id="category-create-form" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 space-y-8" method="POST" action="/app/views/pages/admin/CreateCategory.php">
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
            />
          </div>

          <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <div>
              <p class="text-base font-medium">Trạng thái</p>
              <p class="text-sm text-gray-500">Bật để danh mục được hiển thị.</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" class="sr-only peer" checked />
              <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-primary peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
            </label>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>
