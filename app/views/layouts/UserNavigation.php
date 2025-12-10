<?php
Import::helpers(["Cookie"]);
Import::middlewares(["Authentication"]);
// Import Repository để lấy số lượng giỏ hàng
Import::repositories(["CartRepository"]); 

$isAuthenticated = Authentication::isAuthenticated();
$user = $isAuthenticated ? Authentication::getAuthentication() : null;
$pathCurrent = $_SERVER["PHP_SELF"];

// Tính số lượng giỏ hàng
$cartCount = 0;
if ($isAuthenticated && $user) {
    $cartCount = CartRepository::getTotalQuantity($user->id);
}

// Dữ liệu Menu
$navItems = [
  ['href' => '/app/views/pages/Home.php', 'label' => 'Trang chủ'],
  ['href' => '/app/views/pages/Product.php', 'label' => 'Sản phẩm'],
  ['href' => '/app/views/pages/Posts.php', 'label' => 'Bài viết'],
  ['href' => '/app/views/pages/Contact.php', 'label' => 'Liên hệ'],
  ['href' => '/app/views/pages/AboutUs.php', 'label' => 'Giới thiệu'],
];
?>

<header class="sticky top-0 z-50 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-md shadow-sm border-b border-gray-200/50">
  <div class="container mx-auto px-4 sm:px-6 md:px-10">
    <div class="flex items-center justify-between h-16">
      
      <div class="flex items-center gap-4">
        <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-md text-text-light dark:text-text-dark hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none">
          <span class="material-symbols-outlined text-2xl">menu</span>
        </button>

        <a href="/app/views/pages/Home.php" class="flex items-center gap-2 text-primary dark:text-white group nav-transition">
          <span class="material-symbols-outlined text-3xl group-hover:text-accent transition-colors">music_note</span>
          <h2 class="text-xl font-bold leading-tight tracking-[-0.015em] hidden sm:block">HTAMusic</h2>
        </a>
      </div>

      <nav class="hidden lg:flex items-center gap-8">
        <?php foreach ($navItems as $item): ?>
          <?php 
            $isActive = strpos($pathCurrent, $item['href']) !== false;
            $classes = $isActive
              ? "text-accent font-bold"
              : "text-text-light dark:text-text-dark hover:text-accent dark:hover:text-accent font-medium";
          ?>
          <a class="text-sm transition-colors <?= $classes ?> nav-transition" href="<?= $item['href'] ?>">
            <?= $item['label'] ?>
          </a>
        <?php endforeach; ?>
      </nav>

      <div class="flex items-center gap-2 sm:gap-4">
        <form action="/app/views/pages/Product.php" method="GET" class="hidden lg:flex flex-col min-w-40 !h-10 max-w-64 transition-all duration-300 focus-within:w-64">
          <div class="flex w-full items-stretch rounded-full h-full focus-within:ring-2 focus-within:ring-accent/50 bg-gray-100 dark:bg-gray-800 border border-transparent focus-within:bg-white dark:focus-within:bg-gray-900 transition-all">
            <div class="text-gray-500 dark:text-gray-400 flex items-center justify-center pl-3">
              <span class="material-symbols-outlined text-[20px]">search</span>
            </div>
            <input name="q" class="flex w-full min-w-0 flex-1 rounded-r-full border-none bg-transparent h-full placeholder:text-gray-500 dark:placeholder:text-gray-400 px-3 text-sm text-text-light dark:text-text-dark focus:ring-0" placeholder="Tìm kiếm..."/>
          </div>
        </form>

        <a href="/app/views/pages/Cart.php" class="relative flex h-10 w-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-text-light dark:text-text-dark transition-colors nav-transition" data-cart-button>
          <span class="material-symbols-outlined">shopping_cart</span>
          <span class="absolute top-0 right-0 h-4 min-w-[16px] px-1 flex items-center justify-center rounded-full bg-accent text-white text-[10px] font-bold shadow-sm <?= $cartCount > 0 ? '' : 'hidden' ?>" data-cart-badge>
            <?= $cartCount ?>
          </span>
        </a>

        <div class="relative">
          <button data-user-toggle class="flex h-10 w-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-text-light dark:text-text-dark transition-colors focus:outline-none focus:ring-2 focus:ring-accent/20">
            <?php if ($isAuthenticated): ?>
                <?php if (!empty($user->avatar)): ?>
                    <img src="<?= htmlspecialchars($user->avatar) ?>" class="h-8 w-8 rounded-full object-cover border border-gray-200">
                <?php else: ?>
                    <span class="material-symbols-outlined fill-current text-accent">account_circle</span>
                <?php endif; ?>
            <?php else: ?>
                <span class="material-symbols-outlined">person</span>
            <?php endif; ?>
          </button>

          <div data-user-menu class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 hidden p-1 z-50 transform origin-top-right transition-all">
            <?php if ($isAuthenticated): ?>
              <div class="px-4 py-2 text-xs text-gray-500 border-b border-gray-100 dark:border-gray-700 mb-1">
                Xin chào, <span class="font-bold text-primary dark:text-accent"><?= htmlspecialchars($user->name ?? 'User') ?></span>
              </div>
              
              <a href="/app/views/pages/User.php" class="block px-4 py-2 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-text-light dark:text-text-dark nav-transition">
                Hồ sơ cá nhân
              </a>
              
              <a href="/app/views/pages/User.php" class="block px-4 py-2 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-text-light dark:text-text-dark">
                Đơn hàng của tôi
              </a>
              <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
              <a href="/app/views/pages/auth/Logout.php" class="flex items-center gap-2 px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                <span class="material-symbols-outlined text-lg">logout</span> Đăng xuất
              </a>
            <?php else: ?>
              <div class="px-4 py-2 text-xs text-gray-500 border-b border-gray-100 dark:border-gray-700 mb-1">Tài khoản</div>
              <a href="/app/views/pages/auth/SignIn.php" class="flex items-center gap-2 px-4 py-2 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-text-light dark:text-text-dark nav-transition"><span class="material-symbols-outlined text-lg">login</span> Đăng nhập</a>
              <a href="/app/views/pages/auth/SignUp.php" class="flex items-center gap-2 px-4 py-2 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-text-light dark:text-text-dark nav-transition"><span class="material-symbols-outlined text-lg">person_add</span> Đăng ký</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-background-dark">
    <div class="px-4 pt-4 pb-6 space-y-4">
      <div class="flex flex-col gap-1">
        <?php foreach ($navItems as $item): ?>
          <?php $isActive = strpos($pathCurrent, $item['href']) !== false; $classes = $isActive ? "bg-accent/10 text-accent font-bold" : "text-text-light dark:text-text-dark hover:bg-gray-100 dark:hover:bg-gray-800"; ?>
          <a class="block px-4 py-3 rounded-lg text-base transition-colors nav-transition <?= $classes ?>" href="<?= $item['href'] ?>"><?= $item['label'] ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</header>

