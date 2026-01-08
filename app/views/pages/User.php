<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["UserController", "UserAddressController"]);
Import::middlewares(["Authentication"]);
Import::repositories(['OrderRepository', 'ProductValuationRepository']); // Import Repo

// 1. Check Login
$currentUser = Authentication::getAuthentication();
if (!$currentUser) {
    header("Location: /app/views/pages/auth/SignIn.php");
    exit;
}

$message = null;
$autoOpenAddress = false; 
$activeTab = 'profile';

if (isset($_GET['tab'])) {
    $activeTab = $_GET['tab'];
}

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
        if($message['type'] === 'success') {
            $autoOpenAddress = true;
            $activeTab = 'address';
        }
    }
    elseif ($action === 'delete_address') {
        $addrCtrl = new UserAddressController();
        $message = $addrCtrl->delete($currentUser->id);
        if($message['type'] === 'success') {
            $autoOpenAddress = true;
            $activeTab = 'address';
        }
    }
}

// 3. Lấy dữ liệu hiển thị
$userController = new UserController();
$data = $userController->index(); 

$user      = $data['user'];
$addresses = $data['addresses'];
$avatarUrl = $user->avatar;

$myOrders = OrderRepository::getOrdersByUserId($currentUser->id);

// [MỚI] Lấy cả 2 danh sách: Chưa đánh giá & Đã đánh giá
$unvaluedItems = [];
$valuedItems   = [];

