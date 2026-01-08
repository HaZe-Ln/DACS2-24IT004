<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["ProductController"]);

$controller = new ProductController();

// 1. Lấy ID từ URL
$productId = $_GET['id'] ?? null;

// 2. Gọi Controller để lấy dữ liệu
$product = $controller->getDetail($productId);

// 3. Nếu không tìm thấy -> Về trang danh sách
if (!$product) {
    header("Location: /app/views/pages/Product.php");
    exit;
}

// [LOGIC MỚI] Lấy đánh giá & Tính trung bình sao
$reviews = $controller->getProductReviews($product->id);
$totalStars = 0;
$reviewCount = count($reviews);
if ($reviewCount > 0) {
    foreach ($reviews as $rv) {
        $totalStars += $rv['star_rate'];
    }
    $avgRating = round($totalStars / $reviewCount, 1);
} else {
    $avgRating = 0;
}

// [LOGIC CŨ] Xử lý giá
$price_current = $product->price_current;
$price_original = $product->price_original ?? 0; 
$discount = $product->discount_percent ?? 0;
if ($price_original == 0 && $discount > 0) {
    $price_original = $price_current / (1 - $discount / 100);
}

// Lấy ảnh chính
$mainImage = !empty($product->productImages) ? $product->productImages[0]->url : 'https://via.placeholder.com/500';

// Lấy sản phẩm liên quan (nếu cần)
$related = $controller->getRelatedProducts($product->productCategory->id, $product->id);
?>

<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => htmlspecialchars($product->name)]); ?>

<body class="font-display bg-background-light text-text-light">
  <?php Import::layout("UserNavigation") ?>

  <main class="flex-1 px-4 sm:px-6 md:px-10 py-8">
    <div class="layout-content-container flex flex-col max-w-6xl mx-auto flex-1">
      
      <nav class="flex flex-wrap gap-2 py-4 text-sm text-gray-600">
        <a class="hover:text-primary" href="/app/views/pages/home.php">Trang chủ</a>
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

        <div class="flex flex-col gap-5"> <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($product->name) ?></h1>

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
            
            <form id="add-to-cart-form" action="/app/controllers/CartController.php?action=add" method="POST">
                <input type="hidden" name="product_id" value="<?= $product->id ?>">
                
                <input type="hidden" name="redirect_url" id="redirect_url" value="">

                <div class="flex items-center mb-4">
                    <label class="text-gray-700 font-medium mr-4">Số lượng:</label>
                    <div class="flex items-center border border-gray-300 rounded-lg">
                        <button type="button" onclick="updateQty(-1)" class="px-3 py-1 hover:bg-gray-100 text-gray-600 border-r border-gray-300">-</button>
                        <input type="number" name="quantity" id="input-qty" value="1" min="1" max="99" class="w-12 text-center focus:outline-none py-1" readonly>
                        <button type="button" onclick="updateQty(1)" class="px-3 py-1 hover:bg-gray-100 text-gray-600 border-l border-gray-300">+</button>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                  <button type="button" 
                    onclick="submitCart('current')"
                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-primary bg-primary/10 py-3 px-6 font-bold text-primary hover:bg-primary/20 transition-colors">
                    <span class="material-symbols-outlined">add_shopping_cart</span> Thêm vào giỏ
                  </button>
                  
                  <button type="button" 
                    onclick="submitCart('checkout')"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary py-3 px-6 font-bold text-white hover:bg-primary/90 transition-colors">
                    <span class="material-symbols-outlined">shopping_bag</span> Mua ngay
                  </button>
                </div>
            </form>
          </div>
          </div> </div>
      <section class="mt-12 bg-white rounded-xl border border-gray-200 p-6 sm:p-8 shadow-sm">
        <h2 class="text-2xl font-bold text-primary mb-6 flex items-center gap-3">
            Đánh giá sản phẩm
            <?php if ($reviewCount > 0): ?>
                <span class="text-base font-normal text-gray-500">(<?= $reviewCount ?> đánh giá)</span>
            <?php endif; ?>
        </h2>

        <?php if ($reviewCount == 0): ?>
            <div class="flex flex-col items-center justify-center py-10 text-center text-gray-500 bg-gray-50 rounded-lg border border-gray-100 border-dashed">
                <span class="material-symbols-outlined text-5xl mb-3 text-gray-300">reviews</span>
                <p class="font-medium">Sản phẩm này chưa có đánh giá nào.</p>
                <p class="text-sm">Hãy mua hàng và là người đầu tiên đánh giá!</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <div class="lg:col-span-1 flex flex-col items-center justify-center p-6 bg-orange-50 rounded-xl border border-orange-100 text-center h-fit">
                    <div class="text-5xl font-black text-orange-500 mb-2"><?= $avgRating ?>/5</div>
                    <div class="flex gap-1 text-orange-400 mb-2">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <span class="material-symbols-outlined text-2xl" 
                                  style="font-variation-settings: 'FILL' <?= $i <= round($avgRating) ? 1 : 0 ?>">star</span>
                        <?php endfor; ?>
                    </div>
                    <p class="text-sm text-gray-600 font-medium">Dựa trên <?= $reviewCount ?> nhận xét</p>
                </div>

                <div class="lg:col-span-3 flex flex-col gap-6">
                    <?php foreach ($reviews as $rv): ?>
                        <div class="border-b border-gray-100 pb-6 last:border-0 last:pb-0">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold shrink-0">
                                    <?= strtoupper(substr($rv['user_name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm font-bold text-gray-900"><?= htmlspecialchars($rv['user_name'] ?? 'Người dùng') ?></p>
                                            <div class="flex gap-0.5 text-orange-400 my-1">
                                                <?php for($i=1; $i<=5; $i++): ?>
                                                    <span class="material-symbols-outlined text-[16px]" 
                                                          style="font-variation-settings: 'FILL' <?= $i <= $rv['star_rate'] ? 1 : 0 ?>">star</span>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-400">
                                            <?= isset($rv['created_at']) ? date('d/m/Y', strtotime($rv['created_at'])) : '' ?>
                                        </span>
                                    </div>
                                    <div class="mt-2 text-gray-700 text-sm leading-relaxed bg-gray-50 p-3 rounded-lg">
                                        <?= nl2br(htmlspecialchars($rv['content'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
      </section>

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
                            "id"               => $rp->id,
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

  <script>
      function updateQty(change) {
          const input = document.getElementById('input-qty');
          let newVal = parseInt(input.value) + change;
          if (newVal < 1) newVal = 1;
          if (newVal > 99) newVal = 99;
          input.value = newVal;
      }

      function submitCart(type) {
          const form = document.getElementById('add-to-cart-form');
          const qty = document.getElementById('input-qty').value;
          const productId = '<?= $product->id ?>'; 

          if (type === 'checkout') {
              // Logic Mua ngay -> Chuyển hướng Checkout
              const url = `/app/views/pages/Checkout.php?direct=true&product_id=${productId}&quantity=${qty}`;
              window.location.href = url;
          } else {
              // Logic Thêm giỏ -> Submit Form
              document.getElementById('redirect_url').value = window.location.href;
              form.submit();
          }
      }
  </script>
  <?php Import::component('SocialWidget'); ?>
</body>
</html>