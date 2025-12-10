<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/helpers/Import.php";
Import::repositories(["ProductCategoryRepository"]);
Import::helpers(["Request"]);

class ProductCategoryController
{
  public static function index()
  {
    $page  = max(1, (int)($_GET["page"] ?? 1));
    $limit = max(1, (int)($_GET["limit"] ?? 10));
    $search = trim($_GET["q"] ?? "") ?: null;

    $categories = ProductCategoryRepository::all($search, $page, $limit);
    $total = ProductCategoryRepository::countAll($search);

    // các biến này sẽ được view sử dụng
    $items = $categories;
    require $_SERVER["DOCUMENT_ROOT"] . "/app/views/pages/admin/CategoryManagement.php";
  }

  public static function create()
  {
    $error = null;
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $name = trim($_POST["name"] ?? "");
      if ($name === "") {
        $error = "Tên danh mục không được để trống.";
      } else {
        ProductCategoryRepository::create(["name" => $name]);
        header("Location: /app/views/pages/admin/CategoryManagement.php");
        exit;
      }
    }
    require $_SERVER["DOCUMENT_ROOT"] . "/app/views/pages/admin/CreateCategory.php";
  }

  public static function edit()
  {
    $id = $_GET["id"] ?? null;
    $category = $id ? ProductCategoryRepository::findById($id) : null;
    $error = null;

    if ($_SERVER["REQUEST_METHOD"] === "POST" && $category) {
      $name = trim($_POST["name"] ?? "");
      if ($name === "") {
        $error = "Tên danh mục không được để trống.";
      } else {
        ProductCategoryRepository::update($id, ["name" => $name]);
        header("Location: /app/views/pages/admin/CategoryManagement.php");
        exit;
      }
    }
    require $_SERVER["DOCUMENT_ROOT"] . "/app/views/pages/admin/EditCategory.php";
  }

  public static function delete()
  {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $id = $_POST["id"] ?? null;
      if ($id) {
        ProductCategoryRepository::delete((int)$id);
      }
    }
    header("Location: /app/views/pages/admin/CategoryManagement.php");
    exit;
  }
}
