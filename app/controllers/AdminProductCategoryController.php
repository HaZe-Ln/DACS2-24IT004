<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["ProductCategoryRepository"]);
Import::middlewares(["Authentication"]);

class AdminProductCategoryController
{
    public function index()
    {
        // 1. Check quyền Admin
        $currentUser = Authentication::getAuthentication();
        if (!$currentUser || $currentUser->role !== 'admin') {
            header("Location: /app/views/pages/auth/SignIn.php"); 
            exit;
        }

        // 2. Xử lý Action (Xóa Danh mục)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
            $deleteId = (int)$_POST['id'];
            if ($deleteId > 0) {
                ProductCategoryRepository::delete($deleteId);
            }
            
            // Redirect để refresh trang tránh gửi lại form
            $qs = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
            header("Location: /app/views/pages/admin/CategoryManagement.php" . $qs);
            exit;
        }

        // 3. Lấy tham số Filter
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(1, (int)($_GET['limit'] ?? 10));
        $search = trim($_GET['q'] ?? '');

        // 4. Query dữ liệu (Gọi hàm mới có Count)
        $items = ProductCategoryRepository::getAllWithCount($search, $page, $limit);
        $total = ProductCategoryRepository::countAll($search);
        $totalPages = max(1, (int)ceil($total / $limit));

        // 5. Trả dữ liệu về View
        return [
            'items' => $items,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'total' => $total,
            'limit' => $limit
        ];
    }
}