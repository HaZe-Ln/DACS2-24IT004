<?php
Import::models(['User', 'Address']);
Import::configs(["db/Query"]);

class UserRepository
{
  // ... (Các hàm save, findByEmail, findById GIỮ NGUYÊN như cũ) ...
  public static function save(User $user) 
  { 
    
    Query::from("users")->save($user); 
  }
  public static function update(User $user)
  {
      // Dùng hàm save của Query Builder (nó sẽ tự Update vì user này đã có ID)
      Query::from("users")->save($user);
  }

  public static function findByEmail(string $email) {
    $row = Query::from("users")->where(["email = :email"])->bindValue([":email" => $email])->limit(1)->get();
    if (!$row) return null; $user = new User(); $user->fill($row); return $user;
  }

  public static function findById(int $id) {
    $row = Query::from("users")->where(["id = :id"])->bindValue([":id" => $id])->limit(1)->get();
    if (!$row) return null; $user = new User(); $user->fill($row); unset($user->password); return $user;
  }

  // --- HÀM LẤY ĐỊA CHỈ  ---
  public static function getAddressesByUserId($userId)
  {
    $rows = Query::from("addresss a") 
      ->select([
          "a.*"  // [ĐÃ SỬA] Chỉ lấy thông tin bảng address, bỏ ua.is_default
      ])
      ->joins([
          "useraddresss ua ON a.id = ua.address_id" 
      ])
      ->where(["ua.user_id = :uid"])
      ->bindValue([":uid" => $userId])
      ->getAll();

    $addresses = [];
    foreach ($rows as $row) {
      $addr = new Address();
      $addr->fill($row);
      // [ĐÃ XÓA] Dòng gán is_default ở đây
      $addresses[] = $addr;
    }

    return $addresses;
  }
  public static function addAddress($userId, $data)
    {
        // 1. Insert vào bảng 'addresss' trước
        $addr = new Address();
        $addr->address = $data['address'];
        $addr->phone = $data['phone'];
        $addr->city = $data['city'];
        $addr->ward = $data['ward'];
        
        // Save vào bảng addresss -> Trả về ID vừa tạo
        $addressId = Query::from("addresss")->save($addr);

        // 2. Insert vào bảng trung gian 'useraddresss'
        // Vì class UserAddress của bạn chưa có logic save chuẩn, ta dùng Query raw cho nhanh
        $sql = "INSERT INTO useraddresss (user_id, address_id) VALUES (:uid, :aid)";
        $pdo = PDODatabase::getInstance()->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':uid', $userId);
        $stmt->bindValue(':aid', $addressId);
        $stmt->execute();
    }

    /**
     * Xóa địa chỉ
     */
    public static function deleteAddress($userId, $addressId)
    {
        // 1. Kiểm tra xem địa chỉ này có đúng là của user này không (Bảo mật)
        $check = Query::from("useraddresss")
            ->where(["user_id = :uid", "address_id = :aid"])
            ->bindValue([":uid" => $userId, ":aid" => $addressId])
            ->limit(1)
            ->get();

        if ($check) {
            // 2. Xóa trong bảng trung gian
            $pdo = PDODatabase::getInstance()->getConnection();
            $stmt = $pdo->prepare("DELETE FROM useraddresss WHERE user_id = :uid AND address_id = :aid");
            $stmt->bindValue(':uid', $userId);
            $stmt->bindValue(':aid', $addressId);
            $stmt->execute();

            // 3. (Tuỳ chọn) Xóa luôn trong bảng addresss gốc để đỡ rác DB
             $stmt2 = $pdo->prepare("DELETE FROM addresss WHERE id = :aid");
             $stmt2->bindValue(':aid', $addressId);
             $stmt2->execute();
        }
    }
    public static function paginate($page, $limit, $search = "", $role = "all")
    {
        $pdo = PDODatabase::getInstance()->getConnection();
        $offset = ($page - 1) * $limit;

        // Build WHERE clause
        $where = "1=1";
        $params = [];

        // 1. Tìm kiếm (Tên, Email, SĐT)
        if (!empty($search)) {
            $where .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search)";
            $params[':search'] = "%$search%";
        }

        // 2. Lọc theo Role
        if ($role !== 'all') {
            $where .= " AND role = :role";
            $params[':role'] = $role;
        }

        $sql = "SELECT * FROM users 
                WHERE $where 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $users = [];
        foreach ($rows as $row) {
            $u = new User();
            $u->fill($row);
            $users[] = $u;
        }
        return $users;
    }

    /**
     * ĐẾM TỔNG SỐ USER (Để tính số trang)
     */
    public static function count($search = "", $role = "all")
    {
        $pdo = PDODatabase::getInstance()->getConnection();

        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            $where .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if ($role !== 'all') {
            $where .= " AND role = :role";
            $params[':role'] = $role;
        }

        $sql = "SELECT COUNT(*) as total FROM users WHERE $where";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * XÓA USER (Cẩn thận: Cần xóa dữ liệu liên quan trước)
     */
    public static function delete($userId)
    {
        try {
            $pdo = PDODatabase::getInstance()->getConnection();

            // 1. Xóa Address liên quan
            $pdo->prepare("DELETE FROM useraddresss WHERE user_id = :uid")->execute([':uid' => $userId]);
            
            // 2. Xóa Cart
            $pdo->prepare("DELETE FROM cartitems WHERE user_id = :uid")->execute([':uid' => $userId]);

            // 3. Xóa User (Nếu có ràng buộc khóa ngoại với Order, DB có thể chặn)
            // Tốt nhất nên Soft Delete (đánh dấu deleted_at), nhưng ở đây ta Hard Delete theo yêu cầu
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :uid");
            return $stmt->execute([':uid' => $userId]);
        } catch (Exception $e) {
            error_log("Delete User Error: " . $e->getMessage());
            return false;
        }
    }
}