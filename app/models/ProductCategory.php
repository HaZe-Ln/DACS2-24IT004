<?php
require_once "Model.php";
class ProductCategory extends Model
{

  public $id, $name, $slug, $deleted_at;
  public $product_count;
  const FieldJoin = "pc.id as pc_id, pc.name as pc_name, pc.slug as pc_slug, pc.deleted_at as pc_deleted_at";
  public function fillJoin($row)
  {
    $this->id = $row["pc_id"];
    $this->name = $row["pc_name"];
    $this->slug = $row["pc_slug"];
    $this->deleted_at = $row["pc_deleted_at"];
  }
  public static function tables()
  {
    return [
      "id int primary key auto_increment",
      "name varchar(255) not null ",
      "slug varchar(255) not null unique",
      "deleted_at TIMESTAMP"
    ];
  }
}
