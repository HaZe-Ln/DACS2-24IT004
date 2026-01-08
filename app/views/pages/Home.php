<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::configs(["db/PDO"]);
Import::controllers(["ProductController"]);
Import::repositories(["PostRepository"]); 
// 1. LẤY DỮ LIỆU TỪ DATABASE
// A. Lấy 4 sản phẩm Ưu đãi
$discountedProducts = ProductRepository::paginate(1, 8, [
    'has_discount' => true, 
    'sort' => 'price_asc'
]);
$latestPosts = PostRepository::getLatest(3);

// B. Lấy 8 sản phẩm Nổi bật
$featuredProducts = ProductRepository::getBestSellingProducts(8);

// Hero banner
$hero = [
  "title" => "Giảm Giá Lớn Mùa Đông",
  "subtitle" => "Khám phá bộ sưu tập nhạc cụ mới nhất với ưu đãi lên đến 30%.",
  "action" => "/app/views/pages/Product.php",
  "image" => "https://images.unsplash.com/photo-1511379938547-c1f69419868d?auto=format&fit=crop&w=1600&q=80",
];

// Logo thương hiệu
$brands = ["yamaha", "fender", "gibson", "roland", "martin", "ibanez"];
$brandLogos = array_map(function($brand) {
  return [
    "src" => "/uploads/brands/" . $brand . ".png",
    "alt" => ucfirst($brand)
  ];
}, $brands);


?>
<!DOCTYPE html>
<html lang="vi">
<?php
Import::layout('Head', [
  "title" => "Trang chủ"
]);
?>