<div id="page-transition-loader" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-white/60 backdrop-blur-[2px] transition-all duration-300">
    <div class="flex flex-col items-center gap-3">
        <span class="loading loading-bars loading-xl text-primary"></span>
    </div>
</div>

<style>
@keyframes bounce-cart {
    0%, 100% { transform: translateY(0); }
    25% { transform: translateY(-4px); }
    50% { transform: translateY(0); }
    75% { transform: translateY(-2px); }
}
.animate-bounce {
    animation: bounce-cart 0.5s ease-in-out;
    color: #ef4444; 
}
html {
    scroll-behavior: smooth; /* Cuộn mượt */
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // 1. Logic Rung Giỏ Hàng khi redirect về
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('added') === '1') {
        const cartIcon = document.querySelector('[data-cart-button]');
        if (cartIcon) {
            cartIcon.classList.add('animate-bounce'); 
            setTimeout(() => {
                cartIcon.classList.remove('animate-bounce');
            }, 1000);
        }
        // Xóa param trên URL
        const newUrl = window.location.pathname + window.location.search.replace(/[\?&]added=1/, '').replace(/^&/, '?');
        window.history.replaceState({}, document.title, newUrl);
    }

    // 2. Logic Loader Thông Minh
    const loader = document.getElementById('page-transition-loader');
    const links = document.querySelectorAll('.nav-transition');

    links.forEach(link => {
        link.addEventListener('click', function(e) {
            if (e.ctrlKey || e.shiftKey || e.metaKey || e.button === 1) return; // Mở tab mới
            if (this.getAttribute('href') === '#' || this.target === '_blank') return;
            try {
                const currentUrl = new URL(window.location.href);
                const targetUrl = new URL(this.href, window.location.origin);
                if (currentUrl.pathname === targetUrl.pathname && currentUrl.search === targetUrl.search) return;
            } catch (err) {}

            loader.classList.remove('hidden');
            loader.classList.add('flex');
            setTimeout(() => { loader.classList.add('hidden'); loader.classList.remove('flex'); }, 5000); 
        });
    });
});

// Tự tắt loader khi back
window.addEventListener('pageshow', function(event) {
    const loader = document.getElementById('page-transition-loader');
    if (loader) { loader.classList.add('hidden'); loader.classList.remove('flex'); }
});

// 3. Logic Mobile Menu
const btn = document.getElementById('mobile-menu-btn');
const menu = document.getElementById('mobile-menu');
if(btn && menu) {
    btn.addEventListener('click', () => {
        menu.classList.toggle('hidden');
        const icon = btn.querySelector('span');
        icon.textContent = menu.classList.contains('hidden') ? 'menu' : 'close';
    });
}

// 4. Logic User Dropdown (ĐÃ FIX: Bấm là hiện)
document.addEventListener("click", (e) => {
    // Tìm nút toggle gần nhất
    const toggleBtn = e.target.closest("[data-user-toggle]");
    const allMenus = document.querySelectorAll("[data-user-menu]");
    
    if (toggleBtn) {
        // Tìm menu tương ứng với nút bấm
        const targetMenu = toggleBtn.parentElement.querySelector("[data-user-menu]");
        
        // Đóng các menu khác
        allMenus.forEach((m) => {
            if (m !== targetMenu) m.classList.add("hidden");
        });

        // Bật/Tắt menu hiện tại
        if (targetMenu) {
            targetMenu.classList.toggle("hidden");
        }
    } else {
        // Nếu click ra ngoài menu -> Đóng hết
        if (!e.target.closest('[data-user-menu]')) {
            allMenus.forEach((m) => m.classList.add("hidden"));
        }
    }
});
</script>