<?php
Import::configs(["db/Query"]);
Import::models(["Post"]);

class PostRepository
{
    /**
     * Lấy danh sách bài viết (Có phân trang)
     */
    public static function paginate($page = 1, $limit = 6)
    {
        // Ép kiểu an toàn để tránh lỗi SQL Injection hoặc lỗi logic
        $page = (int)$page;
        $limit = (int)$limit;
        if ($page < 1) $page = 1;
        
        $offset = ($page - 1) * $limit;

        // Lấy bài public và chưa bị xóa
        $query = Query::from("posts")
            ->where([
                "visibility = 'public'", 
                "deleted_at IS NULL"
            ])
            ->limit($limit)
            ->offset($offset)
            ->toQuery("ORDER BY id DESC"); // Bài mới nhất lên đầu

        $rows = Query::from("posts")->getAll($query);

        $posts = [];
        foreach ($rows as $row) {
            $post = new Post();
            $post->fill($row);
            $posts[] = $post;
        }

        return $posts;
    }

    /**
     * Đếm tổng số bài viết (Để tính số trang)
     */
    public static function count()
    {
        $result = Query::from("posts")
            ->select(["COUNT(*) as total"])
            ->where([
                "visibility = 'public'", 
                "deleted_at IS NULL"
            ])
            ->get();

        return $result ? $result['total'] : 0;
    }

    /**
     * [ĐÃ SỬA] Lấy chi tiết 1 bài viết theo ID
     */
    public static function getById($id)
    {
        // Query theo ID
        $rows = Query::from("posts")
            ->where([
                "id = :id",
                "visibility = 'public'", 
                "deleted_at IS NULL"
            ])
            ->bindValue([":id" => $id])
            ->getAll();

        if (empty($rows)) return null;

        $post = new Post();
        $post->fill($rows[0]);
        return $post;
    }

    /**
     * Lấy bài viết liên quan (Trừ bài hiện tại)
     */
    public static function getRelated($currentId, $limit = 3)
    {
        $query = Query::from("posts")
            ->where([
                "id != :id", // Trừ bài đang xem ra
                "visibility = 'public'", 
                "deleted_at IS NULL"
            ])
            ->bindValue([":id" => $currentId])
            ->limit($limit)
            ->toQuery("ORDER BY id DESC"); // Lấy ngẫu nhiên hoặc bài mới nhất

        $rows = Query::from("posts")->getAll($query);

        $posts = [];
        foreach ($rows as $row) {
            $post = new Post();
            $post->fill($row);
            $posts[] = $post;
        }
        return $posts;
    }
    public static function getLatest($limit = 3)
    {
        // Query lấy bài viết, sắp xếp ID giảm dần (bài mới nhất)
        $query = Query::from("posts")
            ->where([
                "visibility = 'public'", 
                "deleted_at IS NULL"
            ])
            ->limit($limit)
            ->toQuery("ORDER BY id DESC"); // Sắp xếp mới nhất

        $rows = Query::from("posts")->getAll($query);

        $posts = [];
        foreach ($rows as $row) {
            $post = new Post();
            $post->fill($row);
            $posts[] = $post;
        }
        return $posts;
    }
    /**
     * [ADMIN] Phân trang, Tìm kiếm và Lọc
     */
    public static function paginateAdmin($page, $limit, $search = "", $visibility = "all")
    {
        $pdo = PDODatabase::getInstance()->getConnection();
        $offset = ($page - 1) * $limit;

        $where = "deleted_at IS NULL"; // Mặc định không lấy bài đã xóa mềm (nếu có logic xóa mềm)
        $params = [];

        // 1. Tìm kiếm theo tên bài viết
        if (!empty($search)) {
            $where .= " AND name LIKE :search";
            $params[':search'] = "%$search%";
        }

        // 2. Lọc theo trạng thái (public/private)
        if ($visibility !== 'all') {
            $where .= " AND visibility = :vis";
            $params[':vis'] = $visibility;
        }

        $sql = "SELECT * FROM posts 
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
        
        $posts = [];
        foreach ($rows as $row) {
            $post = new Post();
            $post->fill($row);
            $posts[] = $post;
        }
        return $posts;
    }

    /**
     * [ADMIN] Đếm tổng số bài viết
     */
    public static function countAdmin($search = "", $visibility = "all")
    {
        $pdo = PDODatabase::getInstance()->getConnection();

        $where = "deleted_at IS NULL";
        $params = [];

        if (!empty($search)) {
            $where .= " AND name LIKE :search";
            $params[':search'] = "%$search%";
        }

        if ($visibility !== 'all') {
            $where .= " AND visibility = :vis";
            $params[':vis'] = $visibility;
        }

        $sql = "SELECT COUNT(*) as total FROM posts WHERE $where";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * [ADMIN] Xóa bài viết
     */
    public static function delete($id)
    {
        // Có thể dùng Soft Delete (update deleted_at) hoặc Hard Delete (DELETE FROM)
        // Ở đây mình dùng Hard Delete cho đơn giản
       return Query::from("posts")->delete($id);
    }

}