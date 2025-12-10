<?php
 require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Model.php';
class Address extends Model
{
  public $id, $address, $phone, $city, $ward, $is_default;
  
  
  public static  function tables()
  {
    return 
    [
        "id int primary key auto_increment",
        "address varchar(255) not null",
        "phone varchar(20) not null",
        "city varchar(100) not null",
        "ward varchar(100) not null"

    ];  

    }
}
