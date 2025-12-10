<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["BranchRepository"]);

$error = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = trim($_POST["name"] ?? "");
  $address = trim($_POST["address"] ?? "");
  $desc = trim($_POST["description"] ?? "");
  if ($name === "") {
    $error = "Tên thương hiệu không được để trống.";
  } else {
    BranchRepository::create([
      "name" => $name,
      "address" => $address,
      "description" => $desc,
      // Không dùng logo nữa
      "image_url" => null,
    ]);
    header("Location: /app/views/pages/admin/BrandManagement.php");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="vi" class="light">
<?php Import::layout('Head', ["title" => "Thêm Thương hiệu"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
  <div class="relative flex min-h-screen w-full">
    <?php Import::layout('AdminSidebar', ["active" => "brands"]); ?>

    <main class="flex-1 p-8">
      <div class="max-w-7xl mx-auto">
        <!-- Breadcrumbs -->
        <div class="flex flex-wrap gap-2 mb-6 text-sm text-gray-500">
          <a class="hover:text-primary" href="/app/views/pages/admin/BrandManagement.php">Thương hiệu</a>
          <span>/</span>
          <span class="text-gray-900">Thêm mới</span>
        </div>

        <!-- Heading -->
        <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
          <div class="flex flex-col gap-2">
            <h1 class="text-4xl font-black leading-tight text-gray-900 tracking-[-0.033em]">Thêm Thương Hiệu Mới</h1>
            <p class="text-base text-gray-600">Điền thông tin chi tiết để thêm một thương hiệu nhạc cụ mới vào hệ thống.</p>
          </div>
          <div class="flex items-center gap-3">
            <a href="/app/views/pages/admin/BrandManagement.php" class="px-6 py-3 rounded-lg text-gray-800 bg-gray-200 hover:bg-gray-300 text-base font-medium">Hủy</a>
            <button form="brand-create-form" class="px-6 py-3 rounded-lg text-white bg-primary hover:bg-primary/90 text-base font-medium flex items-center gap-2">
              <span class="material-symbols-outlined">save</span>
              Lưu Thay Đổi
            </button>
          </div>
        </div>

        <!-- Form -->
        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
          <?php if (!empty($error)): ?>
            <p class="text-sm text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
          <?php endif; ?>

          <form id="brand-create-form" method="POST" action="/app/views/pages/admin/CreateBrand.php" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <label class="flex flex-col w-full gap-2">
                <span class="text-base font-medium text-gray-900">Tên thương hiệu</span>
                <input name="name" class="form-input h-14 rounded-lg border border-gray-300 bg-white text-gray-900 focus:ring-2 focus:ring-primary/50 focus:outline-0 placeholder:text-gray-500 p-[15px]" placeholder="Ví dụ: Yamaha, Fender, Roland" />
              </label>

              <label class="flex flex-col w-full gap-2">
                <span class="text-base font-medium text-gray-900">Địa chỉ</span>
                <input name="address" class="form-input h-14 rounded-lg border border-gray-300 bg-white text-gray-900 focus:ring-2 focus:ring-primary/50 focus:outline-0 placeholder:text-gray-500 p-[15px]" placeholder="Nhập địa chỉ (nếu có)" />
              </label>
            </div>

            <label class="flex flex-col w-full gap-2">
              <span class="text-base font-medium text-gray-900">Mô tả thương hiệu</span>
              <textarea name="description" rows="6" class="form-textarea rounded-lg border border-gray-300 bg-white text-gray-900 focus:ring-2 focus:ring-primary/50 focus:outline-0 placeholder:text-gray-500 p-[15px]" placeholder="Nhập mô tả chi tiết về thương hiệu"></textarea>
            </label>
          </form>
        </div>

        <footer class="text-center mt-12 py-4">
          <p class="text-sm text-gray-500">© 2024 Nhạc Cụ Pro. All rights reserved.</p>
        </footer>
      </div>
    </main>
  </div>
</body>
</html>
