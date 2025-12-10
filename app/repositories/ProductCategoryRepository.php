<?php
Import::configs(["db/Query"]);
Import::models(["ProductCategory"]);

class ProductCategoryRepository
{
  /**
   * Lấy danh sách danh mục (lọc theo tên nếu có) có phân trang.
   * @return ProductCategory[]
   */
  public static function all(?string $search = null, int $page = 1, int $limit = 10): array
  {
    $offset = ($page - 1) * $limit;
    $where = ["pc.deleted_at IS NULL"];
    $bind = [];
    if ($search) {
      $where[] = "pc.name LIKE :search";
      $bind[":search"] = "%" . $search . "%";
    }

    $rows = Query::from("productcategorys pc")
      ->select(["pc.*"])
      ->where($where)
      ->bindValue($bind)
      ->limit($limit)
      ->offset($offset)
      ->getAll();

    $items = [];
    foreach ($rows as $row) {
      $c = new ProductCategory();
      $c->fill($row);
      $items[] = $c;
    }
    return $items;
  }

  public static function countAll(?string $search = null): int
  {
    $where = ["deleted_at IS NULL"];
    $bind = [];
    if ($search) {
      $where[] = "name LIKE :search";
      $bind[":search"] = "%" . $search . "%";
    }
    $row = Query::from("productcategorys")
      ->select(["COUNT(*) AS total"])
      ->where($where)
      ->bindValue($bind)
      ->get();
    return (int)($row["total"] ?? 0);
  }

  /** @return ProductCategory|null */
  public static function findById($id)
  {
    if (!$id) return null;
    $row = Query::from("productcategorys")
      ->where(["id = :id", "deleted_at IS NULL"])
      ->bindValue([":id" => $id])
      ->get();
    if (!$row) return null;
    $c = new ProductCategory();
    $c->fill($row);
    return $c;
  }

  public static function create(array $data)
  {
    $model = new ProductCategory();
    $model->name = $data["name"] ?? null;
    $model->slug = $data["slug"] ?? self::slugify($model->name);
    return Query::from("productcategorys")->save($model);
  }

  public static function update($id, array $data)
  {
    $model = new ProductCategory();
    $model->id = $id;
    $model->name = $data["name"] ?? null;
    $model->slug = $data["slug"] ?? self::slugify($model->name);
    return Query::from("productcategorys")->save($model);
  }

  public static function delete($id)
  {
    return Query::from("productcategorys")->delete((int)$id);
  }

  private static function slugify(?string $text): ?string
  {
    if (!$text) return null;
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^\\pL\\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    $text = preg_replace('~[^-a-z0-9]+~', '', $text);
    return $text ?: null;
  }
  public static function getAllWithCount(?string $search = null, int $page = 1, int $limit = 10): array
  {
    $pdo = PDODatabase::getInstance()->getConnection();
    $offset = ($page - 1) * $limit;
    
    // 1. Viết câu SQL chuẩn
    $sql = "SELECT pc.*, COUNT(p.id) as product_count 
            FROM productcategorys pc 
            LEFT JOIN products p ON pc.id = p.product_category_id AND p.deleted_at IS NULL 
            WHERE pc.deleted_at IS NULL";

    $params = [];

    // 2. Xử lý tìm kiếm
    if ($search) {
        $sql .= " AND pc.name LIKE :search";
        $params[':search'] = "%" . $search . "%";
    }

    // 3. Group By và Phân trang
    $sql .= " GROUP BY pc.id ORDER BY pc.id DESC LIMIT :limit OFFSET :offset";

    // 4. Prepare & Bind
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    // Bind số nguyên cho Limit/Offset (Quan trọng)
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Mapping dữ liệu vào Model
    $items = [];
    foreach ($rows as $row) {
      $c = new ProductCategory();
      $c->fill($row); // Fill các cột id, name, slug...
      
      // Gán thủ công số lượng sản phẩm (hoặc dùng Attribute AllowDynamicProperties bên Model)
      $c->product_count = $row['product_count'] ?? 0; 
      
      $items[] = $c;
    }
    return $items;
  }

 
}
