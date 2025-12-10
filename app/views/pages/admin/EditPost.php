<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["PostRepository"]);

$error = null;
$id = $_GET['id'] ?? null;

// 1. Kiểm tra ID hợp lệ
if (!$id) {
    header("Location: /app/views/pages/admin/PostManagement.php");
    exit;
}

// 2. Lấy thông tin bài viết hiện tại
$post = PostRepository::getById($id);
if (!$post) {
    echo "Bài viết không tồn tại.";
    exit;
}

// Helper: Slugify (Giữ nguyên từ trang Create)
function createSlug($str) {
    $str = trim(mb_strtolower($str));
    $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
    $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
    $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
    $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
    $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
    $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
    $str = preg_replace('/(đ)/', 'd', $str);
    $str = preg_replace('/[^a-z0-9-\s]/', '', $str);
    $str = preg_replace('/([\s]+)/', '-', $str);
    return $str;
}

// Helper: Upload ảnh
function uploadPostImage() {
    if (empty($_FILES['thumb_image']['name'])) return null;
    
    $targetDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/posts/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    
    $fileName = time() . "_" . basename($_FILES["thumb_image"]["name"]);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    if(!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) return null;

    if (move_uploaded_file($_FILES["thumb_image"]["tmp_name"], $targetFile)) {
        return "/uploads/posts/" . $fileName;
    }
    return null;
}

// 3. Xử lý CẬP NHẬT (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $visibility = $_POST['visibility'] ?? 'private';
    
    if (empty($title)) {
        $error = "Tiêu đề bài viết không được để trống.";
    } else {
        // Cập nhật thông tin vào object $post
        $post->name = $title;
        // Tùy chọn: Có cập nhật slug khi đổi tên không? (Thường là có)
        $post->slug = createSlug($title);
        $post->content = $content;
        $post->visibility = $visibility;

        // Xử lý ảnh: Nếu có upload mới thì lấy link mới, không thì giữ nguyên link cũ
        $newThumb = uploadPostImage();
        if ($newThumb) {
            $post->thumb_url = $newThumb;
        }

        // Lưu cập nhật vào DB
        // Vì $post đã có $id, hàm Query::save() sẽ tự động hiểu là UPDATE
        Import::configs(["db/Query"]);
        Query::from("posts")->save($post);

        header("Location: /app/views/pages/admin/PostManagement.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Chỉnh sửa bài viết"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
    <div class="relative flex min-h-screen w-full">
        <?php Import::layout('AdminSidebar', ["active" => "posts"]); ?>

        <main class="flex-1 p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <div class="mb-6 flex flex-wrap gap-2 text-sm text-gray-500">
                    <a href="/app/views/pages/admin/PostManagement.php" class="hover:text-primary">Quản lý Bài viết</a>
                    <span>/</span>
                    <span class="text-gray-900 font-medium">Sửa bài viết #<?= $post->id ?></span>
                </div>

                <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">Chỉnh sửa bài viết</h1>
                    <a href="/app/views/pages/admin/PostManagement.php" class="text-sm font-medium text-gray-500 hover:text-gray-900">Quay lại danh sách</a>
                </div>

                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200 text-sm">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <label class="block mb-2 text-sm font-medium text-gray-700">Tiêu đề bài viết</label>
                            <input 
                                type="text" 
                                name="title" 
                                value="<?= htmlspecialchars($post->name) ?>"
                                required
                                class="form-input w-full rounded-lg border-gray-300 focus:ring-primary focus:border-primary h-12 text-lg font-medium placeholder-gray-400" 
                                placeholder="Nhập tiêu đề tại đây..." 
                            />
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col h-[500px]">
                            <label class="block mb-2 text-sm font-medium text-gray-700">Nội dung bài viết</label>
                            <textarea 
                                name="content" 
                                class="form-textarea w-full flex-1 rounded-lg border-gray-300 focus:ring-primary focus:border-primary p-4 text-base leading-relaxed resize-none" 
                                placeholder="Viết nội dung bài viết ở đây..."
                            ><?= htmlspecialchars($post->content) ?></textarea>
                            <p class="mt-2 text-xs text-gray-400 text-right">Hỗ trợ Markdown hoặc HTML cơ bản.</p>
                        </div>
                    </div>

                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="p-4 border-b border-gray-100 bg-gray-50">
                                <h3 class="font-semibold text-gray-800">Cập nhật</h3>
                            </div>
                            <div class="p-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                                    <select name="visibility" class="form-select w-full rounded-lg border-gray-300 focus:ring-primary focus:border-primary">
                                        <option value="private" <?= $post->visibility === 'private' ? 'selected' : '' ?>>Lưu nháp (Riêng tư)</option>
                                        <option value="public" <?= $post->visibility === 'public' ? 'selected' : '' ?>>Công khai</option>
                                    </select>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Ngày tạo: <?= date('d/m/Y H:i', strtotime($post->created_at)) ?>
                                </div>
                            </div>
                            <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
                                <a href="/app/views/pages/admin/PostManagement.php" class="text-sm text-gray-600 hover:text-gray-900 font-medium">Hủy</a>
                                <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-primary/90 shadow-sm transition-colors">
                                    Lưu Thay Đổi
                                </button>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="p-4 border-b border-gray-100 bg-gray-50">
                                <h3 class="font-semibold text-gray-800">Ảnh đại diện</h3>
                            </div>
                            <div class="p-4">
                                <div class="relative w-full aspect-video border-2 border-dashed border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex flex-col items-center justify-center cursor-pointer group overflow-hidden">
                                    <input type="file" name="thumb_image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewImage(this)">
                                    
                                    <div class="flex flex-col items-center text-gray-400 group-hover:text-primary transition-colors <?= !empty($post->thumb_url) ? 'hidden' : '' ?>" id="upload-placeholder">
                                        <span class="material-symbols-outlined text-4xl mb-2">image</span>
                                        <span class="text-sm font-medium">Tải ảnh mới</span>
                                    </div>
                                    
                                    <img id="image-preview" 
                                         src="<?= !empty($post->thumb_url) ? htmlspecialchars($post->thumb_url) : '' ?>" 
                                         class="absolute inset-0 w-full h-full object-cover pointer-events-none <?= empty($post->thumb_url) ? 'hidden' : '' ?>"
                                    >
                                </div>
                                <?php if(!empty($post->thumb_url)): ?>
                                    <p class="mt-2 text-xs text-green-600 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">check_circle</span> Đang sử dụng ảnh cũ
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            const placeholder = document.getElementById('upload-placeholder');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if(placeholder) placeholder.classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>