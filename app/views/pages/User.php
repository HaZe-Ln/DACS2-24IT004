<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["UserController", "UserAddressController"]);
Import::middlewares(["Authentication"]);
Import::repositories(['OrderRepository']);


// 1. Check Login
$currentUser = Authentication::getAuthentication();
if (!$currentUser) {
    header("Location: /app/views/pages/auth/SignIn.php");
    exit;
}

$message = null;
$autoOpenAddress = false; 

// 2. Xử lý POST Action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $userCtrl = new UserController();
        $message = $userCtrl->update($currentUser);
    }
    elseif ($action === 'add_address') {
        $addrCtrl = new UserAddressController();
        $message = $addrCtrl->store($currentUser->id);
        if($message['type'] === 'success') $autoOpenAddress = true; 
    }
    elseif ($action === 'delete_address') {
        $addrCtrl = new UserAddressController();
        $message = $addrCtrl->delete($currentUser->id);
        if($message['type'] === 'success') $autoOpenAddress = true;
    }
}

// 3. Lấy dữ liệu hiển thị
$userController = new UserController();
$data = $userController->index(); 

$user      = $data['user'];
$addresses = $data['addresses'];
$orders    = $data['orders'];
$avatarUrl = $user->avatar;

$myOrders = OrderRepository::getOrdersByUserId($currentUser->id);
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Thông tin tài khoản"]); ?>

