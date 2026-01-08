<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["ProductValuationController"]);

$controller = new ProductValuationController();
$data = $controller->create(); // Gọi hàm lấy dữ liệu

$item = $data['item'];
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Đánh giá sản phẩm"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
  <?php Import::layout("UserNavigation") ?>

  <main class="flex-1 w-full max-w-2xl mx-auto px-4 py-8 min-h-screen">
    
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/app/views/pages/User.php?tab=valuations" class="hover:text-primary flex items-center gap-1">
            <span class="material-symbols-outlined text-lg">arrow_back</span> Trở lại
        </a>
        <span>/</span>
        <span class="font-medium text-gray-800">Viết đánh giá</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-white">
            <h1 class="text-lg font-bold text-gray-900">Đánh giá sản phẩm</h1>
            <p class="text-xs text-gray-500 mt-1">Đơn hàng #<?= $item['order_id'] ?> - Mua ngày <?= date('d/m/Y', strtotime($item['order_date'])) ?></p>
        </div>

        <form action="/app/controllers/ProductValuationController.php?action=store" method="POST" class="p-6">
            <input type="hidden" name="order_id" value="<?= $item['order_id'] ?>">
            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">

            <div class="flex gap-4 mb-8">
                <img src="<?= $item['product_image'] ?? 'https://via.placeholder.com/100' ?>" class="w-16 h-16 object-cover rounded border border-gray-100">
                <div>
                    <h3 class="font-medium text-gray-900"><?= htmlspecialchars($item['product_name']) ?></h3>
                    <p class="text-sm text-gray-500 mt-1">Phân loại: Mặc định</p>
                </div>
            </div>

            <div class="flex flex-col items-center gap-3 mb-8">
                <label class="font-bold text-gray-900 text-base">Chất lượng sản phẩm</label>
                <div class="flex gap-4 text-4xl text-gray-300 cursor-pointer" id="star-container">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <span class="material-symbols-outlined fill-current hover:text-yellow-400 transition-colors" data-val="<?= $i ?>" style="font-variation-settings: 'FILL' <?= $i==5?1:0 ?>;">star</span>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="star_rate" id="star-input" value="5">
                <p id="star-text" class="text-orange-500 font-bold text-lg">Tuyệt vời</p>
            </div>

            <div class="space-y-4">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <textarea name="content" rows="6" class="w-full bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400 resize-none" placeholder="Hãy chia sẻ nhận xét cho sản phẩm này bạn nhé! (Chất lượng, đóng gói, v.v...)"></textarea>
                    
                    </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Hiển thị tên đăng nhập trên đánh giá này</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                    </label>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-primary hover:bg-primary/90 text-white font-bold rounded-lg shadow-md transition-transform hover:scale-[1.02]">
                    Gửi Đánh Giá
                </button>
            </div>

        </form>
    </div>
  </main>

  <?php Import::layout("Footer") ?>

  <script>
    // Xử lý chọn sao
    const stars = document.querySelectorAll('#star-container span');
    const starInput = document.getElementById('star-input');
    const starText = document.getElementById('star-text');
    const texts = {1: 'Tệ', 2: 'Không hài lòng', 3: 'Bình thường', 4: 'Hài lòng', 5: 'Tuyệt vời'};

    stars.forEach(star => {
        star.addEventListener('click', function() {
            const val = this.getAttribute('data-val');
            starInput.value = val;
            starText.innerText = texts[val];
            
            stars.forEach(s => {
                const sVal = s.getAttribute('data-val');
                if (sVal <= val) {
                    s.style.color = '#FACC15'; // Vàng
                    s.style.fontVariationSettings = "'FILL' 1";
                } else {
                    s.style.color = '#D1D5DB'; // Xám
                    s.style.fontVariationSettings = "'FILL' 0";
                }
            });
        });
    });
  </script>
  <?php Import::component('SocialWidget'); ?>
</body>
</html>