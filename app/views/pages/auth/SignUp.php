<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';

Import::helpers(["Request"]);
Import::controllers(["AuthController"]);
Import::middlewares(["Authentication"]);

$signUpData = null;

if (Request::method() === "POST") {
    $authController = new AuthController();
    $signUpData = $authController->signUp();

    if ($signUpData['status'] === true && $signUpData['data'] != null) {
        Authentication::setAuthentication($signUpData['data']);
        header("Location: /app/views/pages/Home.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<?php
Import::layout('Head', [
    "title" => "Đăng ký"
]);
?>

<body class="font-display">
    <div class="relative flex h-auto min-h-screen w-full flex-col bg-background-light dark:bg-background-dark group/design-root overflow-x-hidden">
        <div class="layout-container flex h-full grow flex-col">
            <div class="flex flex-1 justify-center">
                <div class="layout-content-container flex w-full flex-col flex-1">
                    <div class="grid lg:grid-cols-2 min-h-screen">
                        <!-- Cột trái: Ảnh -->
                        <div class="relative hidden lg:flex items-center justify-center p-8 bg-black">
                            <div class="absolute inset-0 w-full h-full bg-center bg-no-repeat bg-cover opacity-50"
                                data-alt="Guitar Background"
                                style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBFdnT7pVrVmAVZbWmHPWq5EkuAhvx3Ow6J8xxoN_G3XsImBLTgOhtOfkquOkxT1ArL45dB8ooKdBeScKRz_pZUGmjgjYjnw7GoHV2o7vPkVtJa8VZ6rypK4mmpRBsIkAfBll0olQDzZSdOcr8kKW77CHZNmGu8ARwRG0O-HO1RkKo-HTsVE_4QkWJTX3NReltmnQKXqc7jTBIDoaAuI9rPRs2lUsuwCOsdcD1yrDwkaK0w30WHsctT4E9Ael14uW99jeLOh9tOIUHK");'>
                            </div>
                            <div class="relative z-10 text-center text-white p-8">
                                <h1 class="text-4xl font-black mb-4 tracking-[-0.033em]">Hòa nhịp đam mê cùng chúng tôi</h1>
                                <p class="text-lg text-white/80">Tham gia cộng đồng yêu guitar, khám phá những cây đàn độc đáo và nhận những ưu đãi dành riêng cho thành viên.</p>
                            </div>
                        </div>

                        <!-- Cột phải: Form -->
                        <div class="flex flex-col justify-center items-center w-full bg-white dark:bg-background-dark p-6 sm:p-8">
                            <div class="w-full max-w-md">
                                <div class="flex flex-wrap justify-start gap-3 p-4">
                                    <div class="flex min-w-72 flex-col gap-2">
                                        <p class="text-[#212529] dark:text-white text-4xl font-black leading-tight tracking-[-0.033em]">Tạo tài khoản</p>
                                        <p class="text-[#495057] dark:text-gray-400 text-base font-normal leading-normal">Lưu sản phẩm yêu thích, theo dõi đơn hàng và nhận ưu đãi độc quyền.</p>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-4 px-4 py-3">
                                    <!-- Hiển thị lỗi -->
                                    <?php if ($signUpData != null && $signUpData['status'] == false): ?>
                                    <div role="alert" class="w-full bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm mb-2 rounded-md">
                                        <?php echo htmlspecialchars($signUpData['message'] ?? "Đăng ký thất bại, vui lòng thử lại."); ?>
                                    </div>
                                    <?php endif; ?>

                                    <form class="flex flex-col gap-4" method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                                        
                                        <!-- Họ và tên -->
                                        <div class="flex flex-col">
                                            <label class="flex flex-col min-w-40 flex-1">
                                                <p class="text-[#111418] dark:text-gray-200 text-base font-medium leading-normal pb-2">Họ và Tên</p>
                                                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] dark:text-white focus:outline-0 focus:ring-0 border border-[#dbe0e6] dark:border-gray-600 bg-white dark:bg-gray-700 h-14 placeholder:text-[#617589] p-[15px] text-base font-normal leading-normal"
                                                    placeholder="Nhập họ và tên của bạn" type="text" name="name" 
                                                    value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" />
                                            </label>
                                        </div>

                                        <!-- MỚI: Số điện thoại -->
                                        <div class="flex flex-col">
                                            <label class="flex flex-col min-w-40 flex-1">
                                                <p class="text-[#111418] dark:text-gray-200 text-base font-medium leading-normal pb-2">Số điện thoại</p>
                                                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] dark:text-white focus:outline-0 focus:ring-0 border border-[#dbe0e6] dark:border-gray-600 bg-white dark:bg-gray-700 h-14 placeholder:text-[#617589] p-[15px] text-base font-normal leading-normal"
                                                    placeholder="Nhập số điện thoại (10 số)" type="tel" name="phone" required
                                                    pattern="[0-9]{10,12}"
                                                    value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" />
                                            </label>
                                        </div>

                                        <!-- Email -->
                                        <div class="flex flex-col">
                                            <label class="flex flex-col min-w-40 flex-1">
                                                <p class="text-[#111418] dark:text-gray-200 text-base font-medium leading-normal pb-2">Email</p>
                                                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] dark:text-white focus:outline-0 focus:ring-0 border border-[#dbe0e6] dark:border-gray-600 bg-white dark:bg-gray-700 h-14 placeholder:text-[#617589] p-[15px] text-base font-normal leading-normal"
                                                    placeholder="Nhập email của bạn" type="email" name="email" 
                                                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" />
                                            </label>
                                        </div>

                                        <!-- Mật khẩu -->
                                        <div class="flex flex-col relative">
                                            <label class="flex flex-col min-w-40 flex-1">
                                                <p class="text-[#111418] dark:text-gray-200 text-base font-medium leading-normal pb-2">Mật khẩu</p>
                                                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] dark:text-white focus:outline-0 focus:ring-0 border border-[#dbe0e6] dark:border-gray-600 bg-white dark:bg-gray-700 h-14 placeholder:text-[#617589] p-[15px] pr-12 text-base font-normal leading-normal"
                                                    placeholder="Nhập mật khẩu" type="password" name="password" />
                                            </label>
                                            <button class="absolute right-4 top-[46px] text-gray-500 hover:text-primary transition-colors" type="button" 
                                                onclick="const i=this.previousElementSibling.querySelector('input'); i.type = i.type==='password'?'text':'password'; this.querySelector('span').textContent = i.type==='password'?'visibility_off':'visibility';">
                                                <span class="material-symbols-outlined">visibility_off</span>
                                            </button>
                                        </div>
                                        <div class="mt-1">
                                            <p class="text-xs text-[#495057] dark:text-gray-400">Yêu cầu: Tối thiểu 8 ký tự, có chữ hoa, chữ thường và số.</p>
                                        </div>

                                        <!-- Xác nhận mật khẩu -->
                                        <div class="flex flex-col relative">
                                            <label class="flex flex-col min-w-40 flex-1">
                                                <p class="text-[#111418] dark:text-gray-200 text-base font-medium leading-normal pb-2">Xác nhận mật khẩu</p>
                                                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] dark:text-white focus:outline-0 focus:ring-0 border border-[#dbe0e6] dark:border-gray-600 bg-white dark:bg-gray-700 h-14 placeholder:text-[#617589] p-[15px] text-base font-normal leading-normal"
                                                    placeholder="Nhập lại mật khẩu" type="password" name="confirmPassword" />
                                            </label>
                                        </div>

                                        <div class="pt-4">
                                            <button class="flex min-w-[84px] w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg h-14 px-5 bg-primary text-white text-lg font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90 transition-opacity">
                                                <span class="truncate">Đăng Ký</span>
                                            </button>
                                        </div>

                                        <div class="text-center text-sm text-[#495057] dark:text-gray-400 pt-2">
                                            <p>Bằng việc đăng ký, bạn đồng ý với <a class="font-medium text-primary hover:underline" href="#">Điều khoản Dịch vụ</a> và <a class="font-medium text-primary hover:underline" href="#">Chính sách Bảo mật</a>.</p>
                                        </div>
                                        <div class="text-center text-sm text-[#495057] dark:text-gray-400 pt-4">
                                            <p>Đã là thành viên? <a class="font-bold text-primary hover:underline" href="/app/views/pages/auth/SignIn.php">Đăng nhập ngay</a></p>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</body>
</html>