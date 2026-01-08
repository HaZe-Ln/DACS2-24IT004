<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["ProductValuationRepository"]);

class AdminProductValuationController
{
    public function index()
    {
        $page = max(1, (int)($_GET["page"] ?? 1));
        $limit = 10;
        $search = trim($_GET["q"] ?? "");

        $valuations = ProductValuationRepository::paginateAdmin($page, $limit, $search);
        $totalRecords = ProductValuationRepository::countAdmin($search);
        $totalPages = ceil($totalRecords / $limit);

        return [
            'valuations'   => $valuations,
            'page'         => $page,
            'totalPages'   => $totalPages,
            'search'       => $search
        ];
    }

    public function delete()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $id = $_POST["id"] ?? null;
            if ($id) {
                ProductValuationRepository::delete($id);
            }
            // Quay lại trang quản lý
            header("Location: /app/views/pages/admin/ProductValuationManagement.php");
            exit;
        }
    }
}

// Routing
if (isset($_GET['action'])) {
    $controller = new AdminProductValuationController();
    if ($_GET['action'] === 'delete') {
        $controller->delete();
    }
}