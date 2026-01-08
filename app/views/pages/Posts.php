<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["PostController"]);

// 1. Gọi Controller
$postController = new PostController();
$data = $postController->index();

// 2. Hứng dữ liệu
$posts       = $data['posts'];
$totalPages  = $data['totalPages'];
$currentPage = $data['currentPage'];
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Bài viết"]); ?>

<body class="font-display bg-background-light text-text-light">
  <?php Import::layout("UserNavigation") ?>

  <main class="flex-1">
    <div class="px-4 sm:px-6 md:px-10 flex justify-center py-8">
      <div class="layout-content-container flex flex-col max-w-7xl flex-1 gap-8 md:gap-12">
        
        <div class="text-center space-y-2">
          <h1 class="text-4xl font-bold text-primary">Tin tức &amp; Bài viết</h1>
          <p class="text-lg text-gray-600">Khám phá kiến thức, tin tức và mẹo hay về thế giới nhạc cụ.</p>
        </div>

        <?php if (empty($posts)): ?>
          <div class="p-12 bg-white rounded-xl border border-gray-200 shadow-sm text-center">
            <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">article</span>
            <p class="text-gray-500 text-lg">Chưa có bài viết nào được đăng tải.</p>
          </div>
        <?php else: ?>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($posts as $post): ?>
              <?php
                // Xử lý dữ liệu hiển thị
                $plainContent = strip_tags($post->content ?? '');
                $excerpt = mb_substr($plainContent, 0, 150) . '...';
                $image = !empty($post->thumb_url) ? $post->thumb_url : 'https://via.placeholder.com/600x400?text=No+Image';
                
                // [MỚI] Xử lý ngày đăng
                $dateDisplay = isset($post->created_at) 
                    ? date('d/m/Y', strtotime($post->created_at)) 
                    : "Mới cập nhật";

                // Gọi Component PostCard
                Import::component(fileName: "PostCard", variables: [
                    "id"      => $post->id,
                    "title"   => $post->name,
                    "image"   => $image,
                    "date"    => $dateDisplay, // [ĐÃ SỬA] Truyền ngày thật vào đây
                    "excerpt" => $excerpt
                ]);
              ?>
            <?php endforeach; ?>
          </div>

          <?php if ($totalPages > 1): ?>
          <nav class="flex justify-center mt-12">
            <ul class="flex items-center -space-x-px h-10 text-base">
              
              <?php
                // Hàm tạo URL giữ nguyên các tham số tìm kiếm/lọc khác
                function getUrl($page) {
                    $params = $_GET; 
                    $params['page'] = $page; 
                    return '?' . http_build_query($params);
                }
              ?>

              <li>
                <a href="<?= getUrl(max(1, $currentPage - 1)) ?>" 
                   class="flex items-center justify-center px-4 h-10 leading-tight text-gray-500 bg-white border border-e-0 border-gray-300 rounded-s-lg hover:bg-gray-100">
                  <span class="material-symbols-outlined !text-xl">chevron_left</span>
                </a>
              </li>

              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li>
                    <a href="<?= getUrl($i) ?>" 
                       class="flex items-center justify-center px-4 h-10 leading-tight border <?= $i == $currentPage ? 'bg-primary text-white/10 border-primary' : 'bg-white text-gray-500 border-gray-300 hover:bg-gray-100' ?>">
                       <?= $i ?>
                    </a>
                </li>
              <?php endfor; ?>

              <li>
                <a href="<?= getUrl(min($totalPages, $currentPage + 1)) ?>" 
                   class="flex items-center justify-center px-4 h-10 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100">
                  <span class="material-symbols-outlined !text-xl">chevron_right</span>
                </a>
              </li>
            </ul>
          </nav>
          <?php endif; ?>

        <?php endif; ?>
      </div>
    </div>
  </main>

  <?php Import::layout("Footer") ?>
  <?php Import::component('SocialWidget'); ?>
</body>
</html>