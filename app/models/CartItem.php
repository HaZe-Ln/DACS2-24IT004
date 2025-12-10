<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Model.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Product.php';

 class CartItem extends Model
 {
  public $id,$quantity;
  public $user_id;    // [MỚI] Thêm dòng này để fix lỗi Deprecated
  public $product_id; // [MỚI] Thêm dòng này cho đầy đủ
  public User $user;
  public Product $product;

  public static function tables()  
  {
    return 
    [
     "id int primary key auto_increment",
     "quantity bigint not null",

     //relationship
     "user_id int ",
     "CONSTRAINT FK_CartItem_User FOREIGN KEY (user_id) REFERENCES users(id)",
      
     "product_id int ",
     "CONSTRAINT FK_CartItem_Product FOREIGN KEY (product_id) REFERENCES products(id)"
    ];
 }
}