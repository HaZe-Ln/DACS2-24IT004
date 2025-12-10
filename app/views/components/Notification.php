<?php if (isset($message) && !empty($message)): ?>
    <?php
        // Xác định màu sắc dựa trên loại thông báo (success/error)
        $isSuccess = ($message['type'] === 'success');
        $bgColor   = $isSuccess ? 'bg-white border-l-4 border-green-500' : 'bg-white border-l-4 border-red-500';
        $iconColor = $isSuccess ? 'text-green-500' : 'text-red-500';
        $icon      = $isSuccess ? 'check_circle' : 'error';
        $title     = $isSuccess ? 'Thành công!' : 'Đã có lỗi!';
    ?>

    <div id="toast-notification" class="fixed top-24 right-5 z-[9999] animate-slide-in-right shadow-lg rounded-lg overflow-hidden max-w-sm w-full transition-all duration-500 transform translate-x-0 opacity-100">
        <div class="<?= $bgColor ?> p-4 flex items-start gap-3 shadow-sm">
            <div class="<?= $iconColor ?>">
                <span class="material-symbols-outlined text-2xl"><?= $icon ?></span>
            </div>
            
            <div class="flex-1">
                <h4 class="text-sm font-bold text-gray-800"><?= $title ?></h4>
                <p class="text-sm text-gray-600 mt-1 leading-relaxed">
                    <?= htmlspecialchars($message['text']) ?>
                </p>
            </div>

            <button onclick="closeToast()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        
        <div class="h-1 w-full bg-gray-100">
            <div class="h-full <?= $isSuccess ? 'bg-green-500' : 'bg-red-500' ?> animate-progress-bar"></div>
        </div>
    </div>

    <style>
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-slide-in-right { animation: slideInRight 0.5s ease-out forwards; }
        
        @keyframes progressBar {
            from { width: 100%; }
            to { width: 0%; }
        }
        .animate-progress-bar { animation: progressBar 3s linear forwards; }
    </style>
<?php endif; ?>