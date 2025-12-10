<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["ProductController"]);

$controller = new ProductController();

// 1. Lấy ID từ URL
$productId = $_GET['id'] ?? null;

// 2. Gọi Controller để lấy dữ liệu thật
// (Đảm bảo bạn đã thêm hàm getDetail vào ProductController như hướng dẫn trước)
$product = $controller->getDetail($productId);

// 3. Nếu không tìm thấy sản phẩm -> Chuyển hướng về trang danh sách
if (!$product) {
    header("Location: /app/views/pages/Product.php");
    exit;
}

// 4. Lấy sản phẩm liên quan (Dựa theo ID danh mục)
$related = $controller->getRelatedProducts($product->productCategory->id ?? 0, $product->id);

// --- Xử lý hiển thị giá ---
$price_current = $product->price_current;
$price_original = $product->price_original ?? 0; 
$discount = $product->discount_percent ?? 0;

// Tính giá gốc hiển thị nếu chưa có
if ($price_original == 0 && $discount > 0) {
    $price_original = $price_current / (1 - $discount / 100);
}

// Lấy ảnh chính
$mainImage = !empty($product->productImages) ? $product->productImages[0]->url : 'https://via.placeholder.com/500';
?>

<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => htmlspecialchars($product->name)]); ?>

<body class="font-display bg-background-light text-text-light">
  <?php Import::layout("UserNavigation") ?>

  <main class="flex-1 px-4 sm:px-6 md:px-10 py-8">
    <div class="layout-content-container flex flex-col max-w-6xl mx-auto flex-1">
      
      <nav class="flex flex-wrap gap-2 py-4 text-sm text-gray-600">
        <a class="hover:text-primary" href="/app/views/pages/index.php">Trang chủ</a>
        <span>/</span>
        <a class="hover:text-primary" href="/app/views/pages/Product.php?product_category=<?= $product->productCategory->id ?? '' ?>">
            <?= htmlspecialchars($product->productCategory->name ?? 'Sản phẩm') ?>
        </a>
        <span>/</span>
        <span class="text-gray-800 font-medium"><?= htmlspecialchars($product->name) ?></span>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 mt-2">
        
        <div class="flex flex-col gap-4">
          <div class="bg-cover bg-center rounded-xl min-h-80 aspect-square w-full shadow-sm border border-gray-100 main-product-image" 
               style="background-image: url('<?= htmlspecialchars($mainImage) ?>');">
          </div>
          
          <?php if (!empty($product->productImages)): ?>
          <div class="grid grid-cols-4 gap-3">
            <?php foreach ($product->productImages as $img): ?>
              <div class="bg-cover bg-center rounded-lg aspect-square border border-gray-200 cursor-pointer hover:border-primary transition-colors" 
                   style="background-image: url('<?= htmlspecialchars($img->url) ?>');"
                   onclick="document.querySelector('.main-product-image').style.backgroundImage = 'url(<?= htmlspecialchars($img->url) ?>)'">
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <div class="flex flex-col gap-5">
          <div class="space-y-2">
            <h1 class="text-3xl md:text-4xl font-black leading-tight text-primary"><?= htmlspecialchars($product->name) ?></h1>
            <p class="text-gray-600 text-base">
                Thương hiệu: <span class="font-semibold text-gray-900"><?= htmlspecialchars($product->branch->name ?? 'N/A') ?></span> • 
                Loại: <span class="font-semibold text-gray-900"><?= htmlspecialchars($product->productCategory->name ?? 'N/A') ?></span>
            </p>
          </div>

          <div class="flex items-baseline gap-3">
            <p class="text-4xl font-bold text-accent"><?= number_format($price_current, 0, ',', '.') ?>₫</p>
            <?php if ($discount > 0): ?>
              <p class="text-gray-500 line-through text-xl"><?= number_format($price_original, 0, ',', '.') ?>₫</p>
              <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded">-<?= $discount ?>%</span>
            <?php endif; ?>
          </div>

          <p class="text-gray-700 leading-relaxed">
            <?= htmlspecialchars($product->description ?? 'Đang cập nhật mô tả...') ?>
          </p>

          <div class="border-t border-gray-200 pt-4 flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row gap-3">
              <button
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-primary bg-primary/10 py-3 px-6 font-bold text-primary hover:bg-primary/20 transition-colors"
                data-add-to-cart
                data-product-id="<?= htmlspecialchars($product->id) ?>"
                data-product-name="<?= htmlspecialchars($product->name) ?>"
                data-product-price="<?= htmlspecialchars($price_current) ?>"
                data-product-image="<?= htmlspecialchars($mainImage) ?>">
                <span class="material-symbols-outlined">add_shopping_cart</span> Thêm vào giỏ
              </button>
              <button class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary py-3 px-6 font-bold text-white hover:bg-primary/90 transition-colors">
                <span class="material-symbols-outlined">shopping_bag</span> Mua ngay
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <section class="mt-12">
        <h2 class="text-2xl font-bold text-primary mb-6">Sản phẩm liên quan</h2>
        
        <?php if (empty($related)): ?>
          <div class="p-4 bg-gray-50 rounded-xl text-gray-500 italic border border-gray-200">
             Không có sản phẩm liên quan nào.
          </div>
        <?php else: ?>
          <ul class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($related as $rp): ?>
                <li>
                    <?php 
                        $rpImg = !empty($rp->productImages) ? $rp->productImages[0]->url : null;
                        
                        Import::component(fileName: "ProductCard", variables: [
                            "id"               => $rp->id, // Truyền ID để tạo link
                            "title"            => $rp->name,
                            "productCategory"  => $rp->productCategory->name ?? "",
                            "branchName"       => $rp->branch->name ?? "",
                            "price_current"    => $rp->price_current,
                            "discount_percent" => $rp->discount_percent,
                            "imageUrl"         => $rpImg
                        ]); 
                    ?>
                </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

    </div>
    
  </main>

  <?php Import::layout("Footer") ?>
</body>
</html>