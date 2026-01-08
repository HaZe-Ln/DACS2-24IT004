<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
// Gọi OrderController thay vì CartController
Import::controllers(["OrderController"]);

$controller = new OrderController();
$data = $controller->checkout(); // Hàm này trả về mảng dữ liệu

// Hứng dữ liệu
$user        = $data['user'];
$addresses   = $data['addresses'];
$cartItems   = $data['cartItems'];
$subtotal    = $data['subtotal'];
$shippingFee = $data['shippingFee'];
$total       = $data['total'];
$isDirect    = $data['isDirect'] ?? false;
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Thanh toán"]); ?>

<body class="font-display bg-background-light text-text-light">
  <?php Import::layout("UserNavigation") ?>

  <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    <div class="flex flex-col gap-8">
      
      <nav class="flex flex-wrap gap-2 text-sm text-gray-600">
        <a class="hover:text-primary" href="/app/views/pages/Cart.php">Giỏ hàng</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Thanh toán</span>
      </nav>

      <h1 class="text-4xl font-black text-primary leading-tight">Thanh toán & Giao hàng</h1>

      <form action="/app/controllers/OrderController.php?action=placeOrder" method="POST" class="grid grid-cols-1 lg:grid-cols-5 gap-12">
        
        <div class="lg:col-span-3 flex flex-col gap-10">
          
          <section>
            <h2 class="text-[22px] font-bold text-text-light pb-5 flex justify-between items-center">
                Thông tin nhận hàng
                <a href="/app/views/pages/User.php?tab=address" class="text-sm text-primary font-medium hover:underline">Quản lý địa chỉ</a>
            </h2>
            
            <?php if (empty($addresses)): ?>
                <div class="p-4 border border-yellow-200 bg-yellow-50 rounded-lg text-yellow-800">
                    Bạn chưa có địa chỉ nào. <a href="/app/views/pages/User.php?tab=address" class="font-bold underline">Thêm địa chỉ ngay</a>
                </div>
            <?php else: ?>
                <div class="flex flex-col gap-4">
                    <?php foreach ($addresses as $index => $addr): ?>
                        <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-all hover:border-primary/50 [&:has(:checked)]:border-primary [&:has(:checked)]:bg-primary/5">
                            <input type="radio" name="address_id" value="<?= $addr->id ?>" class="mt-1 w-4 h-4 text-primary focus:ring-primary" <?= $index === 0 ? 'checked' : '' ?>>
                            <div class="ml-4 flex-1">
                                <span class="text-text-light font-bold"><?= htmlspecialchars($user->name) ?></span> 
                                <span class="text-gray-500 text-sm ml-2">| <?= htmlspecialchars($addr->phone) ?></span>
                                <p class="text-gray-600 text-sm mt-1">
                                    <?= htmlspecialchars($addr->address) ?>, 
                                    <?= htmlspecialchars($addr->ward) ?>, 
                                    <?= htmlspecialchars($addr->city) ?>
                                </p>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($isDirect): ?>
              <input type="hidden" name="is_direct" value="1">
              <input type="hidden" name="direct_product_id" value="<?= $cartItems[0]->product->id ?>">
              <input type="hidden" name="direct_quantity" value="<?= $cartItems[0]->quantity ?>">
            <?php endif; ?>
          </section>

          <section>
            <h2 class="text-[22px] font-bold text-text-light pb-5">Phương thức vận chuyển</h2>
            <div class="flex flex-col gap-4">
              <label class="flex items-start p-4 border border-primary bg-primary/5 rounded-lg cursor-pointer">
                <input checked class="mt-1 w-4 h-4 text-primary focus:ring-primary" name="shipping_method" value="standard" type="radio" />
                <div class="ml-4 flex-1 flex justify-between items-start">
                  <div>
                    <span class="text-text-light font-medium">Giao hàng Tiêu chuẩn</span>
                    <p class="text-gray-600 text-sm mt-1">Dự kiến giao hàng: 3-5 ngày làm việc</p>
                  </div>
                  <span class="text-gray-700 font-semibold"><?= number_format($shippingFee, 0, ',', '.') ?>₫</span>
                </div>
              </label>
            </div>
          </section>

          <section>
            <h2 class="text-[22px] font-bold text-text-light pb-5">Phương thức thanh toán</h2>
            <div class="flex flex-col gap-4">
              
              <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-primary transition-all has-[:checked]:border-primary has-[:checked]:bg-blue-50">
                <input checked class="w-4 h-4 text-primary focus:ring-primary" name="payment_method" value="cod" type="radio" onchange="toggleQR('cod')" />
                <div class="ml-4 flex-1">
                  <span class="text-text-light font-medium">Thanh toán khi nhận hàng (COD)</span>
                </div>
                <span class="material-symbols-outlined text-gray-400">payments</span>
              </label>

              <label class="flex flex-col p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-primary transition-all has-[:checked]:border-primary has-[:checked]:bg-blue-50">
                <div class="flex items-center w-full">
                    <input class="w-4 h-4 text-primary focus:ring-primary" name="payment_method" value="bank_transfer" type="radio" onchange="toggleQR('bank')" />
                    <div class="ml-4 flex-1">
                      <span class="text-text-light font-medium">Chuyển khoản Ngân hàng (QR)</span>
                    </div>
                    <span class="material-symbols-outlined text-gray-400">qr_code_scanner</span>
                </div>

                <div id="qr-container" class="hidden mt-4 pt-4 border-t border-gray-200 w-full">
                    <div class="bg-white p-3 rounded-lg border border-gray-300 shadow-sm text-center">
                        <p class="text-sm text-blue-600 font-bold mb-2">Quét mã để thanh toán</p>
                        <img id="qr-image" src="" alt="QR Code" class="w-48 h-48 mx-auto object-contain">
                        <p class="text-xs text-gray-500 mt-2 italic">
                            Nội dung: <span class="font-bold text-gray-800">THANH TOAN MUA HANG</span><br>
                            (Vui lòng chờ giây lát để QR cập nhật giá tiền)
                        </p>
                    </div>
                </div>
              </label>

            </div>
          </section>
        </div>

        <div class="lg:col-span-2">
          <div class="sticky top-24 bg-white border border-gray-200 rounded-xl p-6 flex flex-col gap-6 shadow-sm">
            <h2 class="text-[22px] font-bold text-text-light">Đơn hàng của bạn</h2>
            
            <div class="flex flex-col gap-4 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                <?php foreach ($cartItems as $item): ?>
                    <?php 
                        $p = $item->product;
                        $img = !empty($p->productImages) ? $p->productImages[0]->url : 'https://via.placeholder.com/150';
                    ?>
                    <div class="flex items-center gap-4">
                        <div class="relative shrink-0">
                            <img class="w-16 h-16 rounded-lg object-cover border border-gray-100" src="<?= $img ?>" alt="<?= htmlspecialchars($p->name) ?>">
                            <span class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-gray-600 text-white text-xs font-bold"><?= $item->quantity ?></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-text-light font-medium line-clamp-2 text-sm"><?= htmlspecialchars($p->name) ?></p>
                            <p class="text-gray-500 text-xs mt-1">Loại: <?= htmlspecialchars($p->productCategory->name ?? 'N/A') ?></p>
                        </div>
                        <p class="text-gray-800 font-semibold text-sm"><?= number_format($p->price_current * $item->quantity, 0, ',', '.') ?>₫</p>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr class="border-gray-100">

            <div class="flex flex-col gap-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Tạm tính</span>
                    <span class="text-gray-800 font-medium"><?= number_format($subtotal, 0, ',', '.') ?>₫</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Phí vận chuyển</span>
                    <span class="text-gray-800 font-medium"><?= number_format($shippingFee, 0, ',', '.') ?>₫</span>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4 flex justify-between items-center">
                <span class="text-text-light text-lg font-bold">Tổng cộng</span>
                <span class="text-primary text-2xl font-black"><?= number_format($total, 0, ',', '.') ?>₫</span>
            </div>

            <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-lg h-14 bg-primary text-white text-lg font-bold hover:bg-primary/90 transition-transform hover:scale-[1.02] shadow-md disabled:opacity-50 disabled:cursor-not-allowed" <?= empty($addresses) ? 'disabled title="Vui lòng thêm địa chỉ"' : '' ?>>
                Đặt hàng ngay
            </button>

            <div class="flex items-center justify-center gap-2 text-xs text-gray-500">
                <span class="material-symbols-outlined !text-sm">lock</span>
                <span>Thông tin được bảo mật tuyệt đối</span>
            </div>
          </div>
        </div>

      </form>
    </div>
  </main>

  <?php Import::layout("Footer") ?>
  <script>
    // Cấu hình Tài khoản ngân hàng của bạn
    const MY_BANK = {
        ID: 'MB',           // Mã ngân hàng (MB, VCB, TPB, TCB...)
        ACC: '0383028421',  // Số tài khoản
        NAME: 'LE CAO SON TIEN'   // Tên chủ tài khoản
    };

    // Biến lưu tổng tiền hiện tại
    let currentTotal = <?= $total ?>; 

    function toggleQR(method) {
        const qrContainer = document.getElementById('qr-container');
        if (method === 'bank') {
            qrContainer.classList.remove('hidden');
            updateQRImage(); // Tạo QR ngay khi bấm
        } else {
            qrContainer.classList.add('hidden');
        }
    }

    function updateQRImage() {
        // Nội dung chuyển khoản: Vì chưa có mã đơn hàng nên để nội dung chung
        // Hoặc bạn có thể thêm SĐT người mua vào: "TT MUA HANG 098xxx"
        const content = "THANH TOAN MUA HANG"; 
        
        // Link API VietQR
        const qrUrl = `https://img.vietqr.io/image/${MY_BANK.ID}-${MY_BANK.ACC}-compact.png?amount=${currentTotal}&addInfo=${encodeURIComponent(content)}&accountName=${encodeURIComponent(MY_BANK.NAME)}`;
        
        document.getElementById('qr-image').src = qrUrl;
    }

    function updateTotal(shippingFee) {
        let subtotal = <?= $subtotal ?>; 
        
        // Cập nhật biến toàn cục currentTotal
        currentTotal = subtotal + shippingFee;

        // Cập nhật giao diện tiền
        let fmtShipping = new Intl.NumberFormat('vi-VN').format(shippingFee);
        let fmtTotal = new Intl.NumberFormat('vi-VN').format(currentTotal);

        document.getElementById('display-shipping').innerText = fmtShipping + '₫';
        document.getElementById('display-total').innerText = fmtTotal + '₫';

        // Nếu đang chọn Bank Transfer thì cập nhật lại ảnh QR theo giá mới
        const isBank = document.querySelector('input[name="payment_method"][value="bank_transfer"]').checked;
        if (isBank) {
            updateQRImage();
        }
    }
  </script>
  <?php Import::component('SocialWidget'); ?>
</body>
</html>