<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["ProductController"]);

// 1. Gọi Controller
$productController = new ProductController();
$data = $productController->index(); 

// 2. Hứng dữ liệu trả về từ Controller
$products   = $data['products'];
$productCategories = $data['productCategories']; // List object ProductCategory
$branches   = $data['branches'];   // List object Branch
$filters    = $data['filters'];    // Các lựa chọn đang active
$totalPages = $data['totalPages'];
$currentPage= $data['currentPage'];
$totalRecords = $data['totalRecords'] ?? 0;
?>
<!DOCTYPE html>
<html lang="vi">
<?php
Import::layout('Head', [
  "title" => "Sản phẩm"
]);
?>

<body class="font-display bg-background-light text-text-light">
  <?php Import::layout("UserNavigation") ?>

  <main class="flex-1">
    <div class="px-4 sm:px-6 md:px-10 flex justify-center py-8">
      <div class="layout-content-container flex flex-col max-w-7xl flex-1 gap-8 md:gap-12">
        
        <div class="flex flex-col lg:flex-row gap-8">
          
          <form action="" method="GET" id="filterForm" class="w-full lg:w-1/4">
  <aside>
    <div class="sticky top-24 space-y-6 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
      
      <?php 
    // Logic kiểm tra xem có đang lọc gì không
    $hasCategory = !empty($filters['product_category']); // Sửa 'category' thành 'product_category'
    $hasBranch   = !empty($filters['branch']);
    $hasPrice    = ($filters['price_min'] > 0) || ($filters['price_max'] < 50000000); // Kiểm tra cả giá max nếu thay đổi

    if ($hasCategory || $hasBranch || $hasPrice): 
?>
    <div class="flex justify-between items-center border-b pb-2 mb-2">
        <span class="text-xs text-gray-400">Đang lọc...</span>
        <a href="Product.php" class="text-xs text-red-500 hover:underline font-bold">Xóa tất cả lọc</a>
    </div>
<?php endif; ?>

      <div class="space-y-3">
                    <h4 class="font-semibold text-gray-700">Loại đàn</h4>
                    
                    <?php foreach ($productCategories as $cat): ?>
                        <?php 
                            // Check theo key mới 'product_category'
                            $isChecked = (isset($filters['product_category']) && $filters['product_category'] == $cat->id) ? 'checked' : '';
                        ?>
                        <label class="flex items-center gap-2 text-sm cursor-pointer hover:text-primary transition-colors">
                            <input 
                                type="radio" 
                                name="product_category"  
                                value="<?= $cat->id ?>" 
                                <?= $isChecked ?>
                                onchange="this.form.submit()"
                                class="h-4 w-4 rounded-full text-primary focus:ring-accent" 
                            />
                            <span><?= htmlspecialchars($cat->name) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

      <hr class="border-gray-100">

      <div class="space-y-3">
        <h4 class="font-semibold text-gray-700">Thương hiệu</h4>
        
        <div class="space-y-2 max-h-40 overflow-y-auto pr-1 scrollbar-thin">
            <?php foreach ($branches as $branch): ?>
                <?php 
                    // Kiểm tra checked dựa trên key 'branch'
                    $isChecked = (isset($filters['branch']) && $filters['branch'] == $branch->id) ? 'checked' : '';
                ?>
                <label class="flex items-center gap-2 text-sm cursor-pointer hover:text-primary transition-colors">
                    <input 
                        type="radio" 
                        name="branch" 
                        value="<?= $branch->id ?>" 
                        <?= $isChecked ?>
                        onchange="this.form.submit()"
                        class="h-4 w-4 rounded-full text-primary focus:ring-accent" 
                    />
                    <span><?= htmlspecialchars($branch->name) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

      <hr class="border-gray-100">

      <div class="space-y-3">
        <h4 class="font-semibold text-gray-700">Khoảng giá</h4>
        <div class="flex items-center gap-2">
            <input 
                name="price_min" 
                type="number" 
                min="0"
                placeholder="Từ" 
                value="<?= $filters['price_min'] > 0 ? $filters['price_min'] : '' ?>" 
                class="w-full p-2 border border-gray-300 rounded text-sm outline-none focus:border-primary"
            >
            <span class="text-gray-400">-</span>
            <input 
                name="price_max" 
                type="number" 
                min="0"
                placeholder="Đến" 
                value="<?= $filters['price_max'] < 50000000 ? $filters['price_max'] : '' ?>" 
                class="w-full p-2 border border-gray-300 rounded text-sm outline-none focus:border-primary"
            >
        </div>
        <button type="submit" class="w-full btn btn-sm btn-outline btn-primary mt-2">
            Áp dụng giá
        </button>
      </div>

    </div>
  </aside>
