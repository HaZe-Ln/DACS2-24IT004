<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Model.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Order.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Product.php';

class OrderItem extends Model
{
    public $id;
    public $product_name;
    public $product_slug;
    public $product_description;
    public $quantity;
    public $product_price;
    public $product_total_price;

    public $product_image;

    public $product_id;      // Để lưu ID sản phẩm
   
    public $is_reviewed;     // Để lưu trạng thái đánh giá


    public Order $order;

    public Product $product;

    public static function tables(){
    return
    [
        "id int primary key auto_increment",
        "product_name varchar(255) not null",
        "product_slug varchar(255)",
        "product_description longtext",
        "quantity bigint not null",
        "product_price DECIMAL(10,2) not null",
        "product_total_price DECIMAL(10,2) not null",
            
            
        "order_id int",
        "CONSTRAINT FK_OrderItem_Order FOREIGN KEY (order_id) REFERENCES orders(id)",
            
        "product_id int",
        "CONSTRAINT FK_OrderItem_Product FOREIGN KEY (product_id) REFERENCES products(id)"
    ];
        
    }
}