<body class="font-display bg-background-light text-text-light overflow-x-hidden">
  <?php Import::layout("UserNavigation") ?>

  <?php Import::component('Loader'); ?>

  <main class="flex-1 w-full">
    <div class="container mx-auto px-4 sm:px-6 md:px-10 py-8">
      <div class="flex flex-col gap-10">

        <section class="w-full">
          <div class="w-full">
            <div
              class="flex min-h-[280px] md:min-h-[420px] flex-col gap-4 md:gap-6 bg-cover bg-center bg-no-repeat rounded-xl items-center justify-center p-6 md:p-10 text-center w-full"
              style="background-image: linear-gradient(rgba(29, 44, 94, 0.55), rgba(18, 18, 18, 0.8)), url('<?= $hero['image'] ?>');">

              <div class="flex flex-col gap-2 md:gap-4 max-w-xl">
                <h1 class="text-white text-3xl md:text-5xl font-black leading-tight tracking-[-0.03em]">
                  <?= htmlspecialchars($hero["title"]) ?>
                </h1>
                <p class="text-gray-200 text-base md:text-xl leading-relaxed">
                  <?= htmlspecialchars($hero["subtitle"]) ?>
                </p>
              </div>
              <a href="<?= htmlspecialchars($hero["action"]) ?>"
                class="inline-flex items-center justify-center rounded-lg bg-accent text-white h-10 md:h-12 px-6 text-sm md:text-base font-bold tracking-[0.01em] transition-transform hover:scale-105 mt-2">
                Khám phá ngay
              </a>
            </div>
          </div>
        </section>

        <section>
          <div class="flex items-center justify-between pb-4 border-b-2 border-accent/50 mb-6">
            <h2 class="text-primary text-2xl md:text-3xl font-bold tracking-[-0.01em]">Ưu Đãi Sốc</h2>
          </div>
          
          <?php if (empty($discountedProducts)): ?>
             <p class="text-gray-500 text-center">Hiện chưa có chương trình khuyến mãi.</p>
          <?php else: ?>
             <ul class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 px-4 md:px-0">
                <?php foreach ($discountedProducts as $product): ?>
                    <li>
                        <?php 
                            $imgUrl = !empty($product->productImages) ? $product->productImages[0]->url : null;
                            
                            Import::component(fileName: "ProductCard", variables: [
                                "id"               => $product->id, // [QUAN TRỌNG] Phải truyền ID vào đây
                                "title"            => $product->name,
                                "productCategory"  => $product->productCategory->name ?? "",
                                "branchName"       => $product->branch->name ?? "",
                                "price_current"    => $product->price_current,
                                "discount_percent" => $product->discount_percent,
                                "imageUrl"         => $imgUrl
                                  ]); 
                                ?>
                    </li>
                <?php endforeach ?>
             </ul>
          <?php endif; ?>
        </section>

        <section class="">
          <div class="flex justify-between items-center pb-4 border-b-2 border-accent/50 mb-6">
            <h2 class="text-primary text-2xl md:text-3xl font-bold tracking-[-0.01em]">Sản Phẩm Nổi Bật</h2>
            <a class="text-accent font-semibold hover:underline whitespace-nowrap text-sm md:text-base"
              href="/app/views/pages/Product.php">Xem tất cả</a>
          </div>

          <?php if (empty($featuredProducts)): ?>
             <p class="text-gray-500 text-center">Đang cập nhật sản phẩm.</p>
          <?php else: ?>
             <ul class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 px-4 md:px-0">
                <?php foreach ($featuredProducts as $product): ?>
                    <li>
                        <?php 
                            $imgUrl = !empty($product->productImages) ? $product->productImages[0]->url : null;

                            Import::component(fileName: "ProductCard", variables: [
                                "id"               => $product->id, // [QUAN TRỌNG] Phải truyền ID vào đây
                                "title"            => $product->name,
                                "productCategory"  => $product->productCategory->name ?? "",
                                "branchName"       => $product->branch->name ?? "",
                                "price_current"    => $product->price_current,
                                "discount_percent" => $product->discount_percent,
                                "imageUrl"         => $imgUrl
                            ]); 
                        ?>
                    </li>
                <?php endforeach ?>
             </ul>
          <?php endif; ?>
        </section>

        <section>
          <div class="flex items-center justify-between pb-4 border-b-2 border-accent/50 mb-6">
            <h2 class="text-primary text-2xl md:text-3xl font-bold tracking-[-0.01em]">Thương Hiệu</h2>
          </div>
          <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-6 gap-6 items-center">
            <?php foreach ($brandLogos as $brand): ?>
              <div class="flex justify-center p-2">
                <img src="<?= htmlspecialchars($brand["src"]) ?>" alt="<?= htmlspecialchars($brand["alt"]) ?>"
                  class="h-8 md:h-10 object-contain grayscale hover:grayscale-0 transition duration-300" />
              </div>
            <?php endforeach; ?>
          </div>
        </section>

       
          <div class="flex justify-between items-center pb-4 border-b-2 border-accent/50 mb-6">
            <h2 class="text-primary text-2xl md:text-3xl font-bold tracking-[-0.01em]">Tin Tức</h2>
            <a class="text-accent font-semibold hover:underline whitespace-nowrap text-sm md:text-base" href="/app/views/pages/Posts.php">Xem tất cả</a>
          </div>
          
          <?php if (empty($latestPosts)): ?>
             <p class="text-gray-500 text-center">Chưa có bài viết nào.</p>
          <?php else: ?>
             <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($latestPosts as $post): ?>
                  <?php
                    // Xử lý dữ liệu
                    $plainContent = strip_tags($post->content ?? '');
                    $excerpt = mb_substr($plainContent, 0, 120) . '...';
                    $image = !empty($post->thumb_url) ? $post->thumb_url : 'https://via.placeholder.com/600x400?text=No+Image';
                    
                    // Xử lý ngày tháng
                    $dateStr = "Mới cập nhật";
                    if (!empty($post->created_at)) {
                        $dateStr = date('d/m/Y', strtotime($post->created_at));
                    }

                    // Gọi Component PostCard
                    Import::component(fileName: "PostCard", variables: [
                        "id"      => $post->id,
                        "title"   => $post->name,
                        "image"   => $image,
                        "date"    => $dateStr,
                        "excerpt" => $excerpt
                    ]);
                  ?>
                <?php endforeach; ?>
             </div>
          <?php endif; ?>
        </section>

      </div>
    </div>
  </main>

  <?php Import::layout("Footer") ?>
  
  <script>
    function showLoader() { document.getElementById('global-loader')?.classList.remove('hidden'); document.getElementById('global-loader')?.classList.add('flex'); }
  </script>
  <?php Import::component('SocialWidget'); ?>
</body>
</html>