</form>
          <section class="w-full lg:w-3/4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
              <div>
                <h1 class="text-3xl font-bold text-primary">Danh sách sản phẩm</h1>
                
                <p class="text-sm text-gray-500 mt-1">
                    Hiển thị <span class="font-medium text-gray-900"><?= count($products) ?></span> 
                    trên tổng số <span class="font-medium text-gray-900"><?= number_format($totalRecords, 0, ',', '.') ?></span> sản phẩm
                </p>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-base font-medium text-gray-700 hidden sm:inline-block">Sắp xếp:</span>
                
                <div class="relative">
                    <select 
                        name="sort" 
                        form="filterForm" 
                        onchange="this.form.submit()" 
                        class="appearance-none bg-white border border-gray-300 text-gray-700 py-2.5 pl-4 pr-10 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary shadow-sm hover:border-primary transition-all cursor-pointer font-medium min-w-[180px]"
                    >
                        <option value="popular" <?= ($filters['sort'] ?? '') == 'popular' ? 'selected' : '' ?>>
                             Phổ biến nhất
                        </option>
                        <option value="price_asc" <?= ($filters['sort'] ?? '') == 'price_asc' ? 'selected' : '' ?>>
                             Giá: Thấp đến cao
                        </option>
                        <option value="price_desc" <?= ($filters['sort'] ?? '') == 'price_desc' ? 'selected' : '' ?>>
                             Giá: Cao đến thấp
                        </option>
                    </select>
                    
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <span class="material-symbols-outlined text-xl">expand_more</span>
                    </div>
                </div>
            </div>
            </div>

            <?php if (empty($products)): ?>
              <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm text-center text-gray-500">
                Chưa có sản phẩm nào phù hợp với bộ lọc.
              </div>
            <?php else: ?>
                <ul class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 px-4 md:px-0">
                  <?php foreach ($products as $product): ?>
                      <li>
                          <?php 
                              $imgUrl = !empty($product->productImages) ? $product->productImages[0]->url : null;
                              $catName = $product->productCategory->name ?? "Khác";

                              Import::component(fileName: "ProductCard", variables: [
                                  "id"               => $product->id,
                                  "title"            => $product->name,
                                  "productCategory"  => $product->productCategory->name ??"",
                                  "branchName"       => $product->branch->name ?? "",
                                  "price_current"    => $product->price_current,
                                  "discount_percent" => $product->discount_percent,
                                  "imageUrl"         => $imgUrl
                              ]); 
                          ?>
                      </li>
                  <?php endforeach ?>
                </ul>

              <?php if ($totalPages > 1): ?>
               <nav class="flex justify-center mt-8">
                  <ul class="flex items-center -space-x-px h-10 text-base">
                    
                    <li>
                      <?php 
                          // Tạo link cho trang trước, giữ nguyên các tham số khác
                          $prevParams = array_merge($_GET, ['page' => max(1, $currentPage - 1)]);
                          $prevLink = '?' . http_build_query($prevParams);
                      ?>
                      <a href="<?= $prevLink ?>" 
                        class="flex items-center justify-center px-4 h-10 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100">
                        <span class="material-symbols-outlined !text-xl">chevron_left</span>
                      </a>
                    </li>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                      <li>
                          <?php 
                              // Tạo link cho trang $i, giữ nguyên các tham số khác (sort, price, branch...)
                              $pageParams = array_merge($_GET, ['page' => $i]);
                              $pageLink = '?' . http_build_query($pageParams);
                          ?>
                          <a href="<?= $pageLink ?>" 
                            class="flex items-center justify-center px-4 h-10 leading-tight border <?= $i == $currentPage ? 'bg-primary text-white border-primary' : 'bg-white  border-gray-300 hover:bg-gray-100' ?>">
                            <?= $i ?>
                          </a>
                      </li>
                    <?php endfor; ?>

                    <li>
                      <?php 
                          // Tạo link cho trang sau
                          $nextParams = array_merge($_GET, ['page' => min($totalPages, $currentPage + 1)]);
                          $nextLink = '?' . http_build_query($nextParams);
                      ?>
                      <a href="<?= $nextLink ?>" 
                        class="flex items-center justify-center px-4 h-10 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100">
                        <span class="material-symbols-outlined !text-xl">chevron_right</span>
                      </a>
                    </li>

                  </ul>
                </nav>
              <?php endif; ?>

            <?php endif; ?>
          </section>
        </div>
      </div>
    </div>
  </main>

  <?php Import::layout("Footer") ?>
  <?php Import::component('SocialWidget'); ?>
</body>
</html>