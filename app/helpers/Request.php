<?php
class Request
{
    /**
     * Lấy dữ liệu từ GET hoặc POST và escape HTML
     */
    public static function data(string $key, $default = null)
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $value = null;

        switch ($method) {
            case 'GET':
                $value = $_GET[$key] ?? $default;
                break;
            case 'POST':
                $value = $_POST[$key] ?? $default;
                break;
        }
        // Nếu có giá trị, escape HTML
        return $value !== null ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : $default;
    }
    /**
     * Trả về result là mảng kết quả tương ứng với key
     */
    public static function dataArray($keys = [])
    {
        $result = [];
        $keysError = [];
        foreach ($keys as $key) {
            $value = self::data($key);
            if ($value == null) {
                array_push($keysError, $key);
            } else {
                array_push($result,  $value);
            }
        }
        return [
            "keysError" =>  $keysError,
            "result" => $result
        ];
    }
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}
