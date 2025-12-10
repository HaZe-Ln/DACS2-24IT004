<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["PostRepository"]);

// 1. Xử lý Xóa (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete") {
    $deleteId = $_POST["id"] ?? null;
    if ($deleteId) {
        PostRepository::delete((int)$deleteId);
    }
    header("Location: /app/views/pages/admin/PostManagement.php");
    exit;
}

// 2. Lấy tham số (GET)
$page = max(1, (int)($_GET["page"] ?? 1));
$limit = 10;
$search = trim($_GET["q"] ?? "");

// 3. Query Database
$posts = PostRepository::paginateAdmin($page, $limit, $search);
$totalRecords = PostRepository::countAdmin($search);
$totalPages = ceil($totalRecords / $limit);
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Quản lý Bài viết"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
    <div class="relative flex min-h-screen w-full">
        <?php Import::layout('AdminSidebar', ["active" => "posts"]); ?>

        <main class="flex-1 p-6 lg:p-8">
            <div class="flex flex-col gap-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h1 class="text-3xl font-bold text-gray-900">Quản lý Bài viết</h1>
                    
                    <a href="/app/views/pages/admin/CreatePost.php" class="flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold shadow-sm hover:bg-primary/90 transition-colors">
                        <span class="material-symbols-outlined text-xl">add_circle</span>
                        <span class="truncate">Thêm bài viết mới</span>
                    </a>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <div class="flex flex-col md:flex-row gap-4 justify-between">
                        <form method="GET" action="/app/views/pages/admin/PostManagement.php" class="w-full md:w-1/2">
                            <label class="flex flex-col w-full">
                                <div class="flex w-full items-stretch rounded-lg h-10 border border-gray-300 bg-gray-50 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
                                    <div class="text-gray-500 flex items-center justify-center pl-3">
                                        <span class="material-symbols-outlined text-xl">search</span>
                                    </div>
                                    <input 
                                        name="q"
                                        value="<?= htmlspecialchars($search) ?>"
                                        class="form-input flex w-full min-w-0 flex-1 border-none bg-transparent h-full placeholder:text-gray-500 px-3 text-sm focus:ring-0" 
                                        placeholder="Tìm theo tiêu đề bài viết..." 
                                    />
                                </div>
                            </label>
                        </form>

                        <div class="flex gap-2 overflow-x-auto pb-1 md:pb-0">
                             <select class="form-select h-10 rounded-lg border-gray-300 text-sm focus:border-primary focus:ring-primary bg-white">
                                <option>Trạng thái: Tất cả</option>
                                <option>Đã đăng</option>
                                <option>Nháp</option>
                             </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">ID</th>
                                    <th class="px-6 py-3 font-semibold w-1/3">Tiêu đề</th>
                                    <th class="px-6 py-3 font-semibold">Hình ảnh</th>
                                    <th class="px-6 py-3 font-semibold">Hiển thị</th>
                                    <th class="px-6 py-3 font-semibold">Ngày tạo</th>
                                    <th class="px-6 py-3 text-right font-semibold">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (empty($posts)): ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                            Chưa có bài viết nào.
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($posts as $post): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-mono font-medium text-primary">#<?= $post->id ?></td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900 line-clamp-2" title="<?= htmlspecialchars($post->name) ?>">
                                            <?= htmlspecialchars($post->name) ?>
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1 truncate max-w-xs">
                                            <?= htmlspecialchars($post->slug) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if (!empty($post->thumb_url)): ?>
                                            <div class="w-16 h-10 bg-cover bg-center rounded-md border border-gray-200" style="background-image: url('<?= htmlspecialchars($post->thumb_url) ?>');"></div>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($post->visibility === 'public'): ?>
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                Công khai
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                                Riêng tư
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">
                                        <?= isset($post->created_at) ? date('d/m/Y', strtotime($post->created_at)) : 'N/A' ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="/app/views/pages/admin/EditPost.php?id=<?= $post->id ?>" class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-yellow-600 transition-colors" title="Sửa bài viết">
                                                <span class="material-symbols-outlined text-xl">edit</span>
                                            </a>
                                            
                                            <form method="POST" action="/app/views/pages/admin/PostManagement.php" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $post->id ?>">
                                                <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-gray-500 hover:text-red-600 transition-colors" title="Xóa bài viết">
                                                    <span class="material-symbols-outlined text-xl">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                    <div class="flex flex-wrap items-center justify-between gap-4 p-4 border-t border-gray-200 bg-gray-50">
                        <p class="text-sm text-gray-600">
                            Trang <span class="font-semibold text-gray-900"><?= $page ?></span> / <span class="font-semibold text-gray-900"><?= $totalPages ?></span>
                        </p>
                        
                        <?php 
                            $qs = "?q=" . urlencode($search);
                            $prev = $page > 1 ? $page - 1 : 1;
                            $next = $page < $totalPages ? $page + 1 : $totalPages;
                        ?>

                        <div class="flex items-center gap-2">
                            <a href="<?= "/app/views/pages/admin/PostManagement.php" . $qs . "&page=" . $prev ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 <?= $page <= 1 ? 'pointer-events-none opacity-50' : '' ?>">
                                <span class="material-symbols-outlined text-base">chevron_left</span>
                            </a>
                            
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-primary bg-primary text-white text-sm font-semibold shadow-sm">
                                <?= $page ?>
                            </span>

                            <a href="<?= "/app/views/pages/admin/PostManagement.php" . $qs . "&page=" . $next ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 <?= $page >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>">
                                <span class="material-symbols-outlined text-base">chevron_right</span>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>