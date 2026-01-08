<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';

$storyBlocks = [
  [
    "title" => "Sứ mệnh",
    "desc" => "Mang âm nhạc chất lượng cao đến mọi người, truyền cảm hứng và nuôi dưỡng tài năng ở mọi lứa tuổi.",
    "image" => "https://images.unsplash.com/photo-1470229538611-16ba8c7ffbd7?auto=format&fit=crop&w=1200&q=80",
  ],
  [
    "title" => "Tầm nhìn",
    "desc" => "Trở thành thương hiệu nhạc cụ hàng đầu Việt Nam, là điểm đến tin cậy cho tất cả người yêu nhạc.",
    "image" => "https://sf-static.upanhlaylink.com/img/image_20251205497aef97c9c3d2e2a62dea2d68cb76be.jpg",
  ],
];

$coreValues = [
  ["icon" => "favorite", "title" => "Đam mê", "desc" => "Sống cùng âm nhạc và truyền cảm hứng cho khách hàng."],
  ["icon" => "verified", "title" => "Uy tín", "desc" => "Minh bạch, trung thực trong mọi cam kết."],
  ["icon" => "shield", "title" => "Chất lượng", "desc" => "Sản phẩm chính hãng, dịch vụ bảo hành rõ ràng."],
  ["icon" => "headset_mic", "title" => "Tận tâm", "desc" => "Đồng hành trước và sau khi mua hàng."],
];

$teamMembers = [
  [
    "name" => "Nguyễn Văn A",
    "role" => "Nhà sáng lập & CEO",
    "bio" => "24GT",
    "image" => "https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=600&q=80",
  ],
  [
    "name" => "Trần Văn B",
    "role" => "Nhà sáng lập & CEO",
    "bio" => "24GT",
    "image" => "https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=600&q=80",
  ],
  [
    "name" => "Hoàng Anh",
    "role" => "Nhà sáng lập & CEO",
    "bio" => "24IT004",
    "image" => "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=600&q=80",
  ],
];
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Giới thiệu"]); ?>

<style>
  @keyframes wiggle {
    0%, 100% { transform: rotate(-6deg); }
    50% { transform: rotate(6deg); }
  }
  
  /* Class kích hoạt animation khi hover vào thẻ cha (group) */
  .group:hover .group-hover\:animate-wiggle {
    animation: wiggle 0.3s ease-in-out infinite;
  }
</style>

