<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["PostController"]);

$controller = new PostController();

// 1. [ĐÃ SỬA] Lấy ID từ URL
$id = $_GET['id'] ?? null;

// 2. Lấy dữ liệu bài viết theo ID
$post = $controller->getDetail($id);

// 3. Nếu không tìm thấy bài viết -> Về trang danh sách
if (!$post) {
    header("Location: /app/views/pages/Posts.php");
    exit;
}

// 4. Lấy bài viết liên quan
$relatedPosts = $controller->getRelated($post->id);

// --- [CẬP NHẬT] Xử lý hiển thị ngày đăng ---
// Kiểm tra nếu có created_at thì format, không thì để mặc định
$dateDisplay = isset($post->created_at) 
    ? date('d/m/Y - H:i', strtotime($post->created_at)) 
    : "Không xác định";
?>

<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => htmlspecialchars($post->name)]); ?>

<body class="font-display bg-background-light text-text-light">
  <?php Import::layout("UserNavigation") ?>

  <main class="flex-1 px-4 sm:px-6 md:px-10 py-8">
    <div class="layout-content-container flex flex-col max-w-5xl mx-auto flex-1 gap-8">
      
      <nav class="flex flex-wrap gap-2 text-sm text-gray-600">
        <a class="hover:text-primary" href="/app/views/pages/home.php">Trang chủ</a>
        <span>/</span>
        <a class="hover:text-primary" href="/app/views/pages/Posts.php">Bài viết</a>
        <span>/</span>
        <span class="text-gray-800 font-medium line-clamp-1 max-w-[200px]"><?= htmlspecialchars($post->name) ?></span>
      </nav>

      <article class="flex flex-col gap-6">
        
        <header class="space-y-4 border-b border-gray-100 pb-6">
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <span class="bg-accent/10 text-accent px-2 py-1 rounded font-bold text-xs uppercase">Tin tức</span>
                <span>•</span>
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">schedule</span>
                    <?= $dateDisplay ?>
                </span>
            </div>
            
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-black leading-tight text-primary">
                <?= htmlspecialchars($post->name) ?>
            </h1>
        </header>

        <?php if (!empty($post->thumb_url)): ?>
            <div class="w-full aspect-video rounded-xl overflow-hidden shadow-sm">
                <img src="<?= htmlspecialchars($post->thumb_url) ?>" 
                     alt="<?= htmlspecialchars($post->name) ?>" 
                     class="w-full h-full object-cover">
            </div>
        <?php endif; ?>

        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
            <?= $post->content ?> 
        </div>

        <div class="flex items-center justify-between border-t border-gray-100 pt-6 mt-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                    <span class="material-symbols-outlined">person</span>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-900">Admin HTAMusic</p>
                    <p class="text-xs text-gray-500">Biên tập viên</p>
                </div>
            </div>
            
            <div class="flex gap-2">
                <button class="btn btn-circle btn-ghost btn-sm text-blue-600 hover:bg-blue-50">
                    <span class="material-symbols-outlined">thumb_up</span>
                </button>
                <button class="btn btn-circle btn-ghost btn-sm text-gray-600 hover:bg-gray-100">
                    <span class="material-symbols-outlined">share</span>
                </button>
            </div>
        </div>

      </article>

      <section class="mt-12 pt-10 border-t border-gray-200">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold text-primary">Bài viết liên quan</h2>
            <a href="/app/views/pages/Posts.php" class="text-accent hover:underline text-sm font-semibold">Xem tất cả →</a>
        </div>
        
        <?php if (empty($relatedPosts)): ?>
             <p class="text-gray-500 italic">Không có bài viết liên quan.</p>
        <?php else: ?>
             <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($relatedPosts as $rp): ?>
                    <?php 
                        $plainContent = strip_tags($rp->content ?? '');
                        $excerpt = mb_substr($plainContent, 0, 120) . '...';
                        
                        // [CẬP NHẬT] Format ngày cho bài liên quan
                        $dateRelated = isset($rp->created_at) 
                            ? date('d/m/Y', strtotime($rp->created_at)) 
                            : "N/A";

                        Import::component(fileName: "PostCard", variables: [
                            "id"      => $rp->id,
                            "title"   => $rp->name,
                            "image"   => $rp->thumb_url,
                            "date"    => $dateRelated, // Truyền ngày đã format vào Component
                            "excerpt" => $excerpt
                        ]);
                    ?>
                <?php endforeach; ?>
             </div>
        <?php endif; ?>
      </section>

    </div>
  </main>

  <?php Import::layout("Footer") ?>
  <?php Import::component('SocialWidget'); ?>
</body>
</html>