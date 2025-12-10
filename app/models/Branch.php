<?php
require_once "Model.php";
class Branch extends Model
{
  public $id, $name, $slug, $deleted_at, $address, $description;
  const FieldJoin = "b.id as b_id, b.name as b_name, b.slug as b_slug, b.address as b_address, b.deleted_at as b_deleted_at, b.description as b_description";
  public function fillJoin($row)
  {
    $this->id = $row["b_id"];
    $this->name = $row["b_name"];
    $this->slug = $row["b_slug"];
    $this->address = $row["b_address"];
    $this->deleted_at = $row["b_deleted_at"];
    $this->description = $row["b_description"];
  }
  public static function tables()
  {
    return [
      "id int primary key auto_increment",
      "name varchar(255) not null ",
      "slug varchar(255) not null unique",
      "deleted_at TIMESTAMP",
      "address text not null",
      "description longtext"
    ];
  }
}
