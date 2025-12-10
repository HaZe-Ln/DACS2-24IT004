<?php

/**
 * Class JWT (jsonwebtoken) - PHP thuần (HS256) static
 */
class JWT
{
  private static string $alg = 'HS256';

  /**
   * Lấy secret từ environment
   */
  private static function getSecret(): string
  {
    $secret = getenv('JWT_SECRET') ?: ($_ENV['JWT_SECRET'] ?? null);
    if (!$secret) {
      throw new \Exception("JWT secret not set in environment variable JWT_SECRET");
    }
    return $secret;
  }

  /**
   * Encode dữ liệu base64Url
   */
  private static function base64UrlEncode(string $data): string
  {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }

  /**
   * Decode dữ liệu base64Url
   */
  private static function base64UrlDecode(string $data): string
  {
    $pad = 4 - (strlen($data) % 4);
    if ($pad < 4) $data .= str_repeat('=', $pad);
    return base64_decode(strtr($data, '-_', '+/'));
  }

  /**
   * Tạo JWT
   * @param array $payload dữ liệu payload
   * @return string JWT token
   */
  public static function encode(array $payload): string
  {
    $secret = self::getSecret();
    $header = ['typ' => 'JWT', 'alg' => self::$alg];

    $header_encoded = self::base64UrlEncode(json_encode($header));
    $payload_encoded = self::base64UrlEncode(json_encode($payload));

    $signature = '';
    if (self::$alg === 'HS256') {
      $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", $secret, true);
    }

    $signature_encoded = self::base64UrlEncode($signature);

    return "$header_encoded.$payload_encoded.$signature_encoded";
  }

  /**
   * Giải mã và xác thực JWT
   * @param string $jwt token
   * @return array|false payload hoặc false nếu không hợp lệ
   */
  public static function decode(string $jwt)
  {
    $secret = self::getSecret();
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return false;

    list($header_encoded, $payload_encoded, $signature_encoded) = $parts;

    $signature = '';
    if (self::$alg === 'HS256') {
      $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", $secret, true);
    }

    $signature_check = self::base64UrlEncode($signature);

    if (!hash_equals($signature_check, $signature_encoded)) return false;

    $payload = json_decode(self::base64UrlDecode($payload_encoded), true);

    // Kiểm tra thời gian hết hạn nếu có
    if (isset($payload['exp']) && time() > $payload['exp']) return false;

    return $payload;
  }
}
