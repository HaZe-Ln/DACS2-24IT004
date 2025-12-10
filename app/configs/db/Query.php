<?php
require_once "PDO.php";
Import::models(["Model"]);
/**
 * Summary of Query
 * Có thể sử dụng các hàm có sẵn để tạo truy vấn:
 * - from: bắt buộc, truyền giá trị là tên của class mà kế thừa Model, viết thường thêm 's', ví dụ User => users
 * - select: không truyền truy vấn sẽ là SELECT *
 * - where: truyền mảng string điều kiện, ví dụ user.id = :id
 * - bindValue: truyền dữ liệu dạng map với key và value tương ứng trong các điều kiện, ví dụ [":id" => 1]
 * - joins: truyền mảng string lệnh join, ví dụ [" product p ON p.id = card.id"]
 * - groupBy: truyền tên cột để nhóm,
 * - having: truyền điêu kiện để lấy giá trị sau khi groupBy
 * - limit: lấy limit giá trị,
 * - offset: lấy giá trị từ offset
 * - toQuery: lấy chuỗi truy vấn (dùng kiểm tra chuỗi)
 * - get: lấy 1 giá trị sau khi sâu chuỗi truy vấn
 * - getAll: lấy nhiều giá trị sau khi sâu chuỗi truy vấn
 * - save: truyền instance của Model, nếu k có id => create, nếu có id => updated
 * - delete: xoá theo id
 * Để sử dụng , dùng Query::from("users")->getAll();
 */
class Query
{
  private $query;
  private function __construct(string $tableName)
  {
    $this->query = [
      "from" => $tableName,
      "select" => [],
      "conditions" => [],
      "joins" => [],
      "bindValue" => [],
      "groupBy" => null,
      "having" => null,
      "limit" => null,
      "offset" => null
    ];
  }
  public static function from(string $tableName)
  {
    return new Query($tableName);
  }
  /**
   * Summary of where
   * @param mixed $conditions
   *
   * @return self
   */
  public function where($conditions)
  {
    $this->query['conditions'] = $conditions;
    return $this;
  }
  public function bindValue($bindValue)
  {
    $this->query['bindValue'] = $bindValue;
    return $this;
  }
  /**
   * Summary of select
   * @param mixed $fields ["usermodel.id", "usermodel.username"]
   * @return self
   */
  public function select($fields = [])
  {
    $this->query["select"] = $fields;
    return $this;
  }
  /**
   * Summary of joins
   * @param mixed $joins ["product ON product.id = card.id "]
   * @return self
   */
  public function joins($joins = [])
  {
    $this->query['joins'] = $joins;
    return $this;
  }
  public function groupBy($by)
  {
    $this->query['groupBy'] = $by;
    return $this;
  }
  public function having($condition)
  {
    $this->query['having'] = $condition;
    return $this;
  }
  public function limit(int $val)
  {
    $this->query['limit'] = $val;
    return $this;
  }
  public function offset(int $val)
  {
    $this->query['offset'] = $val;
    return $this;
  }
  public function toQuery($query = null)
  {

    $queryFinal = "SELECT ";
    if (count($this->query["select"]) > 0) {
      $queryFinal .= implode(", ", $this->query["select"]) . " ";
    } else {
      $queryFinal .= " * ";
    }
    $queryFinal .= " FROM " . $this->query["from"] . " ";

    if ($query != null) {
      return $queryFinal . " " . $query;
    }

    foreach ($this->query["joins"] as $join) {
      $queryFinal .= " JOIN {$join} ";
    }
    if (count($this->query["conditions"]) > 0) {
      $queryFinal .= " WHERE ";
      $count = 0;
      foreach ($this->query["conditions"] as $cond) {
        if ($count > 0) {
          $queryFinal .= " AND ";
        }
        $queryFinal .= " ( {$cond} ) ";
        $count += 1;
      }
    }
    if ($this->query['groupBy'] != null) {
      $queryFinal .= " GROUP BY " . $this->query["groupBy"];
    }
    if ($this->query['having'] != null) {
      $queryFinal .= " HAVING " . $this->query["having"];
    }
    if ($this->query['limit'] != null) {
      $queryFinal .= " LIMIT " . $this->query["limit"];
    }
    if ($this->query['offset'] != null) {
      $queryFinal .= " OFFSET " . $this->query["offset"];
    }
    return $queryFinal;
  }
  private function exec($is_fetchAll = false, $full_query = null)
  {
    $query = $full_query == null ? $this->toQuery() : $full_query;

    $pdo = PDODatabase::getInstance()->getConnection();
    $stmt = $pdo->prepare($query);
    foreach ($this->query["bindValue"] as $param => $val) {
      $stmt->bindValue($param, value: $val);
    }
    $stmt->execute();
    return $is_fetchAll ?
      $stmt->fetchAll(PDO::FETCH_ASSOC) :
      $stmt->fetch(PDO::FETCH_ASSOC);
  }
  public function get($full_query = null)
  {
    return $this->exec(false, $full_query);
  }
  public function getAll($full_query = null)
  {
    return $this->exec(true, $full_query);
  }
  public function save(Model $model)
  {
    $pdo = PDODatabase::getInstance()->getConnection();
    $data = $model->toArray();

    // Kiểm tra id để quyết định insert hay update
    if (isset($data['id']) && $data['id']) {
      // UPDATE
      $id = $data['id'];
      unset($data['id']); // xoá id khỏi $data, id không update trực tiếp

      $set = [];
      foreach ($data as $key => $value) {
        $set[] = "$key = :$key";
      }
      $setStr = implode(", ", $set);

      $sql = "UPDATE {$this->query['from']} SET $setStr WHERE id = :id";
      $stmt = $pdo->prepare($sql);
      // Bind giá trị
      foreach ($data as $key => $value) {
        $stmt->bindValue(":$key", $value);
      }
      $stmt->bindValue(":id", $id, PDO::PARAM_INT);
    } else {
      // INSERT
      unset($data['id']); // id tự tăng
      $columns = implode(", ", array_keys($data));
      $placeholders = ":" . implode(", :", array_keys($data));
      $sql = "INSERT INTO {$this->query['from']} ($columns) VALUES ($placeholders)";
      $stmt = $pdo->prepare($sql);

      // Bind giá trị
      foreach ($data as $key => $value) {
        $stmt->bindValue(":$key", $value);
      }
    }

    $stmt->execute();
    if (!isset($id)) {
      return $pdo->lastInsertId();
    }
    return true;
  }
  public function delete(int $id)
  {
    $pdo = PDODatabase::getInstance()->getConnection();
    $sql = "DELETE FROM {$this->query['from']} WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
  }
}
