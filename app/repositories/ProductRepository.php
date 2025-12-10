<?php
Import::configs(["db/Query"]);
Import::models(["Product", "Branch", "ProductCategory", "ProductImage"]);
class ProductRepository
{
  /**
   * Summary of find
   * @return Product[]
   */
  public static function find()
  {
    //Tất cả sản phẩm
    $products = [];
    //Các id của sản phẩm tìm được
    $prodIds = [];
    //Lấy tất cả sản phẩm có trong csdl
    
    $prodRows = Query::from("products p")
      ->select(["p.*", Branch::FieldJoin, ProductCategory::FieldJoin])
      ->joins([
        "branchs b on b.id = p.branch_id",
        "productcategorys pc on pc.id = p.product_category_id",
      ])
      ->getAll();
    //Ánh xạ db vào model
    foreach ($prodRows as $row) {
      $pro = new Product();
      $pro->fill($row);

      $branch = new Branch();
      $branch->fillJoin($row);

      $proCate = new ProductCategory();
      $proCate->fillJoin($row);

      $pro->branch = $branch;
      $pro->productCategory = $proCate;
      array_push($products, $pro);
      array_push($prodIds, $pro->id);
    }

    //Tất cả product image MÀ thuộc prodIds
    $productImages = [];
    //Lấy tất cả các productImages MÀ có pi.product_id nằm trong prodIds
    $prodImgRows = Query::from("")->getAll(
      Query::from("productimages  pi")
        ->toQuery("WHERE pi.product_id IN (" . implode(", ", $prodIds) . ")")
    );
    //Ánh xạ db vào model
    foreach ($prodImgRows as $row) {
      $proI = new ProductImage();
      $proI->fill($row);
      array_push($productImages, $proI);
    }

    //Đưa các image vào prod tương ứng với productImage.product_id = product.id
    foreach ($products as $pro) {
      $imgs = [];
      foreach ($productImages as $pI) {
        if ($pI->product_id == $pro->id) {
          array_push($imgs, $pI);
        }
      }
      //gán $productImages bằng  $imgs : số ảnh tìm được thoả product_id trong ảnh bằng với product.id
      $pro->productImages = $imgs;
    }
    return $products;
    }
  public static function paginate($page = 1, $limit = 12, $filters = [])
  {
    $products = [];
    $prodIds = [];

    // 1. KHỞI TẠO QUERY
    $query = Query::from("products p")
      ->select(["p.*", Branch::FieldJoin, ProductCategory::FieldJoin])
      ->joins([
        "branchs b on b.id = p.branch_id",
        "productcategorys pc on pc.id = p.product_category_id",
      ]);

    // 2. ÁP DỤNG BỘ LỌC (Tách ra hàm private hoặc viết thẳng vào đây)
    self::applyFilters($query, $filters);

    // 3. XỬ LÝ PHÂN TRANG
    $offset = ($page - 1) * $limit;
    $query->limit($limit)->offset($offset);
    
    // Xử lý Sắp xếp (Sort)
    if (isset($filters['sort'])) {
        switch ($filters['sort']) {
            case 'price_asc': $query->toQuery("ORDER BY p.price_current ASC"); break; // Lưu ý: Query builder của bạn cần hỗ trợ orderBy, nếu chưa thì dùng tạm string chèn
            case 'price_desc': $query->toQuery("ORDER BY p.price_current DESC"); break;
            default: $query->toQuery("ORDER BY p.created_at DESC"); break;
        }
    }

    // 4. CHẠY QUERY LẤY DATA CƠ BẢN
    $prodRows = $query->getAll();

    // 5. MAP DỮ LIỆU VÀO MODEL (Logic giống hệt hàm find cũ)
    foreach ($prodRows as $row) {
      $pro = new Product();
      $pro->fill($row);

      $branch = new Branch();
      $branch->fillJoin($row);

      $proCate = new ProductCategory();
      $proCate->fillJoin($row);

      $pro->branch = $branch;
      $pro->productCategory = $proCate;
      
      array_push($products, $pro);
      array_push($prodIds, $pro->id);
    }

    // 6. LẤY ẢNH (Logic cũ - Rất quan trọng để hiển thị ảnh)
    if (!empty($prodIds)) {
        // Tạo chuỗi ID an toàn hơn: implode
        $idsString = implode(", ", $prodIds);
        
        $prodImgRows = Query::from("productimages pi")
            ->where(["pi.product_id IN ($idsString)"]) // Query builder của bạn hỗ trợ string where
            ->getAll();

        $productImages = [];
        foreach ($prodImgRows as $row) {
            $proI = new ProductImage();
            $proI->fill($row);
            $productImages[] = $proI;
        }

        // Gán ảnh vào product
        foreach ($products as $pro) {
            $imgs = [];
            foreach ($productImages as $pI) {
                if ($pI->product_id == $pro->id) {
                    $imgs[] = $pI;
                }
            }
            $pro->productImages = $imgs;
        }
    }

    return $products;
    }

