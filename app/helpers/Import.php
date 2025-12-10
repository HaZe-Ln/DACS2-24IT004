<?php
class Import
{
  // variable có dạng : ["title" => "Trang chủ"]
  private static function modules($moduleName, $fileNames = [], $variables = [], $is_require = false)
  {
    foreach ($fileNames as $name) {
      $path = $_SERVER['DOCUMENT_ROOT'] . "/{$moduleName}/{$name}.php";
      if (file_exists($path)) {
        // Tạo biến từ mảng $variables
        if (!empty($variables)) {
          extract($variables);
          // require thay vì require_once để load nhiều lần với biến khác nhau
        }
        if ($is_require) {
          require $path;
        } else {
          require_once $path;
        }
      } else {
        throw new Exception("File not found in {$moduleName}: {$name}.php");
      }
    }
  }
  public static function controllers($fileNames = [])
  {
    self::modules("app/controllers", $fileNames);
  }
  public static function services($fileNames = [])
  {
    self::modules("app/services", $fileNames);
  }

  public static function repositories($fileNames = [])
  {
    self::modules("app/repositories", $fileNames);
  }
  public static function middlewares($fileNames = [])
  {
    self::modules("app/middlewares", $fileNames);
  }

  public static function helpers($fileNames = [])
  {
    self::modules("app/helpers", $fileNames);
  }

  public static function models($fileNames = [])
  {
    self::modules("app/models", $fileNames);
  }
  public static function configs($fileNames = [])
  {
    self::modules("app/configs", $fileNames);
  }
  public static function layout($fileName, $variables = [])
  {
    self::modules("app/views/layouts", [$fileName], $variables, true);
  }
  public static function page($fileName)
  {
    self::modules("app/views/pages", [$fileName], true);
  }
  public static function component($fileName, $variables = [])
  {
    self::modules("app/views/components", [$fileName], $variables, true);
  }
}
