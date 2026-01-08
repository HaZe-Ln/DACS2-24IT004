<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["UserRepository"]);
Import::helpers(["Password"]);

$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';
$error = null;
$success = null;

// Kiểm tra token ngay khi vào trang
$user = UserRepository::findByResetToken($email, $token);

if (!$user) {
    $error = "Liên kết không hợp lệ hoặc đã hết hạn.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (strlen($pass) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    } elseif ($pass !== $confirm) {
        $error = "Mật khẩu xác nhận không khớp.";
    } else {
        // Cập nhật mật khẩu mới
        $user->password = Password::hash($pass);
        UserRepository::update($user);
        
        // Xóa token để không dùng lại được
        UserRepository::clearResetToken($user->id);
        
        $success = "Đổi mật khẩu thành công! Bạn sẽ được chuyển hướng về trang đăng nhập...";
        echo "<script>setTimeout(() => { window.location.href = '/app/views/pages/auth/SignIn.php'; }, 3000);</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Đặt lại mật khẩu"]); ?>

<body class="font-display bg-background-light">
  <?php Import::layout("UserNavigation") ?>

  <main class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md rounded-xl bg-white p-8 shadow-lg border border-gray-200">
      <div class="mb-6 text-center">
        <h1 class="text-3xl font-black text-primary">Mật Khẩu Mới</h1>
      </div>

      <?php if($success): ?>
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
              <?= $success ?>
          </div>
      <?php elseif($error): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
              <?= $error ?>
          </div>
          <?php if(!$user): ?>
             <div class="text-center mt-4">
                 <a href="/app/views/pages/auth/ForgotPassword.php" class="text-primary hover:underline">Gửi lại yêu cầu</a>
             </div>
          <?php endif; ?>
      <?php endif; ?>

      <?php if($user && !$success): ?>
      <form class="flex flex-col gap-6" method="post">
        <div class="flex flex-col gap-2">
          <label class="text-sm font-medium">Mật khẩu mới</label>
          <input class="form-input w-full rounded-lg border border-gray-300 py-3 px-4"
              name="password" type="password" placeholder="••••••••" required />
        </div>
        
        <div class="flex flex-col gap-2">
          <label class="text-sm font-medium">Xác nhận mật khẩu</label>
          <input class="form-input w-full rounded-lg border border-gray-300 py-3 px-4"
              name="confirm_password" type="password" placeholder="••••••••" required />
        </div>

        <button type="submit" class="flex w-full items-center justify-center rounded-lg h-12 bg-primary text-white font-bold hover:bg-primary/90">
          Xác nhận
        </button>
      </form>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>