if (class_exists('ProductValuationRepository')) {
    $unvaluedItems = ProductValuationRepository::getUnvaluedItems($currentUser->id);
    $valuedItems   = ProductValuationRepository::getValuedItems($currentUser->id);
}
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
        <a href="/app/views/pages/home.php" class="hover:text-primary transition-colors">Trang chủ</a>
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
                    <button onclick="showTab('profile')" id="nav-profile" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-primary transition-all text-left">
                        <span class="material-symbols-outlined">person</span> Thông tin cá nhân
                    </button>
                    <button onclick="showTab('address')" id="nav-address" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-primary transition-all text-left">
                        <span class="material-symbols-outlined">location_on</span> Sổ địa chỉ
                    </button>
                    
                    <button onclick="showTab('valuations')" id="nav-valuations" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-primary transition-all text-left justify-between group">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined">rate_review</span> Đánh giá sản phẩm
                        </div>
                        <?php if(count($unvaluedItems) > 0): ?>
                            <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm group-hover:bg-red-600 transition-colors"><?= count($unvaluedItems) ?></span>
                        <?php endif; ?>
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
            
            <section id="tab-profile" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8 animate-fade-in-up hidden">
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

            <section id="tab-valuations" class="hidden bg-white rounded-xl shadow-sm border border-gray-100 animate-fade-in-up overflow-hidden">
                <div class="flex items-center px-6 border-b border-gray-100 bg-white">
                    <button onclick="switchReviewTab('todo')" id="btn-tab-todo" class="py-4 px-4 mr-4 text-base font-bold border-b-2 border-orange-600 text-orange-600 transition-colors">
                        Chưa đánh giá (<?= count($unvaluedItems) ?>)
                    </button>
                    <button onclick="switchReviewTab('done')" id="btn-tab-done" class="py-4 px-4 text-base font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 transition-colors">
                        Đã đánh giá
                    </button>
                </div>

                <div id="content-todo" class="divide-y divide-gray-100 bg-gray-50 min-h-[300px]">
                    <?php if (empty($unvaluedItems)): ?>
                        <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <span class="material-symbols-outlined text-4xl text-gray-300">rate_review</span>
                            </div>
                            <p class="font-medium">Chưa có đơn hàng nào cần đánh giá.</p>
                            <a href="/app/views/pages/Product.php" class="text-primary hover:underline mt-2 text-sm">Mua sắm thêm</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($unvaluedItems as $item): ?>
                            <div class="bg-white p-5 flex gap-4 items-start hover:bg-gray-50/50 transition-colors">
                                <img src="<?= $item['product_image'] ?? 'https://via.placeholder.com/100' ?>" class="w-20 h-20 object-cover border border-gray-200 rounded-md shrink-0">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-gray-900 line-clamp-1 text-base"><?= htmlspecialchars($item['product_name']) ?></h4>
                                    <p class="text-sm text-gray-500 mt-1">Phân loại: Mặc định</p>
                                    <div class="mt-2 text-xs text-gray-400 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                                        Mua ngày: <?= date('d/m/Y', strtotime($item['order_date'])) ?>
                                    </div>
                                </div>
                                <div class="shrink-0 flex flex-col items-end gap-2">
                                    <span class="text-xs text-orange-500 font-medium bg-orange-50 px-2 py-1 rounded">Chưa đánh giá</span>
                                    <a href="/app/views/pages/CreateProductValuation.php?order_id=<?= $item['order_id'] ?>&product_id=<?= $item['product_id'] ?>" 
                                       class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded shadow-sm transition-colors flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">edit_square</span> Đánh giá
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div id="content-done" class="divide-y divide-gray-100 bg-gray-50 min-h-[300px] hidden">
                    <?php if (empty($valuedItems)): ?>
                        <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <span class="material-symbols-outlined text-4xl text-gray-300">history</span>
                            </div>
                            <p class="font-medium">Bạn chưa đánh giá sản phẩm nào.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($valuedItems as $item): ?>
                            <div class="bg-white p-5 flex gap-4 items-start">
                                <img src="<?= $item['product_image'] ?? 'https://via.placeholder.com/100' ?>" class="w-20 h-20 object-cover border border-gray-200 rounded-md shrink-0">
                                
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-gray-900 line-clamp-1 text-base"><?= htmlspecialchars($item['product_name']) ?></h4>
                                    
                                    <div class="flex items-center gap-1 mt-2 text-yellow-400 text-sm">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <span class="material-symbols-outlined !text-[18px]" style="font-variation-settings: 'FILL' <?= $i <= $item['star_rate'] ? 1 : 0 ?>">star</span>
                                        <?php endfor; ?>
                                        <span class="text-gray-400 text-xs ml-2">Logic: <?= $item['star_rate'] ?>/5</span>
                                    </div>

                                    <div class="mt-3 p-3 bg-gray-50 rounded text-sm text-gray-700 italic border border-gray-100">
                                        "<?= htmlspecialchars($item['content'] ?? 'Không có nội dung') ?>"
                                    </div>
                                    
                                    <div class="mt-2 text-xs text-gray-400">
                                        Đã đánh giá lúc: <?= date('d/m/Y H:i', strtotime($item['created_at'] ?? 'now')) ?>
                                    </div>
                                </div>
                                
                                <div class="shrink-0">
                                    <span class="text-xs text-green-600 font-medium bg-green-50 px-2 py-1 rounded border border-green-100">Đã xong</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
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
                                    <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">Chưa có đơn hàng.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($myOrders as $order): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 font-medium text-gray-900">#<?= $order->id ?></td>
                                            <td class="px-6 py-4 text-gray-600"><?= date('d/m/Y', strtotime($order->created_at)) ?></td>
                                            <td class="px-6 py-4 font-bold text-primary"><?= number_format($order->total_amount ?? 0, 0, ',', '.') ?>₫</td>
                                            <td class="px-6 py-4"><?php Import::component('OrderStatusBadge', ['status' => $order->status_order, 'size' => 'sm', 'showIcon' => false]); ?></td>
                                            <td class="px-6 py-4 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <a href="/app/views/pages/OrderDetail.php?order_id=<?= $order->id ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-primary/10 text-gray-400 hover:text-primary transition-colors"><span class="material-symbols-outlined text-[20px]">visibility</span></a>
                                                    <?php if ($order->status_order === 'unconfirmed'): ?>
                                                        <form method="POST" action="/app/controllers/OrderController.php?action=cancel" onsubmit="return confirm('Hủy đơn?');" class="inline-block">
                                                            <input type="hidden" name="order_id" value="<?= $order->id ?>">
                                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors"><span class="material-symbols-outlined text-[20px]">delete</span></button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
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
    // --- Tabs Switching Logic (JS) ---
    function showTab(tabName) {
        const sections = ['profile', 'address', 'add-address', 'orders', 'valuations'];
        sections.forEach(sec => {
            document.getElementById('tab-' + sec).classList.add('hidden');
            const btn = document.getElementById('nav-' + sec);
            if(btn){
                btn.classList.remove('bg-primary/10', 'text-primary', 'font-medium');
                btn.classList.add('text-gray-600', 'hover:bg-gray-50', 'hover:text-primary');
            }
        });

        document.getElementById('tab-' + tabName).classList.remove('hidden');
        let navId = (tabName === 'add-address') ? 'nav-address' : 'nav-' + tabName;
        const activeBtn = document.getElementById(navId);
        if(activeBtn){
            activeBtn.classList.remove('text-gray-600', 'hover:bg-gray-50', 'hover:text-primary');
            activeBtn.classList.add('bg-primary/10', 'text-primary', 'font-medium');
        }
        
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({}, '', url);
    }

    // --- Switch between "To Do" and "Done" reviews ---
    function switchReviewTab(subTab) {
        const btnTodo = document.getElementById('btn-tab-todo');
        const btnDone = document.getElementById('btn-tab-done');
        const contentTodo = document.getElementById('content-todo');
        const contentDone = document.getElementById('content-done');

        if (subTab === 'todo') {
            contentTodo.classList.remove('hidden');
            contentDone.classList.add('hidden');
            
            btnTodo.classList.add('border-orange-600', 'text-orange-600');
            btnTodo.classList.remove('border-transparent', 'text-gray-500');
            
            btnDone.classList.remove('border-orange-600', 'text-orange-600');
            btnDone.classList.add('border-transparent', 'text-gray-500');
        } else {
            contentTodo.classList.add('hidden');
            contentDone.classList.remove('hidden');

            btnDone.classList.add('border-orange-600', 'text-orange-600');
            btnDone.classList.remove('border-transparent', 'text-gray-500');

            btnTodo.classList.remove('border-orange-600', 'text-orange-600');
            btnTodo.classList.add('border-transparent', 'text-gray-500');
        }
    }

    // --- Inline Edit Logic ---
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
            if(confirm("Cập nhật thông tin " + field + "?")) { form.submit(); }
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        <?php if($autoOpenAddress): ?>
            showTab('address'); 
        <?php elseif(isset($activeTab)): ?>
            showTab('<?= $activeTab ?>');
        <?php endif; ?>
    });
  </script>
  <?php Import::component('SocialWidget'); ?>
</body>
</html>