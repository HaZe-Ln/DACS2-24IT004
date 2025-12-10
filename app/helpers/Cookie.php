<?php
class Cookie
{
    /**
     * Đặt cookie
     * 
     * @param string $name
     * @param string $value
     * @param int $expire Thời gian tồn tại tính bằng giây từ hiện tại (default 1 ngày)
     * @param string $path
     * @param string|null $domain
     * @param bool $secure
     * @param bool $    
     * @return void
     */
    public static function set(
        string $name,
        string $value,
        int $expire = 86400,
        string $path = '/',
        ?string $domain = null,
        bool $secure = false,
        bool $httponly = true
    ): void {
        setcookie($name, $value, [
            'expires' => time() + $expire,
            'path' => $path,
            'domain' => $domain ?? '',
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => 'Lax' // Optional: 'Strict' | 'None'
        ]);
        $_COOKIE[$name] = $value; // cập nhật luôn cho request hiện tại
    }

    /**
     * Lấy giá trị cookie
     * 
     * @param string $name
     * @param mixed $default
     * @return string|null
     */
    public static function get(string $name, mixed $default = null): ?string
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
    }

    /**
     * Xóa cookie
     * 
     * @param string $name
     * @param string $path
     * @param string|null $domain
     * @return void
     */
    public static function delete(string $name, string $path = '/', ?string $domain = null): void
    {
        setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => $path,
            'domain' => $domain ?? '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE[$name]); // xóa luôn khỏi request hiện tại
    }
}
