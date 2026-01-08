<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["UserRepository"]);
Import::helpers(["MailService"]);

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    $user = UserRepository::findByEmail($email);
    
    if ($user) {
        // 1. Tạo token ngẫu nhiên
        $token = bin2hex(random_bytes(32));
        
        // 2. Lưu vào DB
        UserRepository::saveResetToken($email, $token);
        
        // 3. Tạo link reset (Chạy trên localhost thì sửa domain/port cho đúng)
        // Ví dụ: http://localhost:3000/app/views/pages/auth/ResetPassword.php...
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $link = "$protocol://$host/app/views/pages/auth/ResetPassword.php?email=$email&token=$token";
        
        // 4. Gửi mail
        if (MailService::sendResetPassword($email, $user->name, $link)) {
            $message = "Chúng tôi đã gửi liên kết đặt lại mật khẩu vào email của bạn. Vui lòng kiểm tra hộp thư.";
        } else {
            $error = "Không thể gửi email. Vui lòng thử lại sau.";
        }
    } else {
        // Để bảo mật, dù email không tồn tại cũng nên báo thành công giả hoặc báo chung chung
        $error = "Email này không tồn tại trong hệ thống.";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Đặt lại mật khẩu"]); ?>

<body class="font-display bg-background-light text-text-light">
  <?php Import::layout("UserNavigation") ?>

  <main class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md rounded-xl bg-white p-8 shadow-lg border border-gray-200">
      <div class="mb-6 text-center space-y-2">
        <h1 class="text-3xl font-black text-primary">Quên Mật Khẩu?</h1>
        <p class="text-sm text-gray-600">
          Nhập email của bạn để nhận liên kết đặt lại mật khẩu.
        </p>
      </div>

      <?php if($message): ?>
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
              <?= $message ?>
          </div>
      <?php elseif($error): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
              <?= $error ?>
          </div>
      <?php endif; ?>

      <form class="flex flex-col gap-6" method="post">
        <div class="flex flex-col gap-2">
          <label class="text-sm font-medium text-text-light" for="email">Địa chỉ Email</label>
          <div class="relative">
            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">mail</span>
            <input
              class="form-input w-full rounded-lg border border-gray-300 py-3 pl-10 pr-4 focus:border-primary focus:ring-2 focus:ring-primary/50"
              id="email" name="email" type="email" placeholder="Nhập email của bạn" required
            />
          </div>
        </div>

        <button type="submit" class="flex w-full items-center justify-center rounded-lg h-12 px-5 bg-primary text-white text-base font-bold transition-colors hover:bg-primary/90">
          Gửi liên kết
        </button>
      </form>

      <div class="mt-6 text-center text-sm">
        <a href="/app/views/pages/auth/SignIn.php" class="font-medium text-primary hover:underline">Quay lại đăng nhập</a>
      </div>
    </div>
  </main>
</body>
</html>