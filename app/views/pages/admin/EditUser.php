<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["AdminUserController"]);

$controller = new AdminUserController();

// 1. Kiểm tra POST để cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->update(); // Gọi hàm update trong controller
}

// 2. Lấy thông tin user (GET)
$user = $controller->edit(); 
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Sửa người dùng"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
    <div class="relative flex min-h-screen w-full">
        <?php Import::layout('AdminSidebar', ["active" => "users"]); ?>

        <main class="flex-1 p-6 lg:p-10">
            <div class="max-w-5xl mx-auto">
                <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold text-primary">Sửa thông tin người dùng</h1>
                        <p class="text-sm text-gray-500">Cập nhật thông tin tài khoản #<?= $user->id ?></p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="/app/views/pages/admin/UserManagement.php" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                            Hủy
                        </a>
                        <button form="edit-user-form" class="px-4 py-2 text-sm font-medium text-white bg-primary border border-transparent rounded-lg shadow-sm hover:bg-primary/90 flex items-center gap-2 transition-colors">
                            <span class="material-symbols-outlined text-xl">save</span>
                            Lưu thay đổi
                        </button>
                    </div>
                </div>

                <form id="edit-user-form" method="POST" action="" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <input type="hidden" name="id" value="<?= $user->id ?>">

                    <div class="lg:col-span-2 flex flex-col gap-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                            <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-4">Thông tin tài khoản</h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <label class="flex flex-col gap-2">
                                    <span class="text-sm font-medium text-gray-700">Họ và Tên</span>
                                    <input name="name" value="<?= htmlspecialchars($user->name) ?>" class="form-input w-full rounded-lg border border-gray-300 h-12 px-4 text-sm" required />
                                </label>

                                <label class="flex flex-col gap-2">
                                    <span class="text-sm font-medium text-gray-700">Số điện thoại</span>
                                    <input name="phone" value="<?= htmlspecialchars($user->phone ?? '') ?>" class="form-input w-full rounded-lg border border-gray-300 h-12 px-4 text-sm" />
                                </label>
                            </div>

                            <label class="flex flex-col gap-2">
                                <span class="text-sm font-medium text-gray-700">Email (Không thể sửa)</span>
                                <input name="email" value="<?= htmlspecialchars($user->email) ?>" class="form-input w-full rounded-lg border border-gray-300 bg-gray-100 h-12 px-4 text-sm text-gray-500 cursor-not-allowed" readonly />
                            </label>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                            <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-4">Đổi mật khẩu</h2>
                            <p class="text-xs text-gray-500">Chỉ điền vào đây nếu bạn muốn đổi mật khẩu mới cho người dùng này. Nếu không, hãy để trống.</p>
                            
                            <label class="flex flex-col gap-2">
                                <span class="text-sm font-medium text-gray-700">Mật khẩu mới</span>
                                <input type="password" name="password" class="form-input w-full rounded-lg border border-gray-300 h-12 px-4 text-sm" placeholder="••••••••" />
                            </label>
                        </div>
                    </div>

                    <div class="lg:col-span-1 flex flex-col gap-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                            <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-4">Phân quyền</h2>
                            
                            <label class="flex flex-col gap-2">
                                <span class="text-sm font-medium text-gray-700">Vai trò</span>
                                <select name="role" class="form-select w-full rounded-lg border border-gray-300 h-12 px-4 text-sm">
                                    <option value="user" <?= $user->role === 'user' ? 'selected' : '' ?>>Khách hàng (User)</option>
                                    <option value="admin" <?= $user->role === 'admin' ? 'selected' : '' ?>>Quản trị viên (Admin)</option>
                                </select>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>