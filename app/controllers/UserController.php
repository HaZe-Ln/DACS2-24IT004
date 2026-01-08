<?php
Import::repositories(["UserRepository"]);
Import::helpers(["Request"]);
Import::middlewares(["Authentication"]);

class UserController
{
    /**
     * Hàm này chỉ làm 1 việc: Lấy dữ liệu để hiển thị lên View
     */
    public function index()
    {
        // 1. Kiểm tra đăng nhập
        $currentUser = Authentication::getAuthentication();
        if (!$currentUser) {
            header("Location: /app/views/pages/auth/SignIn.php");
            exit;
        }

        $userId = $currentUser->id;

        // 2. Lấy dữ liệu mới nhất
        $user = UserRepository::findById($userId);
        
        // Avatar giả lập
        if (!isset($user->avatar) || empty($user->avatar)) {
            $encodedName = urlencode($user->name);
            $user->avatar = "https://ui-avatars.com/api/?name={$encodedName}&background=0D8ABC&color=fff&size=128";
        }

        $addresses = UserRepository::getAddressesByUserId($userId);
        
        // Mock data đơn hàng
        $orders = [
             (object) ["id" => "#ORD-9988", "date" => "05/12/2025", "total" => 15000000, "status" => "Đang giao"],
             (object) ["id" => "#ORD-1122", "date" => "20/11/2025", "total" => 4500000, "status" => "Hoàn thành"],
        ];

        return [
            'user'      => $user,
            'addresses' => $addresses,
            'orders'    => $orders
        ];
    }

    /**
     * Hàm này xử lý cập nhật thông tin cá nhân
     */
    public function update($currentUser)
    {
        $newName = $_POST['name'] ?? $currentUser->name;
        $newPhone = $_POST['phone'] ?? $currentUser->phone;

        if (!preg_match('/^[0-9]{10,12}$/', $newPhone)) {
            return ["type" => "error", "text" => "Số điện thoại không hợp lệ (10-12 số)!"];
        }

        $currentUser->name = $newName;
        $currentUser->phone = $newPhone;

        UserRepository::update($currentUser);
        Authentication::setAuthentication($currentUser); // Update Session
        
        return ["type" => "success", "text" => "Cập nhật thông tin thành công!"];
    }
    
}