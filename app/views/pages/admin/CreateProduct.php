<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["AdminProductController"]); // Import Controller Mới

$controller = new AdminProductController();
// Hàm create() trong controller sẽ tự xử lý POST và redirect nếu thành công
$data = $controller->create(); 

$error = $data['error'] ?? null;
$categories = $data['categories'];
$branches = $data['branches']
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Thêm sản phẩm"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
  <div class="relative flex min-h-screen w-full flex-row overflow-x-hidden">
    <?php Import::layout('AdminSidebar', ["active" => "products"]); ?>

    <main class="flex-1 p-6 lg:p-10">
      <div class="max-w-7xl mx-auto">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
          <h1 class="text-3xl font-bold text-primary">Thêm sản phẩm mới</h1>
          <div class="flex items-center gap-3">
            <a class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50" href="/app/views/pages/admin/ProductManagement.php">Hủy</a>
            <button form="product-form" class="px-4 py-2 text-sm font-medium text-white bg-primary border border-transparent rounded-lg shadow-sm hover:bg-primary/90 flex items-center gap-2">
              <span class="material-symbols-outlined text-[18px]">save</span>
              Lưu Sản phẩm
            </button>
          </div>
        </div>

        <?php if (!empty($error)): ?>
          <p class="text-sm text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form id="product-form" method="POST" action="/app/views/pages/admin/CreateProduct.php" enctype="multipart/form-data">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 flex flex-col gap-8">
              <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 space-y-6">
                  <h2 class="text-lg font-semibold text-gray-900">Thông tin chung</h2>
                  <div class="space-y-4">
                    <label class="flex flex-col gap-2">
                      <span class="text-sm font-medium text-gray-800">Tên sản phẩm</span>
                      <input name="name" class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm" placeholder="Ví dụ: Đàn Guitar Acoustic" required />
                    </label>
                    <label class="flex flex-col gap-2">
                      <span class="text-sm font-medium text-gray-800">Mô tả</span>
                      <textarea name="description" class="form-textarea w-full rounded-lg border border-gray-300 bg-white min-h-40 px-4 py-3 text-sm" placeholder="Nhập mô tả chi tiết cho sản phẩm..."></textarea>
                    </label>
                  </div>
                </div>
              </div>

              <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                  <h2 class="text-lg font-bold text-gray-900">Hình ảnh sản phẩm</h2>
                  <span class="text-xs text-gray-500">Kéo thả để sắp xếp (Coming soon)</span>
                </div>
                
                <div class="p-6">
                  <div id="gallery-container" class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                      </div>

                  <input type="file" id="upload-input" name="images[]" multiple accept="image/*" class="hidden" onchange="handleFiles(this.files)">
                  
                  <label for="upload-input" id="drop-zone" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer bg-gray-50 hover:bg-blue-50 hover:border-blue-400 transition-all group">
                      <div class="flex flex-col items-center justify-center pt-5 pb-6">
                          <div class="p-2 rounded-full bg-white shadow-sm mb-2 group-hover:scale-110 transition-transform">
                              <span class="material-symbols-outlined text-2xl text-primary">cloud_upload</span>
                          </div>
                          <p class="mb-1 text-sm text-gray-600"><span class="font-semibold text-primary">Nhấn để tải lên</span> hoặc kéo thả vào đây</p>
                          <p class="text-xs text-gray-400">Hỗ trợ PNG, JPG, WEBP (Tối đa 5MB)</p>
                      </div>
                  </label>
                </div>
              </div>
            </div>

            <div class="lg:col-span-1 flex flex-col gap-8">
              <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 space-y-4">
                  <h2 class="text-lg font-semibold text-gray-900">Giá &amp; Tồn kho</h2>
                  <div class="grid grid-cols-1 gap-4">
                      <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-gray-800">Giá gốc (VND)</span>
                        <input name="price_original" id="price_original" class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm" type="number" min="0" step="1000" placeholder="0" oninput="calculatePrice()" />
                      </label>
                      
                      <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-gray-800">% Giảm giá</span>
                        <input name="discount_percent" id="discount_percent" class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm" type="number" min="0" max="100" step="1" placeholder="0" oninput="calculatePrice()" />
                      </label>

                      <div class="p-3 bg-blue-50 rounded-lg border border-blue-100 flex justify-between items-center">
                          <span class="text-sm text-blue-600">Giá bán sau giảm:</span>
                          <span id="preview_price" class="text-lg font-bold text-blue-800 ml-2">0 ₫</span>
                      </div>

                      <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-gray-800">Số lượng tồn kho (SKU)</span>
                        <input name="quantity" class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm" type="number" min="0" placeholder="100" />
                      </label>
                  </div>
                </div>
              </div>

              <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 space-y-6">
                  <h2 class="text-lg font-semibold text-gray-900">Phân loại</h2>
                  <div class="space-y-4">
                    <label class="flex flex-col gap-2">
                      <span class="text-sm font-medium text-gray-800">Danh mục</span>
                      <select name="product_category_id" class="form-select w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm">
                        <option value="">Chọn danh mục</option>
                        <?php foreach ($categories as $cat): ?>
                          <option value="<?= htmlspecialchars($cat->id) ?>"><?= htmlspecialchars($cat->name) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </label>
                    <label class="flex flex-col gap-2">
                      <span class="text-sm font-medium text-gray-800">Thương hiệu</span>
                      <select name="branch_id" class="form-select w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm">
                        <option value="">Chọn thương hiệu</option>
                        <?php foreach ($branches as $b): ?>
                          <option value="<?= htmlspecialchars($b->id) ?>"><?= htmlspecialchars($b->name) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script>
    // 1. Script tính giá
    function calculatePrice() {
        let original = parseFloat(document.getElementById('price_original').value) || 0;
        let percent = parseFloat(document.getElementById('discount_percent').value) || 0;
        
        if(percent > 100) percent = 100;
        if(percent < 0) percent = 0;

        let current = original * (1 - percent / 100);
        
        // Format tiền Việt Nam
        let formatted = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(current);
        document.getElementById('preview_price').innerText = formatted;
    }

    // 2. SCRIPT QUẢN LÝ ẢNH (GIỐNG EDIT PRODUCT)
    let newFiles = new DataTransfer(); // Chứa file mới upload

    const gallery = document.getElementById('gallery-container');

    // Hàm render lại toàn bộ gallery
    function renderGallery() {
        gallery.innerHTML = '';

        // Render ảnh mới (Preview từ Input)
        Array.from(newFiles.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'group relative aspect-square bg-gray-100 rounded-lg overflow-hidden border border-green-200 ring-2 ring-green-500/20 shadow-sm';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover opacity-90">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <button type="button" onclick="removeNewFile(${index})" class="bg-white text-red-600 p-1.5 rounded-full hover:bg-red-50" title="Hủy tải lên">
                            <span class="material-symbols-outlined text-[18px] block">close</span>
                        </button>
                    </div>
                    <div class="absolute top-2 right-2 bg-green-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">Mới</div>
                `;
                gallery.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    }

    // Xử lý khi chọn file mới
    function handleFiles(files) {
        if (!files) return;
        Array.from(files).forEach(file => {
            // Có thể thêm kiểm tra size/type tại đây
            newFiles.items.add(file);
        });
        document.getElementById('upload-input').files = newFiles.files; // Update input
        renderGallery();
    }

    // Xóa file mới chọn (chưa upload)
    window.removeNewFile = function(index) {
        const dt = new DataTransfer();
        const inputFiles = newFiles.files;
        for (let i = 0; i < inputFiles.length; i++) {
            if (index !== i) dt.items.add(inputFiles[i]);
        }
        newFiles = dt;
        document.getElementById('upload-input').files = newFiles.files;
        renderGallery();
    }

    // Drag & Drop Effect
    const dropZone = document.getElementById('drop-zone');
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => { e.preventDefault(); e.stopPropagation(); }, false);
    });
    dropZone.addEventListener('dragenter', () => dropZone.classList.add('border-primary', 'bg-blue-50'));
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-primary', 'bg-blue-50'));
    dropZone.addEventListener('drop', (e) => {
        dropZone.classList.remove('border-primary', 'bg-blue-50');
        handleFiles(e.dataTransfer.files);
    });

    // Init
    renderGallery();
  </script>
</body>
</html>