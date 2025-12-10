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

        // Lưu thông tin đăng nhập (set cookie token)
        Authentication::setAuthentication($user);

        // Lấy role (dạng object hoặc array đều xử lý)
        $role = null;
        if (is_array($user) && isset($user['role'])) {
            $role = $user['role'];
        } elseif (is_object($user) && isset($user->role)) {
            $role = $user->role;
        }

        // Nếu là admin → sang Dashboard admin
        if ($role === 'admin') {
            header("Location: /app/views/pages/admin/Dashboard.php"); // sửa đúng path Dashboard của bạn nếu khác
            exit;
        }

        // Còn lại → về Home
        header("Location: /app/views/pages/Home.php");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<?php
// GIỮ NGUYÊN HEAD CỦA PROJECT (CSS, JS, EFFECT NAVBAR...)
Import::layout('Head', [
    "title" => "Đăng nhập"
]);
?>

<body class="text-gray-800 min-h-screen bg-gray-50">

    <!-- Lỗi thiếu trường (email/password trống) -->
    <?php if ($signInData != null && $signInData['status'] == false && !empty($signInData['keysError'])): ?>
    <div role="alert" class="w-full bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm rounded-md">
        Trường
        <strong><?php echo htmlspecialchars(implode(", ", $signInData['keysError'])); ?></strong>
        bị thiếu.
    </div>
    <?php endif; ?>

    <!-- Lỗi đăng nhập sai (email không tồn tại, mật khẩu sai, v.v.) -->
    <?php if ($signInData != null && $signInData['status'] == false && !empty($signInData['message'])): ?>
    <div role="alert" class="w-full bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm mt-2 rounded-md">
        <?php echo htmlspecialchars($signInData['message']); ?>
    </div>
    <?php endif; ?>

    <!-- NAVBAR GIỮ NGUYÊN, KHÔNG ĐỤNG VÀO -->

    <!-- GIAO DIỆN LOGIN MỚI (DÙNG TAILWIND MẶC ĐỊNH, KHÔNG TÁC ĐỘNG NAVBAR) -->
    <div class="relative flex w-full min-h-[80vh] flex-col overflow-x-hidden">

        <div class="flex flex-1 items-stretch">
            <div class="flex min-h-[80vh] w-full flex-col lg:flex-row">

                <!-- Cột trái: Ảnh -->
                <div class="relative hidden w-full flex-1 items-center justify-center lg:flex">
                    <img class="absolute inset-0 h-full w-full object-cover"
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuBqZw3ISuPALGtkWL08bmKA9kf8-lbnHo_bpBCBI_XHqKi8iZzYOV42cq4cWKhbHHM80E6p1__nYuWyJTH7NfoBZAPmBhFa_wIEIn5lK86z41u_rAOfvlGa9H2JPWwC38uZz7jIKV3OkglBqfwbO5-m9bcjJ3Qk32SGE-xDZ96Wuxh7DXN_M7jvN_TxlN4HT4zcmpQF4SrEJr_6O6np76LtBfF6QR_QRhSHAy2JKhUV0VlaoaQzoTHQDAi-EnqBQkycAG_RS0lqGVUy"
                        alt="Người đang chơi guitar trong phòng ánh sáng ấm" />
                    <div class="absolute inset-0 bg-black/30"></div>
                </div>

                <!-- Cột phải: Form -->
                <div class="flex w-full flex-1 items-center justify-center bg-gray-50 p-6 sm:p-8 lg:max-w-xl">
                    <div class="flex w-full max-w-md flex-col items-center justify-center py-10">

                        <!-- Logo -->
                        <div class="mb-8 text-center">
                            <a href="../Home.php" class="inline-flex flex-col items-center gap-3">
                                <!-- Huy hiệu tròn với icon nốt nhạc -->
                                <div
                                    class="flex h-16 w-16 items-center justify-center rounded-full bg-white border border-amber-500 shadow-md">
                                    <svg class="h-9 w-9 text-amber-600" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M9 19C7.34315 19 6 20.1193 6 21.5C6 22.8807 7.34315 24 9 24C10.6569 24 12 22.8807 12 21.5V9.5L19 8V5L9 7V19Z"
                                            fill="currentColor" />
                                    </svg>
                                </div>

                                <!-- Chữ logo -->
                                <div class="text-center">
                                    <p class="text-2xl font-extrabold text-amber-700 tracking-tight">
                                        XjnGuitar
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Âm nhạc trong tầm tay bạn
                                    </p>
                                </div>
                            </a>
                        </div>


                        <!-- Tiêu đề -->
                        <div class="w-full text-center">
                            <h1 class="tracking-tight text-3xl font-bold leading-tight pb-2 text-gray-900">
                                Chào mừng trở lại
                            </h1>
                            <p class="text-base font-normal leading-normal pb-8 text-gray-500">
                                Đăng nhập để tiếp tục trải nghiệm mua sắm.
                            </p>
                        </div>

                        <!-- FORM ĐĂNG NHẬP (logic y như cũ) -->
                        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post"
                            class="flex w-full flex-col gap-4">
                            <!-- Email -->
                            <label class="flex flex-col w-full">
                                <p class="text-sm font-medium leading-normal pb-2 text-gray-800">
                                    Email
                                </p>
                                <div class="relative flex w-full items-center">
                                    <div class="absolute left-0 flex h-full w-12 items-center justify-center">
                                        <span class="material-symbols-outlined text-gray-400">person</span>
                                    </div>
                                    <input
                                        class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500/70 border border-gray-300 bg-white h-12 placeholder:text-gray-400 pl-12 pr-4 text-base font-normal leading-normal"
                                        placeholder="Nhập email của bạn" type="email" name="email" required
                                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" />
                                </div>
                            </label>

                            <!-- Mật khẩu -->
                            <label class="flex flex-col w-full">
                                <div class="flex items-center justify-between pb-2">
                                    <p class="text-sm font-medium leading-normal text-gray-800">Mật khẩu</p>
                                </div>
                                <div class="relative flex w-full items-center">
                                    <div class="absolute left-0 flex h-full w-12 items-center justify-center">
                                        <span class="material-symbols-outlined text-gray-400">lock</span>
                                    </div>
                                    <input
                                        class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500/70 border border-gray-300 bg-white h-12 placeholder:text-gray-400 pl-12 pr-12 text-base font-normal leading-normal"
                                        placeholder="Nhập mật khẩu của bạn" type="password" name="password" required
                                        minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                        title="Must be more than 8 characters, including number, lowercase letter, uppercase letter" />
                                    <button
                                        class="absolute right-0 flex h-full w-12 items-center justify-center text-gray-400"
                                        type="button"
                                        onclick="const input = this.parentElement.querySelector('input[name=password]'); input.type = input.type === 'password' ? 'text' : 'password';">
                                        <span class="material-symbols-outlined">visibility</span>
                                    </button>
                                </div>
                            </label>

                            <!-- Quên mật khẩu -->
                            <div class="w-full text-right mt-1">
                                <a class="text-sm font-medium leading-normal text-amber-700 underline hover:text-amber-600"
                                    href="#">
                                    Quên mật khẩu?
                                </a>
                            </div>

                            <!-- Nút đăng nhập -->
                            <button
                                class="flex items-center justify-center whitespace-nowrap transition-colors duration-200 disabled:pointer-events-none disabled:opacity-50 text-white hover:bg-amber-700 focus:ring-2 focus:ring-amber-500/70 h-12 px-6 rounded-lg w-full bg-amber-600 text-base font-semibold mt-4"
                                type="submit">
                                Đăng nhập
                            </button>
                        </form>

                        <!-- Nút social (chỉ UI, không ảnh hưởng logic) -->
                        <div class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2">

                        </div>


                        <!-- Link đăng ký -->
                        <p class="text-sm font-normal leading-normal text-center pt-8 text-gray-500">
                            Chưa có tài khoản?
                            <a class="font-medium text-amber-700 underline hover:text-amber-600"
                                href="/app/views/pages/auth/SignUp.php">
                                Đăng ký ngay
                            </a>
                        </p>

                        <!-- Footer nhỏ -->
                        <div class="mt-12 text-center text-xs text-gray-400">
                            <a class="underline hover:text-amber-700" href="#">Điều khoản dịch vụ</a>
                            <span class="mx-2">·</span>
                            <a class="underline hover:text-amber-700" href="#">Chính sách bảo mật</a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>