  /**
   * Hàm đếm tổng số sản phẩm (Dùng để tính số trang hiển thị ở View)
   */
  public static function count($filters = [])
  {
    $query = Query::from("products p")
      ->select(["COUNT(*) as total"])
      ->joins([
        "branchs b on b.id = p.branch_id",
        "productcategorys pc on pc.id = p.product_category_id",
      ]);

    self::applyFilters($query, $filters);
    
    $result = $query->get();
    return $result ? $result['total'] : 0;
    }

  /**
   * Hàm phụ: Giúp tái sử dụng logic lọc cho cả paginate và count
   */
  private static function applyFilters($query, $filters)
  {
    $conditions = [];
    $bindValues = [];

    // Lọc Brand
    if (!empty($filters['branch'])) {
        $conditions[] = "b.id = :branchId";
        $bindValues[":branchId"] = $filters['branch'];
    }

    // Lọc Giá
    if (!empty($filters['price_min'])) {
        $conditions[] = "p.price_current >= :minPrice";
        $bindValues[":minPrice"] = $filters['price_min'];
    }
    if (!empty($filters['price_max'])) {
        $conditions[] = "p.price_current <= :maxPrice";
        $bindValues[":maxPrice"] = $filters['price_max'];
    }

    // Lọc Loại (Category)
    if (!empty($filters['product_category'])) { // Đổi category -> product_category
    $conditions[] = "pc.id = :catId"; // Hoặc pc.name tùy logic
    $bindValues[":catId"] = $filters['product_category'];
    }
    
    //Lọc Giảm Giá
    if (!empty($filters['has_discount'])) {
        $conditions[] = "p.discount_percent > 0";
    }

    // Áp dụng vào Query
    if (!empty($conditions)) {
        $query->where($conditions)->bindValue($bindValues);
    }
    }
  public static function getAllProductCategories()
    {
        // Truy vấn bảng productcategorys (giả sử bảng tên là thế)
        $rows = Query::from("productcategorys")->getAll();
        
        $productCategories = [];
        foreach ($rows as $row) {
            $cat = new ProductCategory();
            $cat->fill($row); // Hoặc hàm fill() tuỳ model bạn viết
            // Lưu ý: Nếu fillJoin cần prefix "pc_" thì query phải select as. 
            // Để đơn giản, giả sử fill() nhận đúng tên cột
            $cat->id = $row['id'];
            $cat->name = $row['name'];
            $productCategories[] = $cat;
        }
        return $productCategories;
    }
  public static function getAllBranches()
    {
        $rows = Query::from("branchs")->getAll();
        
        $branches = [];
        foreach ($rows as $row) {
            $branch = new Branch();
            $branch->fill($row);
            $branch->id = $row['id'];
            $branch->name = $row['name'];
            $branches[] = $branch;
        }
        return $branches;
    } 
  public static function getById($id)
  {
    // 1. Lấy thông tin cơ bản (Product + Branch + Category)
    // Lưu ý: Thêm điều kiện WHERE p.id = :id
    $rows = Query::from("products p")
      ->select(["p.*", Branch::FieldJoin, ProductCategory::FieldJoin])
      ->joins([
        "branchs b on b.id = p.branch_id",
        "productcategorys pc on pc.id = p.product_category_id",
      ])
      ->where(["p.id = :id"])
      ->bindValue([":id" => $id])
      ->getAll();

    if (empty($rows)) {
      return null; // Không tìm thấy sản phẩm
    }

    // Map dữ liệu vào Model
    $row = $rows[0]; // Lấy dòng đầu tiên
    $product = new Product();
    $product->fill($row);

    $branch = new Branch();
    $branch->fillJoin($row);

    $cat = new ProductCategory();
    $cat->fillJoin($row); // Hoặc fill() tuỳ vào cách bạn viết Model ProductCategory

    $product->branch = $branch;
    $product->productCategory = $cat;

    // 2. Lấy danh sách ảnh của sản phẩm này
    $imgRows = Query::from("productimages")
      ->where(["product_id = :pid"])
      ->bindValue([":pid" => $id])
      ->getAll();

    $imgs = [];
    foreach ($imgRows as $imgRow) {
      $img = new ProductImage();
      $img->fill($imgRow);
      $imgs[] = $img;
    }
    $product->productImages = $imgs;

    return $product;
    }
  // ... (Các hàm find, paginate, count, getById... giữ nguyên)

