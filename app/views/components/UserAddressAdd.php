<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8 animate-fade-in-up">
    <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
        <h3 class="text-xl font-bold text-primary flex items-center gap-2">
            <span class="material-symbols-outlined">add_location_alt</span> Thêm địa chỉ mới
        </h3>
        <button type="button" onclick="showTab('address')" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm font-medium transition-colors">
            <span class="material-symbols-outlined text-base">arrow_back</span> Quay lại
        </button>
    </div>

    <form method="POST" action="">
        <input type="hidden" name="action" value="add_address">
        
        <div class="space-y-6 max-w-2xl">
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">Số điện thoại <span class="text-red-500">*</span></label>
                <input type="text" name="phone" required placeholder="Ví dụ: 0912345678" 
                       class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary outline-none transition-all">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Tỉnh / Thành phố <span class="text-red-500">*</span></label>
                    <input type="text" name="city" required placeholder="Hà Nội" 
                           class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary outline-none transition-all">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Quận / Huyện <span class="text-red-500">*</span></label>
                    <input type="text" name="ward" required placeholder="Cầu Giấy" 
                           class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary outline-none transition-all">
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">Địa chỉ cụ thể <span class="text-red-500">*</span></label>
                <textarea name="address" required rows="3" placeholder="Số nhà, tên đường..." 
                          class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary outline-none transition-all"></textarea>
            </div>

            <div class="pt-4 flex gap-4">
                <button type="submit" class="btn bg-primary text-white hover:bg-primary/90 px-6 py-3 rounded-lg font-bold shadow-md transition-transform hover:scale-105">
                    Lưu địa chỉ
                </button>
                <button type="button" onclick="showTab('address')" class="btn bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 px-6 py-3 rounded-lg font-medium">
                    Hủy bỏ
                </button>
            </div>
        </div>
    </form>
</div>