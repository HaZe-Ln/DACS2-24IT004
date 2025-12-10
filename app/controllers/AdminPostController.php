<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["PostRepository"]);
Import::middlewares(["Authentication"]);

class AdminPostController
{
    public function index()
    {
        // 1. Check quyền Admin
        $currentUser = Authentication::getAuthentication();
        if (!$currentUser || $currentUser->role !== 'admin') {
            header("Location: /app/views/pages/auth/SignIn.php"); 
            exit;
        }

        // 2. Xử lý Action (Xóa bài viết)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
            $deleteId = (int)$_POST['id'];
            if ($deleteId > 0) {
                PostRepository::delete($deleteId);
            }
            
            // Redirect để refresh trang
            $qs = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
            header("Location: /app/views/pages/admin/PostManagement.php" . $qs);
            exit;
        }

        // 3. Lấy tham số Filter
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $search = trim($_GET['q'] ?? '');
        $visibility = $_GET['visibility'] ?? 'all'; // public, private, all

        // 4. Query dữ liệu
        $posts = PostRepository::paginateAdmin($page, $limit, $search, $visibility);
        $totalRecords = PostRepository::countAdmin($search, $visibility);
        $totalPages = ceil($totalRecords / $limit);

        return [
            'posts' => $posts,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'visibility' => $visibility
        ];
    }
}