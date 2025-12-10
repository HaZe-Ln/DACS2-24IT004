<?php
require_once "Model.php";
class Post  extends Model
{
  public $id, $name, $slug, $content, $thumb_url, $visibility, $deleted_at;
  public static function tables()
  {
    return [
      "id int primary key auto_increment",
      "name varchar(255) not null",
      "slug varchar(255) not null unique",
      "content longtext",
      "thumb_url text",
      "visibility ENUM('public','private') DEFAULT 'private'",
      "deleted_at TIMESTAMP"
    ];
  }
}
