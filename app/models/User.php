<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Model.php';

class User extends Model
{
  public $id;
  public $name;
  public $password;
  public $email;
  public $phone;
  public $role;

  public $avatar;
  public static function tables()
  {
    return [
      "id int primary key auto_increment",
      "name varchar(255) not null",
      "email varchar(255) unique not null",
      "password text not null",
      "phone varchar(11)",
      "role ENUM('admin', 'user') DEFAULT 'user'",
    ];
  }
}
