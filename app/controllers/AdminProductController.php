<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::repositories(["ProductRepository", "ProductCategoryRepository", "BranchRepository"]);
Import::middlewares(["Authentication"]);

class AdminProductController
{
    /**
     * 1. QUẢN LÝ DANH SÁCH (Dùng cho ProductManagement.php)
     */
    public function index()
    {
        $this->checkAdmin();

        // Xử lý Xóa (POST)
        if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete") {
            $deleteId = $_POST["id"] ?? null;
            if ($deleteId) {
                // Gọi Repository để xóa (Repository cần có hàm delete)
                // Nếu Repository chưa có hàm delete, bạn có thể dùng Query::from('products')->delete($id) tạm thời
                Query::from("products")->delete((int)$deleteId);
            }
            header("Location: /app/views/pages/admin/ProductManagement.php");
            exit;
        }

        // Lấy tham số Lọc & Phân trang (GET)
        $page = max(1, (int)($_GET["page"] ?? 1));
        $limit = 5;
        
        $filters = [
            'search'           => trim($_GET["q"] ?? ""),
            'product_category' => $_GET["category_id"] ?? "",
            'branch'           => $_GET["branch_id"] ?? "",
            'price_min'        => $_GET["price_min"] ?? "",
            'price_max'        => $_GET["price_max"] ?? "",
            'status'           => $_GET["status"] ?? "all"
        ];

        // Gọi Repository
        $items = ProductRepository::paginate($page, $limit, $filters);
        $total = ProductRepository::count($filters);
        $totalPages = ceil($total / $limit);

        // Data cho Dropdown
        $categories = ProductCategoryRepository::all(null, 1, 100);
        $branches = BranchRepository::all(null, 1, 100);

        return [
            'items' => $items,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'filters' => $filters,
            'categories' => $categories,
            'branches' => $branches
        ];
    }

    /**
     * 2. TRANG THÊM MỚI (Dùng cho CreateProduct.php)
     */
    public function create()
    {
        $this->checkAdmin();

        // Xử lý POST (Lưu)
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            try {
                $payload = $this->getFormData();
                
                // Upload ảnh (Tên ảnh theo slug sản phẩm)
                $productNameSlug = $this->slugify($payload['name']);
                $payload['images'] = $this->handleUploadImages($productNameSlug);

                ProductRepository::create($payload);
                
                header("Location: /app/views/pages/admin/ProductManagement.php");
                exit;
            } catch (Exception $e) {
                return [
                    'error' => $e->getMessage(),
                    'categories' => ProductCategoryRepository::all(null, 1, 200),
                    'branches' => BranchRepository::all(null, 1, 200)
                ];
            }
        }

        // Xử lý GET (Hiển thị)
        return [
            'categories' => ProductCategoryRepository::all(null, 1, 200),
            'branches' => BranchRepository::all(null, 1, 200)
        ];
    }

    /**
     * 3. TRANG CHỈNH SỬA (Dùng cho EditProduct.php)
     */
    public function edit()
    {
        $this->checkAdmin();
        $id = $_GET['id'] ?? null;
        if (!$id) { header("Location: /app/views/pages/admin/ProductManagement.php"); exit; }

        $product = ProductRepository::getById($id);

        // Xử lý POST (Cập nhật)
        if ($_SERVER["REQUEST_METHOD"] === "POST" && $product) {
            try {
                $payload = $this->getFormData();
                
                // 1. Upload ảnh mới
                $productNameSlug = $this->slugify($payload['name']);
                $newImages = $this->handleUploadImages($productNameSlug);
                
                // 2. Lọc ảnh cũ giữ lại (Trừ ảnh bị xóa)
                $removeImages = $_POST['remove_images'] ?? [];
                // Lưu ý: Đảm bảo Repository trả về productImages là mảng object
                $currentImageUrls = array_map(fn($img) => $img->url, $product->productImages ?? []);
                $keepImages = array_diff($currentImageUrls, $removeImages);
                
                // 3. Gộp ảnh
                $payload['images'] = array_merge($keepImages, $newImages);

                ProductRepository::update($id, $payload);
                
                header("Location: /app/views/pages/admin/ProductManagement.php");
                exit;
            } catch (Exception $e) {
                return [
                    'error' => $e->getMessage(),
                    'product' => $product,
                    'categories' => ProductCategoryRepository::all(null, 1, 200),
                    'branches' => BranchRepository::all(null, 1, 200)
                ];
            }
        }

        return [
            'product' => $product,
            'categories' => ProductCategoryRepository::all(null, 1, 200),
            'branches' => BranchRepository::all(null, 1, 200)
        ];
    }

    // --- CÁC HÀM HELPER PRIVATE ---

    private function checkAdmin() {
        $user = Authentication::getAuthentication();
        if (!$user || $user->role !== 'admin') {
            header("Location: /app/views/pages/auth/SignIn.php"); exit;
        }
    }

    private function getFormData() {
        return [
            "name" => $_POST["name"] ?? "",
            "description" => $_POST["description"] ?? "",
            "price_original" => $_POST["price_original"] ?? 0,
            "discount_percent" => $_POST["discount_percent"] ?? 0,
            "quantity" => $_POST["quantity"] ?? 0,
            "product_category_id" => $_POST["product_category_id"] ?? null,
            "branch_id" => $_POST["branch_id"] ?? null,
        ];
    }

    // Hàm upload ảnh chuẩn hóa tên file
    private function handleUploadImages($slugName) {
        if (empty($_FILES["images"]) || !is_array($_FILES["images"]["name"])) {
            return [];
        }
        
        $urls = [];
        $files = $_FILES["images"];
        $uploadDir = $_SERVER["DOCUMENT_ROOT"] . "/uploads/products";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
        
        $allowed = ["image/jpeg", "image/png", "image/webp", "image/gif"];
        
        foreach ($files["name"] as $idx => $name) {
            if ($files["error"][$idx] !== UPLOAD_ERR_OK) continue;
            if (!in_array(mime_content_type($files["tmp_name"][$idx]), $allowed)) continue;
            
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            // Tên file: slug-ngaygio-index.ext
            $safeName = $slugName . '-' . time() . '-' . $idx . '.' . strtolower($ext);
            
            if (move_uploaded_file($files["tmp_name"][$idx], $uploadDir . "/" . $safeName)) {
                $urls[] = "/uploads/products/" . $safeName;
            }
        }
        return $urls;
    }
    

    private function slugify($text) {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('~[^\\pL\\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        $text = preg_replace('~[^-a-z0-9]+~', '', $text);
        return $text ?: 'n-a';
    }
}