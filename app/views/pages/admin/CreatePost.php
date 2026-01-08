<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["PostRepository"]);

$error = null;

// Helper: Slugify (Tối ưu hóa lại một chút cho gọn)
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

// Xử lý Upload Ảnh (Giữ nguyên logic của bạn, thêm check lỗi)
function uploadPostImage() {
    if (empty($_FILES['thumb_image']['name'])) return null;
    
    // Đường dẫn lưu file
    $relativeDir = "/uploads/posts/";
    $targetDir = $_SERVER['DOCUMENT_ROOT'] . $relativeDir;
    
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    
    $fileName = time() . "_" . basename($_FILES["thumb_image"]["name"]);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    if(!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) return null;

    if (move_uploaded_file($_FILES["thumb_image"]["tmp_name"], $targetFile)) {
        return $relativeDir . $fileName; // Trả về đường dẫn tương đối để lưu DB
    }
    return null;
}

// Xử lý POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? ''; // CKEditor sẽ gửi nội dung HTML qua đây
    $visibility = $_POST['visibility'] ?? 'private';
    
    if (empty($title)) {
        $error = "Tiêu đề bài viết không được để trống.";
    } else {
        // Tạo slug và xử lý trùng lặp cơ bản bằng cách thêm timestamp nếu cần
        // (Ở đây làm đơn giản, tốt nhất là check DB xem slug tồn tại chưa)
        $slug = createSlug($title);
        
        $thumbUrl = uploadPostImage();

        // Khởi tạo Model (Đảm bảo class Post có các thuộc tính public tương ứng)
        $post = new Post();
        $post->name = $title;       // Map với cột 'name' trong DB
        $post->slug = $slug;        // Map với cột 'slug' trong DB
        $post->content = $content;  // Map với cột 'content' (longtext)
        $post->visibility = $visibility; // Map với cột 'visibility'
        
        if($thumbUrl) {
            $post->thumb_url = $thumbUrl; // Map với cột 'thumb_url'
        }

        try {
            Import::configs(["db/Query"]);
            Query::from("posts")->save($post);
            
            // Redirect thành công
            header("Location: /app/views/pages/admin/PostManagement.php");
            exit;
        } catch (Exception $e) {
            $error = "Lỗi khi lưu dữ liệu: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Thêm Bài viết"]); ?>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<style>
    /* Chỉnh chiều cao cho trình soạn thảo */
    .ck-editor__editable_inline {
        min-height: 400px;
    }
</style>

<body class="font-display bg-gray-50 text-gray-900">
    <div class="relative flex min-h-screen w-full">
        <?php Import::layout('AdminSidebar', ["active" => "posts"]); ?>

        <main class="flex-1 p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <div class="mb-6 flex flex-wrap gap-2 text-sm text-gray-500">
                    <a href="/app/views/pages/admin/PostManagement.php" class="hover:text-primary">Quản lý Bài viết</a>
                    <span>/</span>
                    <span class="text-gray-900 font-medium">Thêm bài viết mới</span>
                </div>

                <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">Thêm bài viết mới</h1>
                </div>

                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200 text-sm">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <label class="block mb-2 text-sm font-medium text-gray-700">Tiêu đề bài viết <span class="text-red-500">*</span></label>
                            <input 
                                type="text" 
                                name="title" 
                                required
                                value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                class="form-input w-full rounded-lg border-gray-300 focus:ring-primary focus:border-primary h-12 text-lg font-medium placeholder-gray-400" 
                                placeholder="Ví dụ: 10 địa điểm du lịch tuyệt nhất..." 
                            />
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <label class="block mb-2 text-sm font-medium text-gray-700">Nội dung bài viết</label>
                            <div class="prose max-w-none">
                                <textarea id="editor" name="content"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="p-4 border-b border-gray-100 bg-gray-50">
                                <h3 class="font-semibold text-gray-800">Đăng bài</h3>
                            </div>
                            <div class="p-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                                    <select name="visibility" class="form-select w-full rounded-lg border-gray-300 focus:ring-primary focus:border-primary">
                                        <option value="private">Lưu nháp (Riêng tư)</option>
                                        <option value="public">Công khai</option>
                                    </select>
                                </div>
                            </div>
                            <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
                                <a href="/app/views/pages/admin/PostManagement.php" class="text-sm text-gray-600 hover:text-gray-900 font-medium">Hủy</a>
                                <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-primary/90 shadow-sm transition-colors">
                                    Lưu & Đăng
                                </button>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="p-4 border-b border-gray-100 bg-gray-50">
                                <h3 class="font-semibold text-gray-800">Ảnh đại diện (Thumbnail)</h3>
                            </div>
                            <div class="p-4">
                                <div class="relative w-full aspect-video border-2 border-dashed border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex flex-col items-center justify-center cursor-pointer group overflow-hidden bg-gray-50">
                                    <input type="file" name="thumb_image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewImage(this)">
                                    
                                    <div class="flex flex-col items-center text-gray-400 group-hover:text-primary transition-colors z-0" id="upload-placeholder">
                                        <span class="material-symbols-outlined text-4xl mb-2">image</span>
                                        <span class="text-sm font-medium">Tải ảnh lên</span>
                                    </div>
                                    
                                    <img id="image-preview" src="" class="absolute inset-0 w-full h-full object-cover hidden z-0 pointer-events-none">
                                </div>
                                <p class="mt-2 text-xs text-gray-400 text-center">Hỗ trợ: JPG, PNG, WEBP</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Kích hoạt CKEditor
        ClassicEditor
            .create(document.querySelector('#editor'), {
                toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo' ]
            })
            .catch(error => {
                console.error(error);
            });

        // Script xem trước ảnh
        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            const placeholder = document.getElementById('upload-placeholder');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>