<body class="font-display bg-background-light text-text-light">
  <?php Import::layout("UserNavigation") ?>

  <?php Import::component('Notification', ['message' => $message]); ?>

  <main class="flex-1 py-10 px-4 sm:px-6 md:px-8 min-h-screen">
    <div class="max-w-6xl mx-auto">
      
      <nav class="flex text-sm text-gray-500 mb-6">
        <a href="/app/views/pages/index.php" class="hover:text-primary transition-colors">Trang chủ</a>
        <span class="mx-2">/</span>
        <span class="text-gray-800 font-medium">Tài khoản của tôi</span>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <aside class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                <div class="flex flex-col items-center text-center mb-6">
                    <img src="<?= $avatarUrl ?>" class="w-24 h-24 rounded-full border-4 border-background-light shadow-md">
                    <h2 class="mt-4 font-bold text-lg text-gray-800"><?= htmlspecialchars($user->name) ?></h2>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($user->email) ?></p>
                </div>
                <hr class="border-gray-100 my-4">
                <nav class="space-y-1">
                    <button onclick="showTab('profile')" id="nav-profile" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-primary/10 text-primary font-medium transition-all text-left">
                        <span class="material-symbols-outlined">person</span> Thông tin cá nhân
                    </button>
                    <button onclick="showTab('address')" id="nav-address" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-primary transition-all text-left">
                        <span class="material-symbols-outlined">location_on</span> Sổ địa chỉ
                    </button>
                    <button onclick="showTab('orders')" id="nav-orders" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-primary transition-all text-left">
                        <span class="material-symbols-outlined">shopping_bag</span> Đơn hàng của tôi
                    </button>
                    <a href="/logout.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-red-500 hover:bg-red-50 transition-all mt-4">
                        <span class="material-symbols-outlined">logout</span> Đăng xuất
                    </a>
                </nav>
            </div>
        </aside>

        <div class="lg:col-span-3">
            
            <section id="tab-profile" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8 animate-fade-in-up">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined">badge</span> Thông tin chung
                    </h3>
                </div>
                <form id="profile-form" method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="grid grid-cols-1 gap-6 max-w-2xl">
                        <div class="space-y-2 relative group">
                            <label class="text-sm font-medium text-gray-700">Họ và tên</label>
                            <div class="relative">
                                <input type="text" id="input-name" name="name" value="<?= htmlspecialchars($user->name) ?>" class="w-full px-4 py-3 pr-12 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 focus:outline-none focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 transition-all" readonly>
                                <button type="button" onclick="toggleEdit('name')" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-primary transition-colors"><span id="icon-name" class="material-symbols-outlined text-xl">edit</span></button>
                            </div>
                        </div>
                        <div class="space-y-2 relative group">
                            <label class="text-sm font-medium text-gray-700">Số điện thoại</label>
                            <div class="relative">
                                <input type="text" id="input-phone" name="phone" value="<?= htmlspecialchars($user->phone ?? '') ?>" class="w-full px-4 py-3 pr-12 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 focus:outline-none focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 transition-all" readonly>
                                <button type="button" onclick="toggleEdit('phone')" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-primary transition-colors"><span id="icon-phone" class="material-symbols-outlined text-xl">edit</span></button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Email</label>
                            <input type="email" value="<?= htmlspecialchars($user->email) ?>" class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed" readonly>
                        </div>
                    </div>
                </form>
            </section>

            <section id="tab-address" class="hidden animate-fade-in-up">
                <?php 
                    Import::component('UserAddressList', [
                        'addresses' => $addresses,
                        'userName'  => $user->name
                    ]); 
                ?>
            </section>

            <section id="tab-add-address" class="hidden animate-fade-in-up">
                <?php Import::component('UserAddressAdd'); ?>
            </section>

            <section id="tab-orders" class="hidden bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8 animate-fade-in-up">
                <h3 class="text-xl font-bold text-primary flex items-center gap-2 mb-6">
                    <span class="material-symbols-outlined">receipt_long</span> Đơn hàng gần đây
                </h3>
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-gray-500 bg-gray-50 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-4">Mã đơn</th>
                    <th class="px-6 py-4">Ngày đặt</th>
                    <th class="px-6 py-4">Tổng tiền</th>
                    <th class="px-6 py-4">Trạng thái</th>
                    <th class="px-6 py-4 text-center">Chi tiết</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($myOrders)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <span class="material-symbols-outlined text-4xl mb-2 text-gray-300">shopping_bag</span>
                            <p>Bạn chưa có đơn hàng nào.</p>
                            <a href="/app/views/pages/Product.php" class="text-primary hover:underline mt-2 inline-block font-medium">Mua sắm ngay</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($myOrders as $order): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                #<?= $order->id ?>
                            </td>
                            
                            <td class="px-6 py-4 text-gray-600">
                                <?= date('d/m/Y H:i', strtotime($order->created_at)) ?>
                            </td>
                            
                            <td class="px-6 py-4 font-bold text-primary">
                                <?= number_format($order->total_amount ?? 0, 0, ',', '.') ?>₫
                            </td>
                            
                            <td class="px-6 py-4">
                                <?php 
                                    $statusColor = 'bg-gray-100 text-gray-800';
                                    $statusLabel = $order->status_order;
                                    
                                    if ($order->status_order === 'confirmed') {
                                        $statusColor = 'bg-blue-50 text-blue-700 border border-blue-100';
                                    } elseif ($order->status_order === 'completed') {
                                        $statusColor = 'bg-green-50 text-green-700 border border-green-100';
                                    } elseif ($order->status_order === 'shipping') {
                                        $statusColor = 'bg-yellow-50 text-yellow-700 border border-yellow-100';
                                    }
                                ?>
                                <span class="<?= $statusColor ?> px-3 py-1 rounded-full text-xs font-semibold capitalize">
                                    <?= htmlspecialchars($statusLabel) ?>
                                </span>
                            </td>
                            
                            <td class="px-6 py-4 text-center">
                                <a href="/app/views/pages/OrderDetail.php?order_id=<?= $order->id ?>" 
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-primary/10 text-gray-400 hover:text-primary transition-colors"
                                   title="Xem chi tiết đơn hàng">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </a>
                               
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
            </section>

        </div>
      </div>
    </div>
  </main>

  <?php Import::layout("Footer") ?>

  <script>
    // --- Toast Notification ---
    function closeToast() {
        const toast = document.getElementById('toast-notification');
        if (toast) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 500);
        }
    }
    setTimeout(() => closeToast(), 3000);

    // --- Tabs Switching ---
    function showTab(tabName) {
        const sections = ['profile', 'address', 'add-address', 'orders'];
        sections.forEach(sec => {
            const el = document.getElementById('tab-' + sec);
            if(el) el.classList.add('hidden');
            const btn = document.getElementById('nav-' + sec);
            if(btn){
                btn.classList.remove('bg-primary/10', 'text-primary', 'font-medium');
                btn.classList.add('text-gray-600', 'hover:bg-gray-50', 'hover:text-primary');
            }
        });

        const target = document.getElementById('tab-' + tabName);
        if(target) target.classList.remove('hidden');

        let navId = 'nav-' + tabName;
        if(tabName === 'add-address') navId = 'nav-address';

        const activeBtn = document.getElementById(navId);
        if(activeBtn){
            activeBtn.classList.remove('text-gray-600', 'hover:bg-gray-50', 'hover:text-primary');
            activeBtn.classList.add('bg-primary/10', 'text-primary', 'font-medium');
        }
    }

    // --- Inline Edit ---
    function toggleEdit(field) {
        const input = document.getElementById('input-' + field);
        const icon = document.getElementById('icon-' + field);
        const form = document.getElementById('profile-form');
        const isReadonly = input.hasAttribute('readonly');

        if (isReadonly) {
            document.querySelectorAll('#profile-form input:not([type=hidden])').forEach(el => {
                if(el.id !== 'input-' + field) {
                    el.setAttribute('readonly', true);
                    el.classList.add('bg-gray-50');
                    const otherField = el.id.replace('input-', '');
                    const otherIcon = document.getElementById('icon-' + otherField);
                    if(otherIcon) otherIcon.innerText = 'edit';
                }
            });
            input.removeAttribute('readonly');
            input.focus();
            input.classList.remove('bg-gray-50');
            input.classList.add('bg-white');
            icon.innerText = 'check';
            icon.parentElement.classList.add('text-green-600');
        } else {
            // Xóa showLoader() ở đây
            if(confirm("Cập nhật thông tin " + field + "?")) {
                form.submit();
            }
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        <?php if($autoOpenAddress): ?>
            showTab('address'); 
        <?php endif; ?>
    });
  </script>
</body>
</html>