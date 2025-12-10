<?php
class Password
{
  /**
   * Mã hóa mật khẩu
   * @param string $password
   * @return string hashed password
   */
  public static function hash(string $password): string
  {
    return password_hash($password, PASSWORD_BCRYPT);
  }

  /**
   * Kiểm tra mật khẩu với hash
   * @param string $password
   * @param string $hash
   * @return bool
   */
  public static function verify(string $password, string $hash): bool
  {
    return password_verify($password, $hash);
  }

  /**
   * Kiểm tra mật khẩu có mạnh hay không
   * @param string $password
   * @return bool
   */
  public static function validate(string $password): bool
  {
    // Ít nhất 8 ký tự, 1 chữ cái hoa, 1 chữ cái thường, 1 số
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password) === 1;
  }
}
