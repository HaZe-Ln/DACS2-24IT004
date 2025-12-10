<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8 animate-fade-in-up">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-primary flex items-center gap-2">
            <span class="material-symbols-outlined">home_pin</span> Sổ địa chỉ
        </h3>
        <button type="button" onclick="showTab('add-address')" 
                class="flex items-center gap-1 text-sm bg-primary/10 text-primary px-3 py-1.5 rounded-lg hover:bg-primary hover:text-white transition-colors">
            <span class="material-symbols-outlined text-base">add</span> Thêm mới
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php if(empty($addresses)): ?>
            <div class="col-span-2 flex flex-col items-center justify-center py-8 text-gray-400 border-2 border-dashed border-gray-200 rounded-xl">
                <span class="material-symbols-outlined text-4xl mb-2">location_off</span>
                <p>Bạn chưa lưu địa chỉ nào.</p>
            </div>
        <?php else: ?>
            <?php foreach($addresses as $addr): ?>
                <div class="group relative p-4 rounded-lg border border-gray-200 hover:border-primary/50 transition-all hover:shadow-md hover:-translate-y-1 bg-white">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-gray-800"><?= htmlspecialchars($userName ?? 'Tôi') ?></p>
                            <p class="text-sm text-gray-500 mt-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">call</span>
                                <?= htmlspecialchars($addr->phone) ?>
                            </p>
                            <p class="text-sm text-gray-600 mt-2 leading-relaxed">
                                <?= htmlspecialchars($addr->address) ?>,<br>
                                <?= htmlspecialchars($addr->ward) ?>, <?= htmlspecialchars($addr->city) ?>
                            </p>
                        </div>
                        <div class="flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <form method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa địa chỉ này?');">
                                <input type="hidden" name="action" value="delete_address">
                                <input type="hidden" name="address_id" value="<?= $addr->id ?>">
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-full transition-colors" title="Xóa">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>