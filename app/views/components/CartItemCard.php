<?php 
    // Props: $item (CartItem Object)
    $p = $item->product; 
    $imgUrl = 'https://via.placeholder.com/150';
    if (!empty($p->productImages) && isset($p->productImages[0])) {
        $imgUrl = $p->productImages[0]->url;
    }
    $subTotalItem = $p->price_current * $item->quantity;
?>

<div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex gap-4 items-center transition-all hover:shadow-md">
    
    <a href="/app/views/pages/ProductDetail.php?id=<?= $p->id ?>" class="shrink-0 w-24 h-24 rounded-lg overflow-hidden border border-gray-200">
        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($p->name) ?>" class="w-full h-full object-cover">
    </a>

    <div class="flex-1 min-w-0">
        <a href="/app/views/pages/ProductDetail.php?id=<?= $p->id ?>" class="text-lg font-bold text-gray-800 hover:text-primary line-clamp-1 mb-1 transition-colors">
            <?= htmlspecialchars($p->name) ?>
        </a>
        <?php if(isset($p->productCategory)): ?>
            <p class="text-xs text-gray-500 mb-1">Loại: <?= htmlspecialchars($p->productCategory->name) ?></p>
        <?php endif; ?>
        <p class="text-accent font-bold"><?= number_format($p->price_current, 0, ',', '.') ?>₫</p>
    </div>

    <div class="flex flex-col items-end gap-3 shrink-0">
        <form method="POST" action="" class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
            <input type="hidden" name="action" value="update_qty">
            <input type="hidden" name="cart_item_id" value="<?= $item->id ?>">
            <button type="submit" name="quantity" value="<?= $item->quantity - 1 ?>" class="w-8 h-8 flex items-center justify-center bg-gray-50 hover:bg-gray-100 border-r border-gray-300 text-gray-600">-</button>
            <span class="w-10 h-8 flex items-center justify-center text-sm font-semibold bg-white text-gray-800"><?= $item->quantity ?></span>
            <button type="submit" name="quantity" value="<?= $item->quantity + 1 ?>" class="w-8 h-8 flex items-center justify-center bg-gray-50 hover:bg-gray-100 border-l border-gray-300 text-gray-600">+</button>
        </form>

        <form method="POST" onsubmit="return confirm('Xóa sản phẩm này?')">
            <input type="hidden" name="action" value="remove_item">
            <input type="hidden" name="cart_item_id" value="<?= $item->id ?>">
            <button type="submit" class="text-gray-400 hover:text-red-500 text-sm flex items-center gap-1 group font-medium">
                <span class="material-symbols-outlined text-[18px] group-hover:fill-current">delete</span> Xóa
            </button>
        </form>
    </div>
</div>