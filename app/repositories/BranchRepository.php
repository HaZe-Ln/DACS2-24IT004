<?php
Import::configs(["db/Query"]);
Import::models(["Branch"]);

class BranchRepository
{
  /**
   * Lấy danh sách thương hiệu (branch) có tìm kiếm + phân trang.
   * @return Branch[]
   */
  public static function all(?string $search = null, int $page = 1, int $limit = 10): array
  {
    $offset = ($page - 1) * $limit;
    $where = ["deleted_at IS NULL"];
    $bind = [];
    if ($search) {
      $where[] = "name LIKE :search";
      $bind[":search"] = "%" . $search . "%";
    }

    $rows = Query::from("branchs")
      ->select(["*"])
      ->where($where)
      ->bindValue($bind)
      ->limit($limit)
      ->offset($offset)
      ->getAll();

    $items = [];
    foreach ($rows as $row) {
      $b = new Branch();
      $b->fill($row);
      $items[] = $b;
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
    $row = Query::from("branchs")
      ->select(["COUNT(*) AS total"])
      ->where($where)
      ->bindValue($bind)
      ->get();
    return (int)($row["total"] ?? 0);
  }

  public static function findById($id)
  {
    if (!$id) return null;
    $row = Query::from("branchs")
      ->where(["id = :id", "deleted_at IS NULL"])
      ->bindValue([":id" => $id])
      ->get();
    if (!$row) return null;
    $b = new Branch();
    $b->fill($row);
    return $b;
  }

  public static function create(array $data)
  {
    $model = new Branch();
    $model->name = $data["name"] ?? null;
    $model->slug = $data["slug"] ?? self::slugify($model->name);
    $model->address = $data["address"] ?? '';
    $model->description = $data["description"] ?? null;
    $id = Query::from("branchs")->save($model);
    return $id;
  }

  public static function update($id, array $data)
  {
    $model = new Branch();
    $model->id = $id;
    $model->name = $data["name"] ?? null;
    $model->slug = $data["slug"] ?? self::slugify($model->name);
    $model->address = $data["address"] ?? '';
    $model->description = $data["description"] ?? null;
    return Query::from("branchs")->save($model);
  }

  public static function delete($id)
  {
    return Query::from("branchs")->delete((int)$id);
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
}
