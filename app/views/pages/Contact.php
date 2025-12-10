<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';

$contactInfo = [
  ["icon" => "location_on", "title" => "Địa chỉ", "text" => "333 Cao Hồng Lãnh, Hòa Quý, TP. Hồ Chí Minh"],
  ["icon" => "phone", "title" => "Số điện thoại", "text" => "(+84) 383028421 "],
  ["icon" => "email", "title" => "Email hỗ trợ", "text" => "support@htamusic.vn"],
  ["icon" => "schedule", "title" => "Giờ làm việc", "text" => "Thứ 2 - Thứ 7: 8:00 - 21:00 • CN: 9:00 - 18:00"],
];
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Liên hệ"]); ?>

<body class="font-display bg-background-light text-text-light">
  <?php Import::layout("UserNavigation") ?>

  <main class="flex-grow">
    <div class="px-4 sm:px-6 md:px-10 flex justify-center py-10 md:py-14">
      <div class="layout-content-container flex flex-col max-w-6xl flex-1 gap-10">
        <div class="text-center space-y-3">
          <h1 class="text-4xl font-bold text-primary">Liên hệ với HTAMusic</h1>
          <p class="text-lg text-gray-600">Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
          <!-- Thông tin & bản đồ -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col gap-6">
            <h2 class="text-2xl font-bold text-text-light">Thông tin liên hệ</h2>
            <div class="space-y-5 text-gray-700">
              <?php foreach ($contactInfo as $info): ?>
                <div class="flex items-start gap-3">
                  <span class="material-symbols-outlined text-primary mt-0.5"><?= htmlspecialchars($info["icon"]) ?></span>
                  <div>
                    <h3 class="font-semibold"><?= htmlspecialchars($info["title"]) ?></h3>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($info["text"]) ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="rounded-lg overflow-hidden border border-gray-200">
              <iframe
                class="w-full h-64 md:h-80"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3835.5568903191975!2d108.23512110925648!3d15.984500184618877!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31421a77e213d2cb%3A0x9fb1c2b7c5e95c2!2zMzMzIENhbyBI4buTbmcgTMOjbmgsIEhvw6AgUXXDvSwgTmfFqSBIw6BuaCBTxqFuLCDEkMOgIE7hurVuZyA1NTAwMDAsIFZpZXRuYW0!5e0!3m2!1sen!2s!4v1764941419656!5m2!1sen!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
              </iframe>
            </div>
          </div>

          <!-- Form -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-text-light mb-6">Gửi tin nhắn cho chúng tôi</h2>
            <form class="space-y-5" action="#" method="POST">
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700" for="name">Họ và tên</label>
                <input id="name" name="name" type="text" required
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-50 focus:ring-accent focus:border-accent"
                  placeholder="Nguyễn Văn A" />
              </div>
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700" for="email">Email</label>
                <input id="email" name="email" type="email" required
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-50 focus:ring-accent focus:border-accent"
                  placeholder="ban@example.com" />
              </div>
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700" for="subject">Chủ đề</label>
                <input id="subject" name="subject" type="text"
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-50 focus:ring-accent focus:border-accent"
                  placeholder="Vấn đề bạn cần hỗ trợ" />
              </div>
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700" for="message">Nội dung tin nhắn</label>
                <textarea id="message" name="message" rows="6" required
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-50 focus:ring-accent focus:border-accent"
                  placeholder="Viết nội dung chi tiết tại đây..."></textarea>
              </div>
              <button type="submit"
                class="w-full inline-flex items-center justify-center bg-primary text-white font-semibold py-3 px-6 rounded-lg shadow-md hover:bg-primary/90 transition-colors">
                Gửi tin nhắn
                <span class="material-symbols-outlined ml-2 text-base">send</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php Import::layout("Footer") ?>
</body>
</html>
