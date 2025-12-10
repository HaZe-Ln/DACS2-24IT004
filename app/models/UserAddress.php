<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Model.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Address.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/User.php';

class UserAddress extends Model
{
 public $id;   
 public Address $address;
 
 public User $user;
 
 public static function tables()
 {
  return [
   "id int primary key auto_increment",

   //relationship
   "user_id int ",
   "CONSTRAINT FK_UserAddress_User FOREIGN KEY (user_id) REFERENCES users(id)",

   "address_id int",
   "CONSTRAINT FK_UserAddress_Address FOREIGN KEY (address_id) REFERENCES addresss(id)"

  ];
 }
}