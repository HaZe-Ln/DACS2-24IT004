<?php
$footerLinks = [
  "Hữu ích" => [
    ["label" => "Chính sách bảo hành", "href" => "#"],
    ["label" => "Hướng dẫn mua hàng", "href" => "#"],
    ["label" => "Câu hỏi thường gặp", "href" => "#"],
    ["label" => "Về chúng tôi", "href" => "#"],
  ],
  "Liên hệ" => [
    ["label" => "123 Nguyễn Văn Huyên, Cầu Giấy, Hà Nội", "icon" => "location_on"],
    ["label" => "(028) 3812 3456", "icon" => "call"],
    ["label" => "support@htamusic.vn", "icon" => "email"],
  ],
];

$socialLinks = [
  ["label" => "Facebook", "href" => "#"],
  ["label" => "Instagram", "href" => "#"],
  ["label" => "YouTube", "href" => "#"],
];
?>

<footer class="bg-primary text-gray-200 mt-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-10 py-10 space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
      <div>
        <h3 class="text-lg font-bold text-white mb-4">HTAMusic</h3>
        <p class="text-sm leading-relaxed">
          Chuyên cung cấp nhạc cụ chính hãng cùng dịch vụ hậu mãi tận tâm cho người chơi mới và chuyên nghiệp.
        </p>
      </div>

      <?php foreach ($footerLinks as $section => $items): ?>
        <div>
          <h3 class="text-lg font-bold text-white mb-4"><?= htmlspecialchars($section) ?></h3>
          <ul class="space-y-2 text-sm">
            <?php foreach ($items as $item): ?>
              <li>
                <?php if (isset($item["icon"])): ?>
                  <span class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-base mt-0.5"><?= htmlspecialchars($item["icon"]) ?></span>
                    <span><?= htmlspecialchars($item["label"]) ?></span>
                  </span>
                <?php else: ?>
                  <a href="<?= htmlspecialchars($item["href"]) ?>" class="hover:text-accent transition-colors">
                    <?= htmlspecialchars($item["label"]) ?>
                  </a>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>

      <div>
        <h3 class="text-lg font-bold text-white mb-4">Theo dõi chúng tôi</h3>
        <div class="flex space-x-4">
          <?php foreach ($socialLinks as $social): ?>
            <a href="<?= htmlspecialchars($social["href"]) ?>" class="text-gray-200 hover:text-accent transition-colors text-sm font-semibold">
              <?= htmlspecialchars($social["label"]) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="border-t border-white/20 pt-6 text-center text-sm">
      © 2024 HTAMusic. All Rights Reserved.
    </div>
  </div>
</footer>
