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
        $pdo = PDODatabase::getInstance()->getConnection();
        $offset = ($page - 1) * $limit;

        // 1. Xây dựng câu SQL cơ bản
        $sql = "SELECT p.*, 
                       b.id as branch_id, b.name as branch_name, 
                       pc.id as pc_id, pc.name as pc_name
                FROM products p
                LEFT JOIN branchs b ON b.id = p.branch_id
                LEFT JOIN productcategorys pc ON pc.id = p.product_category_id
                WHERE 1=1";

        $params = [];

        // 2. ÁP DỤNG BỘ LỌC (Chuyển từ Query Builder sang SQL thuần)
        
        // Lọc Branch
        if (!empty($filters['branch'])) {
            $sql .= " AND b.id = :branchId";
            $params[':branchId'] = $filters['branch'];
        }

        // Lọc Category
        if (!empty($filters['product_category'])) {
            $sql .= " AND pc.id = :catId";
            $params[':catId'] = $filters['product_category'];
        }

        // Lọc Giá
        if (!empty($filters['price_min'])) {
            $sql .= " AND p.price_current >= :minPrice";
            $params[':minPrice'] = $filters['price_min'];
        }
        if (!empty($filters['price_max'])) {
            $sql .= " AND p.price_current <= :maxPrice";
            $params[':maxPrice'] = $filters['price_max'];
        }

        // Lọc có giảm giá
        if (!empty($filters['has_discount'])) {
            $sql .= " AND p.discount_percent > 0";
        }

        // 3. XỬ LÝ SẮP XẾP (Quan trọng: Đặt trước LIMIT)
        $sort = $filters['sort'] ?? 'popular';
        switch ($sort) {
            case 'price_asc': 
                $sql .= " ORDER BY p.price_current ASC"; 
                break;
            case 'price_desc': 
                $sql .= " ORDER BY p.price_current DESC"; 
                break;
            case 'popular':
            default: 
                $sql .= " ORDER BY p.created_at DESC"; 
                break;
        }

        // 4. PHÂN TRANG
        $sql .= " LIMIT :limit OFFSET :offset";

        // 5. THỰC THI QUERY
        $stmt = $pdo->prepare($sql);
        
        // Bind params cho bộ lọc
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        // Bind params cho phân trang (bắt buộc dùng PARAM_INT)
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 6. MAP DỮ LIỆU & LẤY ẢNH (Logic cũ)
        $products = [];
        $prodIds = [];

        foreach ($rows as $row) {
            $pro = new Product();
            $pro->fill($row);

            $branch = new Branch();
            $branch->id = $row['branch_id'] ?? null;
            $branch->name = $row['branch_name'] ?? null;
            $pro->branch = $branch;

            $proCate = new ProductCategory();
            $proCate->id = $row['pc_id'] ?? null;
            $proCate->name = $row['pc_name'] ?? null;
            $pro->productCategory = $proCate;

            $products[] = $pro;
            $prodIds[] = $pro->id;
        }

        // Lấy ảnh (Eager Loading)
        if (!empty($prodIds)) {
            $idsString = implode(", ", $prodIds);
            // Dùng Query builder cho đoạn này vẫn ổn vì nó đơn giản
            $imgRows = Query::from("productimages pi")
                ->where(["pi.product_id IN ($idsString)"])
                ->getAll();

            $productImages = [];
            foreach ($imgRows as $r) {
                $img = new ProductImage();
                $img->fill($r);
                $productImages[] = $img;
            }

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
        // 1. Dùng JOIN mặc định của thư viện Query (Inner Join)
        // Không ghi "LEFT JOIN" hay "INNER JOIN" vào chuỗi, chỉ ghi tên bảng và điều kiện
        $rows = Query::from("products p")
            ->select([
                "p.*", 
                Branch::FieldJoin, 
                ProductCategory::FieldJoin
            ])
            ->joins([
                "branchs b on b.id = p.branch_id",
                "productcategorys pc on pc.id = p.product_category_id",
            ])
            ->where(["p.id = :id"])
            ->bindValue([":id" => $id])
            ->getAll();

        if (empty($rows)) {
            return null;
        }

        $row = $rows[0];
        $product = new Product();
        $product->fill($row);

        // Map dữ liệu sang Object con
        $branch = new Branch();
        $branch->fillJoin($row);
        $product->branch = $branch;

        $cat = new ProductCategory();
        $cat->fillJoin($row);
        $product->productCategory = $cat;

        // 2. Lấy ảnh
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
  

    /**
     * [MỚI] Thêm sản phẩm
     */
    public static function create($data)
    {
        $product = new Product();
        $product->name = $data['name'];
        $product->slug = self::slugify($data['name']);
        $product->description = $data['description'];
        
        $product->price_original = $data['price_original'];
        $product->discount_percent = $data['discount_percent'];
        $product->price_current = self::calculateCurrentPrice($data['price_original'], $data['discount_percent']);
        
        $product->quantity = $data['quantity'];
        
        // [QUAN TRỌNG] Gán ID vào thuộc tính để Query->save() nhận diện được cột
        $product->branch_id = $data['branch_id'];
        $product->product_category_id = $data['product_category_id'];
        
        // Save trả về ID mới
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
        $product->id = $id;
        $product->name = $data['name'];
        $product->slug = self::slugify($data['name']);
        $product->description = $data['description'];
        
        $product->price_original = $data['price_original'];
        $product->discount_percent = $data['discount_percent'];
        $product->price_current = self::calculateCurrentPrice($data['price_original'], $data['discount_percent']);

        $product->quantity = $data['quantity'];
        
        // Gán ID để cập nhật
        $product->branch_id = $data['branch_id'];
        $product->product_category_id = $data['product_category_id'];

        Query::from("products")->save($product);

        // Xử lý ảnh (Xóa cũ thêm mới cho đơn giản)
        if (isset($data['images']) && is_array($data['images'])) {
            $pdo = PDODatabase::getInstance()->getConnection();
            $stmt = $pdo->prepare("DELETE FROM productimages WHERE product_id = :pid");
            $stmt->execute([':pid' => $id]);

            foreach ($data['images'] as $url) {
                $img = new ProductImage();
                $img->product_id = $id;
                $img->url = $url;
                Query::from("productimages")->save($img);
            }
        }
    }
    private static function calculateCurrentPrice($original, $percent)
    {
        $original = (float)$original;
        $percent = (int)$percent;
        if ($percent < 0) $percent = 0;
        if ($percent > 100) $percent = 100;
        return $original * (1 - ($percent / 100));
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
    
    // Hàm lấy sản phẩm nổi bật 
    public static function getBestSellingProducts($limit = 8)
    {
        $pdo = PDODatabase::getInstance()->getConnection();

        // [LOGIC MỚI]: 
        // 1. Dùng INNER JOIN giữa OrderItems và Orders để loại bỏ ngay lập tức các đơn rác
        // 2. Dùng WHERE o.status_order = 'completed' để chỉ lấy đơn thành công
        $sql = "SELECT p.*, 
                       b.id as branch_id, b.name as branch_name, 
                       pc.id as pc_id, pc.name as pc_name,
                       SUM(oi.quantity) as total_sold
                FROM products p
                JOIN orderitems oi ON p.id = oi.product_id
                JOIN orders o ON oi.order_id = o.id
                LEFT JOIN branchs b ON p.branch_id = b.id
                LEFT JOIN productcategorys pc ON p.product_category_id = pc.id
                WHERE o.status_order = 'completed'
                GROUP BY p.id
                ORDER BY total_sold DESC
                LIMIT :limit";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        $prodIds = [];

        // Map dữ liệu từ SQL sang Model Product
        foreach ($rows as $row) {
            $pro = new Product();
            $pro->fill($row);

            $branch = new Branch();
            $branch->id = $row['branch_id'];
            $branch->name = $row['branch_name'];
            $pro->branch = $branch;

            $proCate = new ProductCategory();
            $proCate->id = $row['pc_id'];
            $proCate->name = $row['pc_name'];
            $pro->productCategory = $proCate;

            $products[] = $pro;
            $prodIds[] = $pro->id;
        }

        // Lấy hình ảnh (Logic cũ giữ nguyên)
        if (!empty($prodIds)) {
            $idsString = implode(", ", $prodIds);
            
            $imgRows = Query::from("productimages pi")
                ->where(["pi.product_id IN ($idsString)"])
                ->getAll();

            $productImages = [];
            foreach ($imgRows as $r) {
                $img = new ProductImage();
                $img->fill($r);
                $productImages[] = $img;
            }

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
}

