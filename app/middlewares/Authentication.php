<?php
Import::helpers(["Request", "Cookie"]);
Import::models(["User"]);
Import::configs(["JWT"]);
/**
 * Summary of Authentication
 * -Xác thực tài khoản, bao gồm tạo token khi sign in thành công, kiểm tra đã sign in chưa , kèm lấy thông tin từ token khi đã xác thực
 */
class Authentication
{
    public static function isAuthenticated()
    {
        $token = Cookie::get("token");
        if ($token == null) {
            return false;
        }
        return true;
    }
    public static function setAuthentication($user)
    {
        $data = $user->toArray();
        if (isset($data["password"]) && $data["password"]) {
            unset($data["password"]);
        }
        $jwt = JWT::encode($data);
        Cookie::set("token", $jwt);
    }
    /**
     * Summary of getAuthentication
     * @return null | User
     */
    public static function getAuthentication()
    {
        if (!self::isAuthenticated()) {
            return null;
        }
        $token = Cookie::get("token");
        $data = JWT::decode($token);
        if ($data == false) {
            return null;
        }
        $user = new User();
        $user->fill($data);
        return $user;
    }
    
}
// Xử lý API check authentication
if (isset($_GET['action']) && $_GET['action'] === 'check') {
    header('Content-Type: application/json');
    $isAuth = self::isAuthenticated();
    echo json_encode(['authenticated' => $isAuth]);
    exit;
}
