<?php 
    $pid = $id ?? 0;
    $postLink = "/app/views/pages/PostDetail.php?id=" . $pid;
?>

<article class="flex flex-col bg-white rounded-lg shadow-sm hover:shadow-xl transition-shadow duration-300 overflow-hidden group border border-gray-100 h-full">
  <a class="block overflow-hidden" href="<?= $postLink ?>">
    <div class="w-full bg-center bg-no-repeat aspect-video bg-cover transition-transform duration-500 group-hover:scale-110" 
         style="background-image: url('<?= !empty($image) ? htmlspecialchars($image) : 'https://via.placeholder.com/600x400' ?>');">
    </div>
  </a>

  <div class="p-6 flex-1 flex flex-col justify-between">
    <div>
      <p class="text-sm text-gray-500 mb-2">
        <span class="material-symbols-outlined text-[14px] align-text-bottom mr-1">calendar_today</span>
        <?= htmlspecialchars($date ?? 'N/A') ?>
      </p>
      
      <h3 class="text-xl font-bold leading-tight mb-3 line-clamp-2">
        <a class="hover:text-accent transition-colors" href="<?= $postLink ?>">
            <?= htmlspecialchars($title) ?>
        </a>
      </h3>
      
      <p class="text-gray-600 text-sm leading-relaxed line-clamp-3">
        <?= htmlspecialchars($excerpt) ?>
      </p>
    </div>

    <a class="inline-flex items-center gap-2 text-accent hover:text-primary font-semibold mt-4 text-sm" 
       href="<?= $postLink ?>">
      <span>Đọc thêm</span>
      <span class="material-symbols-outlined text-sm">arrow_forward</span>
    </a>

    </div>
</article>