<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["UserRepository"]);
Import::models(["User"]);
Import::helpers(["Password"]); // Để xử lý hash mật khẩu khi sửa
Import::middlewares(["Authentication"]);

class AdminUserController
{
    // 1. Hiển thị danh sách (kèm Lọc, Tìm kiếm, Phân trang)
    public function index()
    {
        // Check quyền Admin
        $currentUser = Authentication::getAuthentication();
        if (!$currentUser || $currentUser->role !== 'admin') {
            header("Location: /app/views/pages/auth/SignIn.php"); exit;
        }

        // Xử lý Xóa (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
            $deleteId = (int)$_POST['id'];
            if ($deleteId !== $currentUser->id) { // Không cho tự xóa mình
                UserRepository::delete($deleteId);
            }
            // Refresh trang
            $qs = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
            header("Location: /app/views/pages/admin/UserManagement.php" . $qs);
            exit;
        }

        // Lấy tham số GET
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $search = trim($_GET['q'] ?? '');
        $role = $_GET['role'] ?? 'all';

        // Query DB
        $users = UserRepository::paginate($page, $limit, $search, $role);
        $totalRecords = UserRepository::count($search, $role);
        $totalPages = ceil($totalRecords / $limit);

        return [
            'users' => $users,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'role' => $role
        ];
    }

    // 2. Lấy thông tin để hiển thị Form Sửa
    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $user = UserRepository::findById($id);
        
        if (!$user) {
            header("Location: /app/views/pages/admin/UserManagement.php");
            exit;
        }
        return $user;
    }

    // 3. Xử lý Cập nhật (POST từ Form Sửa)
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $name = trim($_POST['name']);
            $email = trim($_POST['email']); // (Lưu ý: Thường ít khi cho sửa email vì liên quan đăng nhập, nhưng cứ để đây)
            $phone = trim($_POST['phone']);
            $role = $_POST['role'];
            $password = $_POST['password']; // Mật khẩu mới (nếu có)

            $user = UserRepository::findById($id);
            if ($user) {
                $user->name = $name;
                $user->phone = $phone;
                $user->role = $role;
                
                // Nếu có nhập mật khẩu mới thì hash và lưu, không thì giữ nguyên
                if (!empty($password)) {
                    $user->password = Password::hash($password);
                }

                UserRepository::update($user);
            }

            // Redirect về trang danh sách hoặc trang edit báo thành công
            header("Location: /app/views/pages/admin/UserManagement.php");
            exit;
        }
    }
}