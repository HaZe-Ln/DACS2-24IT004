<?php
$pathCurrent = $_SERVER["PHP_SELF"];
$active = $active ?? 'dashboard';

$menuItems = [
  ["slug" => "dashboard", "icon" => "dashboard", "label" => "Dashboard", "href" => "/app/views/pages/admin/Dashboard.php"],
  ["slug" => "products", "icon" => "music_note", "label" => "Sản phẩm", "href" => "/app/views/pages/admin/ProductManagement.php"],
  ["slug" => "categories", "icon" => "sell", "label" => "Danh mục", "href" => "/app/views/pages/admin/CategoryManagement.php"],
  ["slug" => "brands", "icon" => "branding_watermark", "label" => "Thương hiệu", "href" => "/app/views/pages/admin/BrandManagement.php"],
  ["slug" => "orders", "icon" => "shopping_bag", "label" => "Đơn hàng", "href" => "/app/views/pages/admin/OrderManagement.php"],
  ["slug" => "users", "icon" => "group", "label" => "Người dùng", "href" => "/app/views/pages/admin/UserManagement.php"],
  ["slug" => "posts", "icon" => "article", "label" => "Bài viết", "href" => "/app/views/pages/admin/PostManagement.php"],
  ["slug" => "valuations", "icon" => "reviews", "label" => "Đánh giá", "href" => "/app/views/pages/admin/ProductValuationManagement.php"]
];
?>

<aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col justify-between">
  <div class="p-4 flex flex-col gap-4">
    <div class="flex items-center gap-3 px-2">
      <svg class="size-6 text-primary" fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
        <path clip-rule="evenodd" d="M24 4H42V17.3333V30.6667H24V44H6V30.6667V17.3333H24V4Z" fill="currentColor" fill-rule="evenodd"></path>
      </svg>
      <h2 class="text-xl font-bold text-primary">AMusic Admin</h2>
    </div>
    <nav class="flex flex-col gap-1">
      <?php foreach ($menuItems as $item):
        $isActive = $active === $item["slug"];
      ?>
        <a href="<?= htmlspecialchars($item["href"]) ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $isActive ? 'bg-gray-100 text-primary' : 'text-gray-700 hover:bg-gray-100' ?>">
          <span class="material-symbols-outlined text-2xl"><?= $item["icon"] ?></span>
          <span class="text-sm font-medium"><?= htmlspecialchars($item["label"]) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
  </div>
  
  <div class="p-4 border-t border-gray-200">
    <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100" href="/app/views/pages/auth/Logout.php">
      <span class="material-symbols-outlined text-2xl">logout</span>
      <span class="text-sm font-medium">Đăng xuất</span>
    </a>
  </div>
</aside>