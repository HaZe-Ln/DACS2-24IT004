<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::controllers(["ProductController"]);
Import::helpers(["API"]);
class ProductApi extends API
{
  private ProductController $productController;
  public function __construct(ProductController $productController)
  {
    parent::__construct();
    $this->productController = $productController;
  }
  public function GET(string $feature)
  {
    switch ($feature) {
      case "getProducts": {
          $products = $this->productController->getAllProduct();
          $this->response($products);
          break;
        }
      default: {
          $this->featureNotFound();
        }
    }
  }
  public function POST(string $feature) {}
}

$productController = new ProductController();
$productApi = new ProductApi($productController);
$productApi->listen();

