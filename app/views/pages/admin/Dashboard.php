<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
// 1. Import Controller
Import::controllers(["AdminDashboardController"]);

// 2. Gọi Controller để lấy dữ liệu thật từ Database
$controller = new AdminDashboardController();
$data = $controller->index();

// 3. Hứng dữ liệu từ Controller trả về
$metrics       = $data['metrics'];
$revenueSeries = $data['revenueSeries'];
$orderStatus   = $data['orderStatus'];

// 4. Tính tổng số đơn (để tính % cho biểu đồ tròn)
// Lưu ý: array_sum có thể trả về 0 nếu chưa có đơn hàng, cần handle để tránh lỗi chia cho 0
$totalOrdersCount = array_sum($orderStatus["data"]); 
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Admin Dashboard"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
  <div class="min-h-screen flex">
    <?php Import::layout('AdminSidebar', ["active" => "dashboard"]); ?>

    <main class="flex-1 flex flex-col">
      <header class="flex items-center justify-between border-b border-gray-200 px-4 sm:px-6 lg:px-10 py-3 bg-white sticky top-0 z-10">
        <div class="flex items-center gap-2 md:hidden">
          <span class="material-symbols-outlined">menu</span>
          <h1 class="font-semibold">HTAMusic Admin</h1>
        </div>
        <div class="flex flex-1 justify-end items-center gap-3 sm:gap-4">
          <label class="hidden md:flex flex-col min-w-40 !h-10 max-w-64">
            <div class="flex w-full items-stretch rounded-lg h-full bg-gray-100 dark:bg-gray-800">
              <div class="text-gray-500 flex items-center justify-center pl-3 rounded-l-lg">
                <span class="material-symbols-outlined">search</span>
              </div>
              <input class="flex w-full min-w-0 flex-1 rounded-r-lg border-none bg-transparent h-full placeholder:text-gray-500 px-3 text-sm text-gray-900 dark:text-white focus:ring-0" placeholder="Tìm kiếm..." />
            </div>
          </label>
          <button class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">
            <span class="material-symbols-outlined">notifications</span>
          </button>
          <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10" style='background-image: url("https://ui-avatars.com/api/?name=Admin&background=0D8ABC&color=fff");'></div>
        </div>
      </header>

      <div class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50">
        <div class="max-w-7xl mx-auto flex flex-col gap-8">
          <div class="flex flex-wrap justify-between gap-3">
            <h1 class="text-3xl font-bold text-primary">Tổng quan</h1>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            <?php foreach ($metrics as $card): ?>
              <div class="flex flex-col gap-1 rounded-xl p-4 bg-white border border-gray-200 shadow-sm transition-transform hover:-translate-y-1">
                <div class="flex items-center gap-3 text-gray-500">
                  <span class="material-symbols-outlined text-2xl"><?= $card["icon"] ?></span>
                  <p class="text-sm font-medium"><?= htmlspecialchars($card["label"]) ?></p>
                </div>
                <p class="text-2xl font-bold text-gray-900 tracking-tight leading-tight whitespace-nowrap">
                    <?= htmlspecialchars($card["value"]) ?>
                </p>
                <div class="flex items-center gap-1 text-xs <?= $card["trend_color"] ?>">
                    <span class="material-symbols-outlined text-base">trending_up</span>
                    <span><?= htmlspecialchars($card["trend"]) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 flex flex-col gap-4 rounded-xl border border-gray-200 p-6 bg-white shadow-sm">
              <div class="flex flex-wrap items-center justify-between gap-4">
                <h3 class="text-lg font-semibold text-gray-900">Báo cáo doanh thu (15 ngày qua)</h3>
                <div class="flex items-center gap-2 rounded-lg bg-gray-100 p-1">
                  <button class="px-3 py-1 text-sm font-semibold bg-white rounded-md shadow-sm">Ngày</button>
                  <button class="px-3 py-1 text-sm text-gray-600 hover:text-gray-900">Tháng</button>
                </div>
              </div>
              <div class="w-full h-80">
                  <canvas id="revenueChart"></canvas>
              </div>
            </div>

            <div class="lg:col-span-1 flex flex-col gap-4 rounded-xl border border-gray-200 p-6 bg-white shadow-sm">
              <h3 class="text-lg font-semibold text-gray-900">Trạng thái đơn hàng</h3>
              
              <div class="w-full h-64 flex items-center justify-center">
                  <?php if($totalOrdersCount > 0): ?>
                    <canvas id="orderStatusChart"></canvas>
                  <?php else: ?>
                    <p class="text-gray-400 text-sm">Chưa có dữ liệu đơn hàng</p>
                  <?php endif; ?>
              </div>
              
              <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm mt-4">
                <?php foreach ($orderStatus["labels"] as $i => $label): ?>
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <div class="w-3 h-3 rounded-full" style="background: <?= $orderStatus["colors"][$i] ?>;"></div>
                      <span class="text-gray-600 dark:text-gray-300"><?= htmlspecialchars($label) ?></span>
                    </div>
                    <span class="font-medium text-text-light">
                      <?= $totalOrdersCount > 0 ? number_format($orderStatus["data"][$i] / $totalOrdersCount * 100, 0) : 0 ?>%
                    </span>
                  </div>
                <?php endforeach; ?>
              </div>

            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Nhận dữ liệu từ PHP
    const revenueData = <?= json_encode($revenueSeries) ?>;
    const orderStatusData = <?= json_encode($orderStatus) ?>;

    // 1. Vẽ biểu đồ Doanh thu
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
      new Chart(revenueCtx, {
        type: 'line',
        data: {
          labels: revenueData.labels,
          datasets: [{
            data: revenueData.data,
            backgroundColor: 'rgba(43, 108, 238, 0.15)',
            borderColor: '#2b6cee',
            borderWidth: 2,
            pointBackgroundColor: '#2b6cee',
            pointRadius: 4,
            tension: 0.4,
            fill: true,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false }, tooltip: {
              callbacks: {
                  label: function(context) {
                      let label = context.dataset.label || '';
                      if (label) { label += ': '; }
                      if (context.parsed.y !== null) {
                          label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
                      }
                      return label;
                  }
              }
          }},
          scales: {
            y: { beginAtZero: true, ticks: { color: '#64748b' } },
            x: { ticks: { color: '#64748b' } }
          }
        }
      });
    }

    // 2. Vẽ biểu đồ Trạng thái
    const orderCtx = document.getElementById('orderStatusChart');
    if (orderCtx) {
      new Chart(orderCtx, {
        type: 'doughnut',
        data: {
          labels: orderStatusData.labels,
          datasets: [{
            data: orderStatusData.data,
            backgroundColor: orderStatusData.colors,
            hoverOffset: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          cutout: '70%'
        }
      });
    }
  </script>
</body>
</html>