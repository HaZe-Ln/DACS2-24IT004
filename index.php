<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once "./app/helpers/Import.php";
Import::configs(["db/PDO", "db/Query"]);
$models = ['User',"Address", "ProductCategory", "Branch", "Product","UserAddress","ProductImage","CartItem","Order","OrderItem","ProductValuation","Post"];
Import::models($models);

/** *
 * @param PDO $pdo
 * @param array $modelClasses Danh sách tên class model
 */
function createTables(PDO $pdo, array $modelClasses)
{
  foreach ($modelClasses as $modelClass) {
    if (!class_exists($modelClass)) {
      throw new Exception("Model $modelClass không tồn tại.");
    }

    if (!method_exists($modelClass, "tables")) {
      throw new Exception("Model $modelClass chưa định nghĩa phương thức tables().");
    }
    $columns = $modelClass::tables();
    array_push(
      $columns,
      "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
      "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    );
    $tableName = strtolower($modelClass); // tên bảng = tên class viết thường
    $sql = "CREATE TABLE IF NOT EXISTS `$tableName" . "s" . "` (" . implode(", ", $columns) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql);
  }
}

$pdo = PDODatabase::getInstance();
createTables(pdo: $pdo->getConnection(), modelClasses: $models);

// $productsTest = [
//     new Product(),
//     new Product(),
//     new Product(),
//     new Product()
// ];
// foreach ($productsTest as $product) {
//     $product->fill([
//         "name" => "Lorem",
//         "slug" => "lorem",
//         "description" => "Lorem ipsum dolor sit amet consectetur adipisicing elit. Blanditiis alias rerum consequuntur minus exercitationem at ab suscipit, corporis cum, nisi neque laborum odio itaque sunt architecto ipsum harum facere quo!",
//         "price" => 10.1
//     ]);
//     Query::from("products")->save($product);
// }
// $user = new User();
// $user->fill([
//     "username" => "dung",
//     "email" => "dung@gmail.com",
//     "password" => "dung"
// ]);
// Query::from('users')->save($user );

header("Location: /app/views/pages/Home.php");
exit;
