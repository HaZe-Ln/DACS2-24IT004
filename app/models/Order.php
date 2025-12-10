<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Address.php';

class Order extends Model
{
 public $id, $payment_method, $status_payment, $status_order;
 
 public User $user;

 public Address $address;

 public $total_amount;

 public static function tables()
 {
   return [
    "id int primary key auto_increment",
    "payment_method varchar(50) not null",
    "status_payment ENUM('unpaid', 'paid') DEFAULT 'unpaid'",
    "status_order ENUM('confirmed', 'shipping', 'completed') DEFAULT 'confirmed'",

    //relationship
    "user_id int",
    "CONSTRAINT FK_Order_User FOREIGN KEY (user_id) REFERENCES users(id)",
            
    "address_id int",
    "CONSTRAINT FK_Order_Address FOREIGN KEY (address_id) REFERENCES addresss(id)"
   ]; 
 }
}