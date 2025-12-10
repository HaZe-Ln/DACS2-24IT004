<?php 
    $price_original_display = 0;
    if(isset($discount_percent) && $discount_percent > 0){
        $price_original_display = $price_current / (1 - ($discount_percent / 100));
    }
    $detailUrl = "/app/views/pages/ProductDetail.php?id=" . ($id ?? 0);
    
    // Lấy URL hiện tại để controller biết đường redirect về
    $currentUrl = $_SERVER['REQUEST_URI'];
?>

<div id="product-card-<?= $id ?? 0 ?>" class="group card bg-white shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 rounded-lg overflow-hidden h-full flex flex-col relative">
  <figure class="relative aspect-[4/5] overflow-hidden">
    <img src="<?= $imageUrl ?? 'https://via.placeholder.com/300x400' ?>" alt="<?= htmlspecialchars($title ?? '') ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
    <?php if (!empty($branchName)): ?>
      <div class="absolute top-2 right-2 z-10"><span class="badge badge-sm bg-white/90 backdrop-blur text-xs font-semibold shadow-sm border-none"><?= htmlspecialchars($branchName) ?></span></div>
    <?php endif; ?>
    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-20">
        <a href="<?= $detailUrl ?>" class="btn btn-primary btn-sm text-white font-bold rounded-full px-6 shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">Xem chi tiết</a>
    </div>
  </figure>

  <div class="p-3 flex flex-col flex-1 justify-between">
    <div>
      <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1"><?= htmlspecialchars($productCategory ?? "DANH MỤC") ?></div>
      <a href="<?= $detailUrl ?>" class="hover:text-primary transition-colors"><h2 class="text-sm font-medium text-gray-700 line-clamp-2 min-h-[2.5rem] mb-2" title="<?= htmlspecialchars($title ?? '') ?>"><?= htmlspecialchars($title ?? 'Sản phẩm') ?></h2></a>
    </div>

    <div class="bg-red-50 rounded-lg p-3 flex items-center justify-between mt-auto">
      <div class="flex flex-col">
        <span class="text-[#b91c1c] font-bold text-lg leading-tight"><?= number_format($price_current ?? 0, 0, ',', '.') ?>₫</span>
        <?php if (isset($discount_percent) && $discount_percent > 0): ?>
          <div class="flex items-center gap-2 text-xs mt-1">
            <span class="text-gray-400 line-through"><?= number_format($price_original_display, 0, ',', '.') ?>₫</span>
            <span class="text-red-600 font-bold">-<?= $discount_percent ?>%</span>
          </div>
        <?php endif; ?>
      </div>

      <form method="POST" action="/app/controllers/CartController.php?action=add" class="inline-block">
        <input type="hidden" name="product_id" value="<?= $id ?? 0 ?>">
        <input type="hidden" name="quantity" value="1">
        <input type="hidden" name="redirect_url" value="<?= $currentUrl ?>">
        
        <button type="submit" class="text-[#991b1b] hover:text-red-600 hover:scale-110 transition-transform p-1" title="Thêm vào giỏ hàng">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
            <path d="M2.25 2.25a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 00-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 000-1.5H5.378A2.25 2.25 0 017.5 15h11.218a.75.75 0 00.674-.421 60.358 60.358 0 002.96-7.228.75.75 0 00-.525-.965A60.864 60.864 0 005.68 4.509l-.232-.867A1.875 1.875 0 003.636 2.25H2.25zM3.75 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM16.5 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z" />
          </svg>
        </button>
      </form>

    </div>
  </div>
</div>  