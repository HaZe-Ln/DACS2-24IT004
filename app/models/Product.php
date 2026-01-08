<?php
require_once "Model.php";
require_once "Branch.php";
require_once "ProductCategory.php";
require_once "ProductImage.php";
class Product extends Model
{
  public $id;
  public $name;
  public $slug;
  public $description;
  public $price_current;
  public $price_original;
  public $discount_percent;
  public $quantity;
  public $deleted_at;

  public $product_category_id;
  public $branch_id;

  public Branch $branch;
  public ProductCategory $productCategory;
  
  /** @var ProductImage[] */
  public array $productImages;

  public static function tables()
  {
    return [
      "id int primary key auto_increment",
      "name varchar(255) not null ",
      "slug varchar(255) not null unique",
      "description longtext",
      "price_current DECIMAL(15,2) not null",
      "price_original DECIMAL(15,2) not null",
      "discount_percent int default 0",
      "quantity bigint not null",
      "deleted_at TIMESTAMP",
      //relationships
      "product_category_id int",
      "CONSTRAINT FK_Product_ProductCategory FOREIGN KEY (product_category_id) REFERENCES productcategorys(id)",

      "branch_id int",
      "CONSTRAINT FK_Product_Branch FOREIGN KEY (branch_id) REFERENCES branchs(id)"
    ];
  }
}

