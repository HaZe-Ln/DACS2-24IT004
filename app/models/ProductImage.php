<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Model.php';
class ProductImage extends Model
{
 public $id, $url, $product_id;

 const FieldJoin = "pi.id as pi_id, pi.url as pi_url, pi.product_id as pi_product_id";
 public function fillJoin($row)
    {

        $this->id = $row["pi_id"];
        $this->url = $row["pi_url"];
        $this->product_id = $row["pi_product_id"];
    }
 public static function tables()
 {
  return 
  [
   "id int primary key auto_increment",
   "url text",

   "product_id int ",
   "CONSTRAINT FK_ProductImage_Product FOREIGN KEY (product_id) REFERENCES products(id)" 
  ];
 }
}
