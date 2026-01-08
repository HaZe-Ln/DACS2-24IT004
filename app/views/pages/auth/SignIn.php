<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';

Import::helpers(["Request", "Cookie"]);
Import::controllers(["AuthController"]);
Import::middlewares(["Authentication"]);

$signInData = null;

if (Request::method() == "POST") {
    $authController = new AuthController();
    $signInData = $authController->signIn();

    if ($signInData['status'] == true && $signInData['data'] != null) {
        $user = $signInData['data'];

        // Lưu thông tin đăng nhập
        Authentication::setAuthentication($user);

        // Lấy role
        $role = null;
        if (is_array($user) && isset($user['role'])) {
            $role = $user['role'];
        } elseif (is_object($user) && isset($user->role)) {
            $role = $user->role;
        }

        // Chuyển hướng
        if ($role === 'admin') {
            header("Location: /app/views/pages/admin/Dashboard.php");
            exit;
        }
        header("Location: /app/views/pages/Home.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<?php
Import::layout('Head', [
    "title" => "Đăng nhập"
]);
?>

<body class="text-gray-800 bg-white">

    <?php if ($signInData != null && $signInData['status'] == false): ?>
        <div class="fixed top-4 right-4 z-50 animate-bounce">
            <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-lg shadow-lg">
                <p class="font-bold">Đăng nhập thất bại!</p>
                <p class="text-sm">
                    <?php 
                    if (!empty($signInData['keysError'])) {
                        echo "Thiếu trường: " . htmlspecialchars(implode(", ", $signInData['keysError']));
                    } elseif (!empty($signInData['message'])) {
                        echo htmlspecialchars($signInData['message']);
                    }
                    ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <div class="flex min-h-screen w-full">
        
        <div class="relative hidden w-0 flex-1 lg:block">
            <img class="absolute inset-0 h-full w-full object-cover" 
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuBqZw3ISuPALGtkWL08bmKA9kf8-lbnHo_bpBCBI_XHqKi8iZzYOV42cq4cWKhbHHM80E6p1__nYuWyJTH7NfoBZAPmBhFa_wIEIn5lK86z41u_rAOfvlGa9H2JPWwC38uZz7jIKV3OkglBqfwbO5-m9bcjJ3Qk32SGE-xDZ96Wuxh7DXN_M7jvN_TxlN4HT4zcmpQF4SrEJr_6O6np76LtBfF6QR_QRhSHAy2JKhUV0VlaoaQzoTHQDAi-EnqBQkycAG_RS0lqGVUy" 
                 alt="HTAMusic Background">
            <div class="absolute inset-0 bg-black/20"></div>
        </div>

        <div class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24 bg-gray-50">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                
                <div class="text-center mb-10">
                    <a href="../Home.php" class="inline-flex flex-col items-center gap-2 group">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white border-2 border-amber-500 shadow-md transition-transform group-hover:scale-110">
                            <svg class="h-8 w-8 text-amber-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 19C7.34315 19 6 20.1193 6 21.5C6 22.8807 7.34315 24 9 24C10.6569 24 12 22.8807 12 21.5V9.5L19 8V5L9 7V19Z" fill="currentColor" />
                            </svg>
                        </div>
                        <h2 class="mt-4 text-2xl font-extrabold text-gray-900 tracking-tight uppercase">HTAMusic</h2>
                        <p class="text-sm text-gray-500">Âm nhạc trong tầm tay bạn</p>
                    </a>
                </div>

                <div class="mb-8 text-center">
                    <h2 class="text-3xl font-bold text-gray-900">Chào mừng trở lại</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Đăng nhập để tiếp tục trải nghiệm mua sắm.
                    </p>
                </div>

                <div class="mt-6">
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" class="space-y-6">
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-gray-400 text-xl">mail</span>
                                </div>
                                <input id="email" name="email" type="email" autocomplete="email" required 
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                       class="block w-full pl-10 h-12 rounded-lg border-gray-300 focus:ring-amber-500 focus:border-amber-500 sm:text-sm shadow-sm" 
                                       placeholder="vidu@gmail.com">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-gray-400 text-xl">lock</span>
                                </div>
                                <input id="password" name="password" type="password" autocomplete="current-password" required 
                                       class="block w-full pl-10 pr-10 h-12 rounded-lg border-gray-300 focus:ring-amber-500 focus:border-amber-500 sm:text-sm shadow-sm" 
                                       placeholder="••••••••">
                                <button type="button" 
                                        onclick="const input = document.getElementById('password'); input.type = input.type === 'password' ? 'text' : 'password';"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-amber-600 cursor-pointer">
                                    <span class="material-symbols-outlined text-xl">visibility</span>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 rounded">
                                <label for="remember-me" class="ml-2 block text-sm text-gray-900">Ghi nhớ đăng nhập</label>
                            </div>

                            <div class="text-sm">
                                <a href="/app/views/pages/auth/ForgotPassword.php" class="font-medium text-amber-600 hover:text-amber-500">Quên mật khẩu?</a>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                                ĐĂNG NHẬP
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-8 text-center">
                    <p class="text-sm text-gray-600">
                        Chưa có tài khoản? 
                        <a href="/app/views/pages/auth/SignUp.php" class="font-medium text-amber-600 hover:text-amber-500">
                            Đăng ký ngay
                        </a>
                    </p>
                </div>

            </div>
        </div>
    </div>

</body>
</html>