<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["AdminProductController"]); 

$controller = new AdminProductController();
// Hàm edit() trong controller sẽ tự xử lý POST update
$data = $controller->edit(); 

$product = $data['product'] ?? null;
$error = $data['error'] ?? null;
$categories = $data['categories'];
$branches = $data['branches'];

// Lấy danh sách ảnh hiện tại để JS xử lý
$currentImages = [];
if ($product && !empty($product->productImages)) {
    foreach ($product->productImages as $img) {
        $currentImages[] = htmlspecialchars($img->url);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Sửa sản phẩm"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
  <div class="relative flex min-h-screen w-full flex-row overflow-x-hidden">
    <?php Import::layout('AdminSidebar', ["active" => "products"]); ?>

    <main class="flex-1 p-6 lg:p-10">
      <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
          <div class="flex flex-col">
            <h1 class="text-3xl font-bold text-primary">Sửa sản phẩm</h1>
            <p class="text-sm text-gray-500">ID #<?= htmlspecialchars($product->id ?? '—') ?></p>
          </div>
          <div class="flex items-center gap-3">
            <a class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-colors" href="/app/views/pages/admin/ProductManagement.php">
              Hủy bỏ
            </a>
            <button form="product-form" class="px-4 py-2 text-sm font-medium text-white bg-primary border border-transparent rounded-lg shadow-sm hover:bg-primary/90 flex items-center gap-2 transition-colors">
              <span class="material-symbols-outlined text-[18px]">save</span>
              Lưu thay đổi
            </button>
          </div>
        </div>

        <?php if (!empty($error)): ?>
          <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <?php if (!$product): ?>
          <p class="text-center text-gray-500 py-10">Không tìm thấy sản phẩm.</p>
        <?php else: ?>

          <form id="product-form" method="POST" action="/app/views/pages/admin/EditProduct.php?id=<?= urlencode($product->id) ?>" enctype="multipart/form-data">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              
              <div class="lg:col-span-2 flex flex-col gap-6">
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                  <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900">Thông tin chung</h2>
                  </div>
                  <div class="p-6 space-y-5">
                    <label class="flex flex-col gap-1.5">
                      <span class="text-sm font-semibold text-gray-700">Tên sản phẩm <span class="text-red-500">*</span></span>
                      <input name="name" value="<?= htmlspecialchars($product->name ?? '') ?>" class="form-input w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary h-11 px-4 text-sm" placeholder="Ví dụ: Đàn Guitar Acoustic" required />
                    </label>
                    <label class="flex flex-col gap-1.5">
                      <span class="text-sm font-semibold text-gray-700">Mô tả chi tiết</span>
                      <textarea name="description" class="form-textarea w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary min-h-[180px] px-4 py-3 text-sm leading-relaxed" placeholder="Nhập mô tả sản phẩm..."><?= htmlspecialchars($product->description ?? '') ?></textarea>
                    </label>
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

                    <div id="remove-images-container"></div>
                  </div>
                </div>
              </div>

              <div class="lg:col-span-1 flex flex-col gap-6">
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                  <div class="p-5 border-b border-gray-100">
                    <h2 class="text-base font-bold text-gray-900">Phân loại</h2>
                  </div>
                  <div class="p-5 space-y-4">
                    <label class="flex flex-col gap-1.5">
                      <span class="text-sm font-medium text-gray-700">Danh mục</span>
                      <select name="product_category_id" class="form-select w-full rounded-lg border-gray-300 text-sm focus:border-primary focus:ring-primary h-10">
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                          <option value="<?= htmlspecialchars($cat->id) ?>" <?= ($product->product_category_id ?? null) == $cat->id ? 'selected' : '' ?>><?= htmlspecialchars($cat->name) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </label>
                    <label class="flex flex-col gap-1.5">
                      <span class="text-sm font-medium text-gray-700">Thương hiệu</span>
                      <select name="branch_id" class="form-select w-full rounded-lg border-gray-300 text-sm focus:border-primary focus:ring-primary h-10">
                        <option value="">-- Chọn thương hiệu --</option>
                        <?php foreach ($branches as $b): ?>
                          <option value="<?= htmlspecialchars($b->id) ?>" <?= ($product->branch_id ?? null) == $b->id ? 'selected' : '' ?>><?= htmlspecialchars($b->name) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </label>
                  </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                  <div class="p-5 border-b border-gray-100">
                    <h2 class="text-base font-bold text-gray-900">Giá bán & Tồn kho</h2>
                  </div>
                  <div class="p-5 space-y-4">
                    
                    <label class="flex flex-col gap-1.5">
                      <span class="text-sm font-medium text-gray-700">Giá gốc (VND)</span>
                      <div class="relative">
                          <input name="price_original" id="price_original" value="<?= htmlspecialchars($product->price_original ?? 0) ?>" type="number" class="form-input w-full rounded-lg border-gray-300 h-10 pl-3 pr-10 text-sm font-medium" placeholder="0" oninput="calculatePrice()" />
                          <span class="absolute right-3 top-2.5 text-gray-400 text-xs">₫</span>
                      </div>
                    </label>
                    
                    <div class="flex gap-3">
                        <label class="flex flex-col gap-1.5 flex-1">
                          <span class="text-sm font-medium text-gray-700">Giảm giá (%)</span>
                          <input name="discount_percent" id="discount_percent" value="<?= htmlspecialchars($product->discount_percent ?? 0) ?>" type="number" min="0" max="100" class="form-input w-full rounded-lg border-gray-300 h-10 px-3 text-sm text-center" oninput="calculatePrice()" />
                        </label>
                        <label class="flex flex-col gap-1.5 flex-1">
                          <span class="text-sm font-medium text-gray-700">Tồn kho</span>
                          <input name="quantity" value="<?= htmlspecialchars($product->quantity ?? 0) ?>" type="number" min="0" class="form-input w-full rounded-lg border-gray-300 h-10 px-3 text-sm text-center" />
                        </label>
                    </div>

                    <div class="mt-2 p-3 bg-blue-50 rounded-lg border border-blue-100 flex justify-between items-center">
                        <span class="text-xs font-medium text-blue-600">Giá bán ra:</span>
                        <span id="preview_price" class="text-base font-bold text-blue-700">
                            <?= number_format($product->price_current ?? 0, 0, ',', '.') ?> ₫
                        </span>
                    </div>

                  </div>
                </div>

              </div> </div> </form>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <script>
    // 1. Script tính giá (Giữ nguyên logic cũ)
    function calculatePrice() {
        let original = parseFloat(document.getElementById('price_original').value) || 0;
        let percent = parseFloat(document.getElementById('discount_percent').value) || 0;
        if(percent > 100) percent = 100; if(percent < 0) percent = 0;
        let current = original * (1 - percent / 100);
        let formatted = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(current);
        document.getElementById('preview_price').innerText = formatted;
    }

    // 2. SCRIPT QUẢN LÝ ẢNH (NÂNG CAO)
    // Dữ liệu ảnh cũ từ PHP
    let existingImages = <?= json_encode($currentImages) ?>; 
    let newFiles = new DataTransfer(); // Chứa file mới upload

    const gallery = document.getElementById('gallery-container');
    const removeInputContainer = document.getElementById('remove-images-container');

    // Hàm render lại toàn bộ gallery
    function renderGallery() {
        gallery.innerHTML = '';

        // A. Render ảnh cũ (từ DB)
        existingImages.forEach((url, index) => {
            const div = document.createElement('div');
            div.className = 'group relative aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200 shadow-sm transition-transform hover:shadow-md';
            div.innerHTML = `
                <img src="${url}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                    <button type="button" onclick="markRemoveImage('${url}', ${index})" class="bg-white text-red-600 p-1.5 rounded-full hover:bg-red-50" title="Xóa ảnh này">
                        <span class="material-symbols-outlined text-[18px] block">delete</span>
                    </button>
                </div>
                <div class="absolute top-2 left-2 bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">Đã lưu</div>
            `;
            gallery.appendChild(div);
        });

        // B. Render ảnh mới (Preview từ Input)
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
            // Kiểm tra trùng lặp cơ bản (theo tên) hoặc size nếu cần
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

    // Đánh dấu xóa ảnh cũ (Tạo input hidden để gửi về server)
    window.markRemoveImage = function(url, index) {
        // Thêm input hidden
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'remove_images[]';
        input.value = url;
        removeInputContainer.appendChild(input);

        // Xóa khỏi mảng hiển thị
        existingImages.splice(index, 1);
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