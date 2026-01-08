<?php
Import::repositories(["ProductRepository","ProductValuationRepository"]);
class ProductController
{
    public function getAllProduct()
    {
        return ProductRepository::find();
    }
    public function index()
    {
        // 1. Xử lý Logic Lọc & Phân trang (Input từ người dùng)
        $page = $_GET['page'] ?? 1;
        $filters = [
            'branch'           => $_GET['branch'] ?? null, // Chú ý: có thể là mảng nếu chọn nhiều
            'product_category' => $_GET['product_category'] ?? [],
            'price_min'        => $_GET['price_min'] ?? 0,
            'price_max'        => $_GET['price_max'] ?? 50000000,
            'sort'             => $_GET['sort'] ?? 'popular'
        ];
        
        // 2. Lấy danh sách Sản phẩm (Kết quả sau khi lọc)
        $limit = 4;
        $products = ProductRepository::paginate($page, $limit, $filters);
        $totalRecords = ProductRepository::count($filters);
        $totalPages = ceil($totalRecords / $limit);

        // 3. Lấy dữ liệu cho Sidebar (Luôn lấy tất cả để hiển thị bộ lọc)
        $productCategories = ProductRepository::getAllProductCategories();
        $branches   = ProductRepository::getAllBranches();

        // 4. Trả về View
        return [
            'products'          => $products,
            'productCategories' => $productCategories, // Truyền qua view
            'branches'          => $branches,   // Truyền qua view
            'totalPages'        => $totalPages,
            'currentPage'       => $page,
            'filters'           => $filters,     // Để view biết cái nào đang được tích
            'totalRecords'      => $totalRecords
        ];
    }
    public function getDetail($id)
    {
        if (!$id) return null;
        return ProductRepository::getById($id);
    }
    
    // Hàm lấy sản phẩm liên quan (cùng danh mục, trừ sản phẩm hiện tại)
    public function getRelatedProducts($categoryId, $currentProductId)
    {
        // Tận dụng hàm paginate đã viết để lọc theo category
        // Lấy 4 sản phẩm
        $filters = ['product_category' => $categoryId]; 
        $products = ProductRepository::paginate(1, 4, $filters);
        
        // Lọc bỏ sản phẩm hiện tại (nếu lỡ trùng)
        $results = [];
        foreach($products as $p){
            if($p->id != $currentProductId) {
                $results[] = $p;
            }
        }
        return $results;
    }
    // [MỚI] Lấy danh sách đánh giá
    public function getProductReviews($productId)
    {
        // Import Repository nếu chưa có ở đầu file (nhưng file cũ của bạn đã import rồi thì thôi)
        // Import::repositories(["ProductValuationRepository"]); 
        
        if (class_exists('ProductValuationRepository')) {
            return ProductValuationRepository::getByProductId($productId);
        }
        return [];
    }
    public function createProduct($data)
    {
        // Có thể validate dữ liệu ở đây nếu cần
        return ProductRepository::create($data);
    }

    // [MỚI] Hàm Update mà EditProduct.php đang gọi
    public function updateProduct($id, $data)
    {
        return ProductRepository::update($id, $data);
    }
}

