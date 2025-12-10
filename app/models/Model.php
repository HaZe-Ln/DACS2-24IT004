<?php
  date_default_timezone_set('Asia/Ho_Chi_Minh');
abstract class Model
{
  public $created_at;
  public $updated_at;
  public function fill($data)
  {
    foreach ($data as $key => $value) {
      if (property_exists($this, $key)) {
        $this->$key = $value;
      }
    }
  }
  public function toArray($excludeNull = true)
  {
    $data = get_object_vars($this); // Lấy tất cả thuộc tính public
    if ($excludeNull) {
      $data = array_filter($data, function ($v) {
        return $v !== null;
      });
    }
    return $data;
  }
}
