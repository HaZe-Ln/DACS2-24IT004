<style>
    /* 1. Hiệu ứng rung lắc (Pulse) */
    @keyframes pulse-ring {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(38, 220, 62, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(220, 38, 38, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
    }
    
    /* Class này sẽ kích hoạt rung */
    .btn-pulse {
        animation: pulse-ring 2s infinite;
    }
    
    /* 2. Hiệu ứng menu trượt */
    .social-menu {
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); /* Chỉnh lại cho mượt hơn */
        opacity: 0;
        transform: translateY(20px) scale(0.95);
        pointer-events: none;
        visibility: hidden;
    }
    
    /* Khi active: Hiện lên */
    .social-menu.active {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
        visibility: visible;
    }
    
    /* Style nút con */
    .social-btn {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s;
        color: white;
    }
    .social-btn:hover {
        transform: scale(1.1);
    }
    .social-btn img, .social-btn svg {
        width: 24px;
        height: 24px;
        fill: currentColor;
    }
</style>

<div class="fixed bottom-6 right-6 z-50 flex flex-col items-center gap-3">
    
    <div id="social-list" class="social-menu active flex flex-col gap-3 pb-2">
        
        <a href="tel:0702830303" title="Gọi ngay" class="social-btn bg-orange-500 hover:bg-orange-600">
            <span class="material-symbols-outlined">call</span>
        </a>

        <a href="https://www.facebook.com/ha.ln.275085" target="_blank" title="Facebook" class="social-btn bg-[#1877F2]">
            <svg viewBox="0 0 24 24"><path d="M12 2.04C6.5 2.04 2 6.53 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85C10.44 7.34 11.93 5.96 14.15 5.96C15.21 5.96 16.12 6.04 16.12 6.04V8.51H15.02C13.78 8.51 13.39 9.28 13.39 10.07V12.06H16.18L15.73 14.96H13.39V21.96C18.17 21.21 21.83 17.06 21.83 12.06C21.83 6.53 17.33 2.04 12 2.04Z"></path></svg>
        </a>

        <a href="https://www.tiktok.com/@ha.ln_?_r=1&_t=ZS-92u44dtUcBb" target="_blank" title="TikTok" class="social-btn bg-black">
            <svg viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"></path></svg>
        </a>

        <a href="https://m.me/username" target="_blank" title="Messenger" class="social-btn bg-gradient-to-tr from-blue-500 via-blue-600 to-purple-500">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.03 2 11C2 13.66 3.32 16.03 5.39 17.56V22L9.16 19.93C10.07 20.18 11.02 20.31 12 20.31C17.52 20.31 22 16.28 22 11.31C22 6.34 17.52 2 12 2ZM13.34 13.69L10.84 11.03L5.97 13.69L11.31 8L13.81 10.66L18.69 8L13.34 13.69Z"></path></svg>
        </a>

        <a href="https://zalo.me/0702830303" target="_blank" title="Zalo" class="social-btn bg-[#0068FF] font-bold text-sm">
            Zalo
        </a>

        <a href="https://maps.app.goo.gl/vo7Rjd7qkt2sPXG58" target="_blank" title="Chỉ đường đến 123 Hòa Phước" class="social-btn bg-white text-gray-700">
             <img src="https://upload.wikimedia.org/wikipedia/commons/a/aa/Google_Maps_icon_%282020%29.svg" alt="Maps" class="w-6 h-6">
        </a>
    </div>

    <button id="social-toggle" class="btn-pulse flex items-center justify-center w-14 h-14 bg-green-800 text-white rounded-full shadow-lg hover:bg-red-700 transition-colors focus:outline-none z-50">
        <span id="icon-chat" class="material-symbols-outlined text-3xl hidden">chat</span>
        <span id="icon-close" class="material-symbols-outlined text-3xl">close</span>
    </button>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toggleBtn = document.getElementById('social-toggle');
        const menuList = document.getElementById('social-list');
        const iconChat = document.getElementById('icon-chat');
        const iconClose = document.getElementById('icon-close');
        
        // Mặc định là ĐANG MỞ
        let isOpen = true;

        toggleBtn.addEventListener('click', function() {
            isOpen = !isOpen; // Đảo trạng thái
            
            if (isOpen) {
                // MỞ RA
                menuList.classList.add('active');
                iconChat.classList.add('hidden');
                iconClose.classList.remove('hidden');
                // Mình KHÔNG xóa class 'btn-pulse' nữa để nó luôn rung theo ý bạn
            } else {
                // ĐÓNG LẠI
                menuList.classList.remove('active');
                iconChat.classList.remove('hidden');
                iconClose.classList.add('hidden');
            }
        });
    });
</script>