<?php
Import::repositories(["UserRepository"]);

class UserAddressController
{
    /**
     * Xử lý thêm địa chỉ mới
     */
    public function store($userId)
    {
        $phone   = $_POST['phone'] ?? '';
        $city    = $_POST['city'] ?? '';
        $ward    = $_POST['ward'] ?? '';
        $address = $_POST['address'] ?? '';

        // Validate
        if (empty($city) || empty($address) || empty($phone)) {
            return ["type" => "error", "text" => "Vui lòng nhập đầy đủ thông tin địa chỉ!"];
        }

        $addrData = [
            'phone'   => $phone,
            'city'    => $city,
            'ward'    => $ward,
            'address' => $address
        ];

        UserRepository::addAddress($userId, $addrData);
        return ["type" => "success", "text" => "Thêm địa chỉ mới thành công!"];
    }

    /**
     * Xử lý xóa địa chỉ
     */
    public function delete($userId)
    {
        $addrId = $_POST['address_id'] ?? 0;

        if ($addrId > 0) {
            UserRepository::deleteAddress($userId, $addrId);
            return ["type" => "success", "text" => "Đã xóa địa chỉ khỏi sổ địa chỉ."];
        }

        return ["type" => "error", "text" => "Địa chỉ không hợp lệ."];
    }
}