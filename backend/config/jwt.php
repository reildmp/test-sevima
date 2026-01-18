<?php
/**
 * JWT Configuration and Helper Functions
 */

class JWT
{
  private static $secret_key = "your-secret-key-change-this-in-production";
  private static $algorithm = 'HS256';
  private static $expiration = 86400; // 24 hours

  /**
   * Generate JWT token
   */
  public static function encode($payload)
  {
    $header = json_encode(['typ' => 'JWT', 'alg' => self::$algorithm]);
    $payload['exp'] = time() + self::$expiration;
    $payload['iat'] = time();

    $base64UrlHeader = self::base64UrlEncode($header);
    $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
    $base64UrlSignature = self::base64UrlEncode($signature);

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
  }

  /**
   * Decode and verify JWT token
   */
  public static function decode($jwt)
  {
    $tokenParts = explode('.', $jwt);

    if (count($tokenParts) !== 3) {
      throw new Exception('Invalid token format');
    }

    $header = base64_decode($tokenParts[0]);
    $payload = base64_decode($tokenParts[1]);
    $signatureProvided = $tokenParts[2];

    $base64UrlHeader = self::base64UrlEncode($header);
    $base64UrlPayload = self::base64UrlEncode($payload);
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
    $base64UrlSignature = self::base64UrlEncode($signature);

    if ($base64UrlSignature !== $signatureProvided) {
      throw new Exception('Invalid token signature');
    }

    $payloadData = json_decode($payload);

    if (isset($payloadData->exp) && $payloadData->exp < time()) {
      throw new Exception('Token has expired');
    }

    return $payloadData;
  }

  /**
   * Base64 URL encode
   */
  private static function base64UrlEncode($data)
  {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }
}
