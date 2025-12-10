<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["UserRepository"]);
Import::helpers(["Password"]); // Cần helper Password để hash mật khẩu

$error = null;

// Xử lý khi Submit form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";
    $role = $_POST["role"] ?? "user";

    // 1. Validate cơ bản
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Vui lòng điền đầy đủ các trường bắt buộc (*).";
    } 
    // 2. Kiểm tra mật khẩu khớp
    elseif ($password !== $confirmPassword) {
        $error = "Mật khẩu xác nhận không khớp.";
    }
    // 3. Kiểm tra độ dài mật khẩu (tùy chọn)
    elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    }
    else {
        // 4. Kiểm tra Email đã tồn tại chưa
        $existingUser = UserRepository::findByEmail($email);
        if ($existingUser) {
            $error = "Email này đã được sử dụng bởi người dùng khác.";
        } else {
            // 5. Tạo User mới
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->phone = $phone;
            $user->password = Password::hash($password); // Mã hóa mật khẩu
            $user->role = $role;

            // Lưu vào DB
            UserRepository::save($user);

            // Chuyển hướng về trang danh sách
            header("Location: /app/views/pages/admin/UserManagement.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Thêm người dùng"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
    <div class="relative flex min-h-screen w-full">
        <?php Import::layout('AdminSidebar', ["active" => "users"]); ?>

        <main class="flex-1 p-6 lg:p-10">
            <div class="max-w-5xl mx-auto">
                
                <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold text-primary">Thêm Người dùng mới</h1>
                        <p class="text-sm text-gray-500">Tạo tài khoản mới cho Quản trị viên hoặc Khách hàng.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="/app/views/pages/admin/UserManagement.php" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                            Hủy
                        </a>
                        <button form="create-user-form" class="px-4 py-2 text-sm font-medium text-white bg-primary border border-transparent rounded-lg shadow-sm hover:bg-primary/90 flex items-center gap-2 transition-colors">
                            <span class="material-symbols-outlined text-xl">save</span>
                            Lưu Người dùng
                        </button>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 flex items-center gap-3 text-red-700">
                        <span class="material-symbols-outlined text-xl">error</span>
                        <span class="text-sm font-medium"><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form id="create-user-form" method="POST" action="/app/views/pages/admin/CreateUser.php" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-2 flex flex-col gap-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                            <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-4">Thông tin tài khoản</h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <label class="flex flex-col gap-2">
                                    <span class="text-sm font-medium text-gray-700">Họ và Tên <span class="text-red-500">*</span></span>
                                    <input 
                                        name="name" 
                                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                        class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm focus:ring-primary focus:border-primary" 
                                        placeholder="Ví dụ: Nguyễn Văn An" 
                                        required 
                                    />
                                </label>

                                <label class="flex flex-col gap-2">
                                    <span class="text-sm font-medium text-gray-700">Số điện thoại</span>
                                    <input 
                                        name="phone" 
                                        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                        class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm focus:ring-primary focus:border-primary" 
                                        placeholder="Ví dụ: 0912 345 678" 
                                    />
                                </label>
                            </div>

                            <label class="flex flex-col gap-2">
                                <span class="text-sm font-medium text-gray-700">Địa chỉ Email <span class="text-red-500">*</span></span>
                                <input 
                                    type="email"
                                    name="email" 
                                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                    class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm focus:ring-primary focus:border-primary" 
                                    placeholder="email@example.com" 
                                    required 
                                />
                                <p class="text-xs text-gray-500">Email này sẽ được sử dụng để đăng nhập.</p>
                            </label>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                            <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-4">Bảo mật</h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <label class="flex flex-col gap-2">
                                    <span class="text-sm font-medium text-gray-700">Mật khẩu <span class="text-red-500">*</span></span>
                                    <input 
                                        type="password"
                                        name="password" 
                                        class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm focus:ring-primary focus:border-primary" 
                                        placeholder="••••••••" 
                                        required 
                                    />
                                </label>

                                <label class="flex flex-col gap-2">
                                    <span class="text-sm font-medium text-gray-700">Xác nhận mật khẩu <span class="text-red-500">*</span></span>
                                    <input 
                                        type="password"
                                        name="confirm_password" 
                                        class="form-input w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm focus:ring-primary focus:border-primary" 
                                        placeholder="••••••••" 
                                        required 
                                    />
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1 flex flex-col gap-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                            <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-4">Phân quyền</h2>
                            
                            <label class="flex flex-col gap-2">
                                <span class="text-sm font-medium text-gray-700">Vai trò</span>
                                <select name="role" class="form-select w-full rounded-lg border border-gray-300 bg-white h-12 px-4 text-sm focus:ring-primary focus:border-primary">
                                    <option value="user" selected>Khách hàng (User)</option>
                                    <option value="admin">Quản trị viên (Admin)</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    <span class="font-semibold">Lưu ý:</span> Quản trị viên có quyền truy cập vào trang Admin này.
                                </p>
                            </label>

                            <div class="pt-4 border-t border-gray-100">
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" name="send_mail" class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary" checked>
                                    <span class="text-sm text-gray-700">Gửi email chào mừng</span>
                                </label>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </main>
    </div>
</body>
</html>