<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Đặt lại mật khẩu"]); ?>

<body class="font-display bg-background-light text-text-light">
  <?php Import::layout("UserNavigation") ?>

  <main class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md rounded-xl bg-white dark:bg-gray-900 p-8 shadow-lg border border-gray-200 dark:border-gray-800">
      <div class="mb-6 text-center space-y-2">
        <h1 class="text-3xl font-black text-primary">Đặt Lại Mật Khẩu</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Nhập email liên kết với tài khoản, chúng tôi sẽ gửi liên kết đặt lại mật khẩu cho bạn.
        </p>
      </div>

      <form class="flex flex-col gap-6" method="post" action="#">
        <div class="flex flex-col gap-2">
          <label class="text-sm font-medium text-text-light" for="email">Địa chỉ Email</label>
          <div class="relative">
            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">mail</span>
            <input
              class="form-input w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 py-3 pl-10 pr-4 text-text-light placeholder:text-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/50"
              id="email"
              name="email"
              type="email"
              placeholder="Nhập email của bạn"
              required
            />
          </div>
        </div>

        <button class="flex w-full items-center justify-center rounded-lg h-12 px-5 bg-accent text-white text-base font-bold tracking-wide transition-colors hover:bg-accent/90">
          Gửi liên kết đặt lại
        </button>
      </form>

      <div class="mt-6 text-center">
        <a class="text-sm font-medium text-primary hover:underline" href="/app/views/pages/auth/SignIn.php">
          Nhớ mật khẩu rồi? Quay lại Đăng nhập
        </a>
      </div>
    </div>
  </main>

  <?php Import::layout("Footer") ?>
</body>
</html>
