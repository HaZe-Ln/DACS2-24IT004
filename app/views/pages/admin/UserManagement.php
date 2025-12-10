<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["AdminUserController"]);

// 1. Gọi Controller
$controller = new AdminUserController();
$data = $controller->index();

// 2. Hứng dữ liệu
$users      = $data['users'];
$page       = $data['page'];
$totalPages = $data['totalPages'];
$search     = $data['search'];
$role       = $data['role'];
?>
<!DOCTYPE html>
<html lang="vi">
<?php Import::layout('Head', ["title" => "Quản lý Người dùng"]); ?>

<body class="font-display bg-gray-50 text-gray-900">
    <div class="relative flex min-h-screen w-full">
        <?php Import::layout('AdminSidebar', ["active" => "users"]); ?>

        <main class="flex-1 p-6 lg:p-8 overflow-y-auto">
            <?php // Bạn dán lại phần HTML view cũ vào đây nhé, nhớ sửa dòng link nút Edit ?>
            
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Quản lý Người dùng</h1>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
                            <form method="GET" action="/app/views/pages/admin/UserManagement.php" class="flex-1 w-full md:max-w-md">
                                <?php if($role !== 'all'): ?>
                                    <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">
                                <?php endif; ?>
                                <label class="flex flex-col w-full h-11">
                                    <div class="flex w-full items-stretch rounded-lg h-full border border-gray-300 bg-gray-50 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
                                        <div class="text-gray-500 flex items-center justify-center pl-3">
                                            <span class="material-symbols-outlined text-xl">search</span>
                                        </div>
                                        <input 
                                            name="q"
                                            value="<?= htmlspecialchars($search) ?>"
                                            class="form-input flex w-full min-w-0 flex-1 border-none bg-transparent h-full placeholder:text-gray-500 px-3 text-sm focus:ring-0" 
                                            placeholder="Tìm kiếm theo tên, email, sđt..." 
                                        />
                                    </div>
                                </label>
                            </form>

                            <a href="/app/views/pages/admin/CreateUser.php" class="flex w-full md:w-auto items-center justify-center rounded-lg h-11 bg-primary text-white gap-2 text-sm font-bold px-5 hover:bg-primary/90 transition-colors shadow-sm">
                                <span class="material-symbols-outlined text-xl">person_add</span>
                                <span class="truncate">Thêm Người dùng</span>
                            </a>
                        </div>
                        
                        <div class="flex gap-3 pt-4 overflow-x-auto">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-600">Vai trò:</span>
                                <div class="flex gap-2">
                                    <?php 
                                        $roles = ['all' => 'Tất cả', 'admin' => 'Quản trị viên', 'user' => 'Khách hàng'];
                                        $baseLink = "/app/views/pages/admin/UserManagement.php?q=" . urlencode($search);
                                    ?>
                                    <?php foreach ($roles as $key => $label): ?>
                                        <a href="<?= $baseLink . "&role=" . $key ?>" 
                                           class="px-3 py-1.5 rounded-lg text-sm font-medium border transition-colors <?= $role === $key ? 'bg-primary/10 border-primary text-primary' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
                                            <?= $label ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3">ID</th>
                                    <th class="px-6 py-3">Người dùng</th>
                                    <th class="px-6 py-3">Liên hệ</th>
                                    <th class="px-6 py-3">Ngày đăng ký</th>
                                    <th class="px-6 py-3">Vai trò</th>
                                    <th class="px-6 py-3 text-right">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Không tìm thấy người dùng nào.</td></tr>
                                <?php endif; ?>

                                <?php foreach ($users as $u): ?>
                                <tr class="bg-white border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-mono font-medium text-primary">#<?= $u->id ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-primary font-bold text-xs border border-gray-200">
                                                <?= strtoupper(substr($u->name ?? 'U', 0, 2)) ?>
                                            </div>
                                            <span class="font-medium text-gray-900"><?= htmlspecialchars($u->name) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-gray-900"><?= htmlspecialchars($u->email) ?></span>
                                            <span class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($u->phone ?? '---') ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        <?= isset($u->created_at) ? date('d/m/Y', strtotime($u->created_at)) : 'N/A' ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($u->role === 'admin'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">Quản trị viên</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">Khách hàng</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="/app/views/pages/admin/EditUser.php?id=<?= $u->id ?>" class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-yellow-600 transition-colors" title="Sửa thông tin">
                                                <span class="material-symbols-outlined text-xl">edit</span>
                                            </a>
                                            
                                            <form method="POST" action="/app/views/pages/admin/UserManagement.php" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $u->id ?>">
                                                <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-gray-500 hover:text-red-600 transition-colors" title="Xóa">
                                                    <span class="material-symbols-outlined text-xl">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    </div>
            </div>
        </main>
    </div>
</body>
</html>