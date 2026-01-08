<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Model.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Order.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Product.php';

class ProductValuation extends Model
{
 public $id,$star_rate,$content,$order_id, $product_id;

 public Order $order;

 public Product $product;

 public static function tables()
 {
  return 
  [
   "id int primary key auto_increment",
   "star_rate int not null check (star_rate >= 1 AND star_rate <= 5)",
   "content longtext",
   "order_id int",
    "CONSTRAINT FK_ProductValuation_Order FOREIGN KEY (order_id) REFERENCES orders(id) ",

    "product_id int",
    "CONSTRAINT FK_ProductValuation_Product FOREIGN KEY (product_id) REFERENCES products(id)"
  ];
 }
}