    /**
     * [MỚI] Thêm sản phẩm
     */
    public static function create($data)
    {
        $product = new Product();
        $product->name = $data['name'];
        // Tự tạo slug nếu chưa có
        $product->slug = self::slugify($data['name']); 
        $product->description = $data['description'];
        $product->price_current = $data['price_current'];
        $product->price_original = $data['price_original'];
        $product->discount_percent = $data['discount_percent'];
        $product->quantity = $data['quantity'];
        $product->branch_id = $data['branch_id'];
        $product->product_category_id = $data['product_category_id'];
        
        // Lưu Product -> Trả về ID mới
        $newId = Query::from("products")->save($product);

        // Lưu ảnh
        if (!empty($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $url) {
                $img = new ProductImage();
                $img->product_id = $newId;
                $img->url = $url;
                Query::from("productimages")->save($img);
            }
        }
        return $newId;
    }

    /**
     * [MỚI] Cập nhật sản phẩm
     */
    public static function update($id, $data)
    {
        $product = new Product();
        $product->id = $id; // Gán ID để Query biết là update
        $product->name = $data['name'];
        $product->slug = self::slugify($data['name']);
        $product->description = $data['description'];
        $product->price_current = $data['price_current'];
        $product->price_original = $data['price_original'];
        $product->discount_percent = $data['discount_percent'];
        $product->quantity = $data['quantity'];
        $product->branch_id = $data['branch_id'];
        $product->product_category_id = $data['product_category_id'];

        Query::from("products")->save($product);

        // Xử lý ảnh: Cách đơn giản là xóa ảnh cũ, thêm ảnh mới (hoặc xử lý logic merge ở Controller)
        if (isset($data['images']) && is_array($data['images'])) {
            // Xóa ảnh cũ
            $pdo = PDODatabase::getInstance()->getConnection();
            $stmt = $pdo->prepare("DELETE FROM productimages WHERE product_id = :pid");
            $stmt->execute([':pid' => $id]);

            // Thêm ảnh mới
            foreach ($data['images'] as $url) {
                $img = new ProductImage();
                $img->product_id = $id;
                $img->url = $url;
                Query::from("productimages")->save($img);
            }
        }
    }

    // Hàm hỗ trợ tạo slug
    private static function slugify($text)
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('~[^\\pL\\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        $text = preg_replace('~[^-a-z0-9]+~', '', $text);
        return $text ?: 'n-a';
    }
  
}

