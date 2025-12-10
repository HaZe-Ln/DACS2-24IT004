<?php
Import::repositories(["UserRepository"]);
Import::helpers(["Request", "Password"]);
Import::models(["User"]);
class AuthController
{
        /**
         * Xử lí đăng nhập
         * @return array [status(bool), keysError(array), message(string|null), data(?User)]
         */

    public function signIn()
    {
         // Lấy dữ liệu từ form
        $body = Request::dataArray(["email", "password"]);

        // 1. Thiếu field => trả về lỗi
        if (count($body['keysError']) > 0) {
            return [
                "status" => false,
                "keysError" => $body['keysError'],
                "message" => "Vui lòng điền đầy đủ thông tin.",
                "data" => null
            ];
        }

        [$email, $password] = $body['result'];

         // 2. Tìm user theo email
        $user = UserRepository::findByEmail($email);
        if ($user === null) {
            return [
                "status" => false,
                "keysError" => [],
                "message" => "Email không tồn tại.",
                "data" => null
            ];
        }

         // 3. Kiểm tra mật khẩu
         if (!Password::verify($password, $user->password)) {
            return [
                "status" => false,
                "keysError" => [],
                "message" => "Mật khẩu không chính xác.",
                "data" => null
            ];
        }

        // 4. Đăng nhập thành công
        return [
            "status" => true,
            "keysError" => [],
            "message" => null,
            "data" => $user
        ];
    }

     /**
         * Xử lí đăng kt
         * @return array [status(bool), keysError(array), message(string|null), data(?User)]
         */

    public function signUp() {
        // Lấy dữ liệu từ form
        $body = Request::dataArray(["name", "email","phone", "password", "confirmPassword"]);

        // 1. Thiếu field => trả về lỗi
        if (count($body['keysError']) > 0) {
            return [
                "status" => false,
                "keysError" => $body['keysError'],
                "message" => "Vui lòng điền đầy đủ thông tin.",
                "data" => null
            ];
        }

        [$name, $email, $phone ,$password, $confirmPassword] = $body['result'];
        // 2. Validate Số điện thoại (Cơ bản: chỉ chứa số, dài 10-12 ký tự)
        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            return [
                "status"   => false,
                "keysError"=> ["phone"],
                "message"  => "Số điện thoại không hợp lệ (phải đúng 10 số).",
                "data"     => null
            ];
        }
        // 3. Kiểm tra confirm password
        if ($password !== $confirmPassword) {
            return [
                "status"   => false,
                "keysError"=> ["confirmPassword"],
                "message"  => "Mật khẩu xác nhận không khớp.",
                "data"     => null
            ];
        }

        // 4. Kiểm tra độ mạnh mật khẩu
        if (!Password::validate($password)) {
            return [
                "status"   => false,
                "keysError"=> ["password"],
                "message"  => "Mật khẩu phải tối thiểu 8 ký tự, có chữ hoa, chữ thường và số.",
                "data"     => null
            ];
        }

        // 5. Check trùng email
        $existing = UserRepository::findByEmail($email);
        if ($existing !== null) {
            return [
                "status"   => false,
                "keysError"=> ["email"],
                "message"  => "Email đã được sử dụng.",
                "data"     => null
            ];
        }

        // 6. Tạo user mới
        $user = new User();
        $user->name     = $name;
        $user->email    = $email;
        $user ->phone   =$phone;
        $user->password = Password::hash($password);
        $user->role     = 'user';

        // 6. Lưu DB
        UserRepository::save($user);

        // Lấy lại user vừa tạo (để có id, v.v.)
        $created = UserRepository::findByEmail($email);

        return [
            "status"   => true,
            "keysError"=> [],
            "message"  => null,
            "data"     => $created ?? $user
        ];
    }
}