<body class="font-display bg-background-light text-text-light">
  <?php Import::layout("UserNavigation") ?>

  <main class="flex flex-col items-center">
    <div class="w-full max-w-6xl px-4 sm:px-6 lg:px-8">
      
      <section class="py-10">
        <div class="@container">
          <div class="@[480px]:p-0">
            <div class="flex min-h-[420px] flex-col gap-6 bg-cover bg-center bg-no-repeat @[480px]:gap-8 rounded-xl items-center justify-center p-6 text-center shadow-lg transition-transform duration-700 hover:scale-[1.01]"
              style="background-image: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.65)), url('https://images.unsplash.com/photo-1507874457470-272b3c8d8ee2?auto=format&fit=crop&w=1600&q=80');">
              
              <div class="flex flex-col gap-3 max-w-2xl transform transition-all duration-500 hover:-translate-y-1">
                <h1 class="text-white text-4xl sm:text-5xl font-black leading-tight tracking-tight drop-shadow-md">
                  Về AMusic - Nơi Giai Điệu Bắt Đầu
                </h1>
                <p class="text-gray-200 text-base sm:text-lg leading-relaxed drop-shadow-sm">
                  Khám phá câu chuyện, sứ mệnh và đội ngũ đứng sau AMusic trên hành trình đem âm nhạc chất lượng đến bạn.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="flex flex-col gap-8 py-10">
        <div class="text-center space-y-3">
          <h2 class="text-primary text-3xl sm:text-4xl font-bold tracking-tight">Câu chuyện của chúng tôi</h2>
          <p class="text-gray-600 text-base sm:text-lg max-w-3xl mx-auto">
            HTAMusic khởi nguồn từ niềm đam mê bất tận với âm nhạc, mong muốn mang đến nhạc cụ chính hãng và dịch vụ tận tâm cho mọi người chơi.
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
          <?php foreach ($storyBlocks as $index => $block): ?>
            <div class="flex flex-col gap-3 group cursor-default">
              <div class="w-full aspect-video rounded-xl shadow-md overflow-hidden">
                 <div class="w-full h-full bg-center bg-no-repeat bg-cover transition-transform duration-700 group-hover:scale-110" 
                      style="background-image: url('<?= htmlspecialchars($block["image"]) ?>');">
                 </div>
              </div>
              <div class="transition-transform duration-300 group-hover:translate-x-2">
                <p class="text-lg font-bold text-text-light text-primary"><?= htmlspecialchars($block["title"]) ?></p>
                <p class="text-gray-600 text-sm leading-relaxed"><?= htmlspecialchars($block["desc"]) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="py-10">
        <h2 class="text-primary text-3xl sm:text-4xl font-bold text-center pb-6">Giá trị cốt lõi</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <?php foreach ($coreValues as $value): ?>
            <div class="group flex flex-col items-center text-center gap-3 rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-xl hover:-translate-y-2 hover:border-primary/30">
              
              <div class="text-primary bg-primary/10 p-3 rounded-full transition-colors group-hover:bg-primary group-hover:text-white">
                <span class="material-symbols-outlined !text-3xl group-hover:animate-wiggle"><?= htmlspecialchars($value["icon"]) ?></span>
              </div>
              
              <h3 class="text-lg font-bold group-hover:text-primary transition-colors"><?= htmlspecialchars($value["title"]) ?></h3>
              <p class="text-sm text-gray-600 leading-relaxed"><?= htmlspecialchars($value["desc"]) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="py-10">
        <div class="text-center space-y-3">
          <h2 class="text-primary text-3xl sm:text-4xl font-bold tracking-tight">Đồng Sáng Lập WebSite</h2>
          <p class="text-gray-600 text-base max-w-3xl mx-auto">
            Gặp gỡ những người truyền cảm hứng và luôn sẵn sàng đồng hành cùng bạn.
          </p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mt-10">
          <?php foreach ($teamMembers as $member): ?>
            <article class="group flex flex-col items-center text-center bg-white rounded-xl shadow-sm border border-gray-200 p-6 gap-3 transition-all duration-300 hover:-translate-y-3 hover:shadow-xl hover:border-accent/50">
              
              <div class="relative overflow-hidden rounded-full h-28 w-28 shadow-md border-2 border-transparent group-hover:border-accent transition-all duration-300">
                  <img class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3" 
                       src="<?= htmlspecialchars($member["image"]) ?>" 
                       alt="<?= htmlspecialchars($member["name"]) ?>">
              </div>

              <div class="space-y-1">
                <h3 class="text-lg font-bold text-text-light group-hover:text-primary transition-colors"><?= htmlspecialchars($member["name"]) ?></h3>
                <p class="text-accent text-sm font-semibold"><?= htmlspecialchars($member["role"]) ?></p>
                <p class="text-sm text-gray-600 leading-relaxed"><?= htmlspecialchars($member["bio"]) ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="py-16">
        <div class="group flex flex-col gap-4 items-center justify-center p-8 text-center bg-accent/10 rounded-xl border border-accent/30 shadow-sm transition-all hover:bg-accent/20">
          <h2 class="text-primary text-3xl font-bold tracking-tight">Sẵn sàng bắt đầu hành trình âm nhạc?</h2>
          <p class="text-gray-700 text-base max-w-2xl">
            Hãy để AMusic đồng hành. Khám phá bộ sưu tập nhạc cụ đa dạng và tìm người bạn tri kỷ của bạn.
          </p>
          <a href="/app/views/pages/Product.php" class="inline-flex items-center justify-center rounded-lg h-12 px-6 bg-primary text-white text-base font-bold shadow-lg hover:bg-primary/90 hover:shadow-primary/50 hover:scale-105 transition-all duration-300 hover:animate-pulse">
            Khám phá sản phẩm
          </a>
        </div>
      </section>
    </div>
  </main>

  <?php Import::layout("Footer") ?>
  <?php Import::component('SocialWidget'); ?>
</body>
</html>