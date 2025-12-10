<?php
Import::models(['CartItem', 'Product', 'ProductImage', 'Branch', 'ProductCategory']);
Import::configs(["db/Query"]);

class CartRepository
{
    /**
     * Lấy danh sách sản phẩm trong giỏ (Kèm Product, Branch, Category, Images)
     * Áp dụng kỹ thuật Eager Loading để tối ưu query ảnh.
     */
    public static function getItems($userId)
    {
        $cartItems = [];
        $prodIds = []; 

        // BƯỚC 1: Lấy CartItem JOIN với Product, Branch, Category
        // Sử dụng FieldJoin để tránh trùng tên cột
        $rows = Query::from("cartitems ci")
            ->select([
                "ci.id as cart_id", 
                "ci.quantity as cart_qty",
                "p.*", // Lấy tất cả thông tin bảng products
                Branch::FieldJoin, // Lấy thông tin Branch (b_id, b_name...)
                ProductCategory::FieldJoin // Lấy thông tin Category (pc_id...)
            ])
            ->joins([
                "products p ON ci.product_id = p.id",
                "branchs b on b.id = p.branch_id",
                "productcategorys pc on pc.id = p.product_category_id"
            ])
            ->where(["ci.user_id = :uid"])
            ->bindValue([":uid" => $userId])
            ->getAll();

        // BƯỚC 2: Mapping dữ liệu thô vào các Model (Logic giống ProductRepository)
        foreach ($rows as $row) {
            // a. Khởi tạo & Fill Product
            $product = new Product();
            $product->fill($row);

            // b. Khởi tạo & Fill Branch
            $branch = new Branch();
            $branch->fillJoin($row);
            $product->branch = $branch;

            // c. Khởi tạo & Fill Category
            $cat = new ProductCategory();
            $cat->fillJoin($row); 
            $product->productCategory = $cat;

            // d. Tạo CartItem và gán Product vào
            $item = new CartItem();
            $item->id = $row['cart_id'];
            $item->quantity = $row['cart_qty'];
            $item->user_id = $userId;
            $item->product = $product; // Quan hệ: CartItem chứa Product

            // Lưu lại danh sách
            $cartItems[] = $item;
            $prodIds[] = $product->id;
        }

        // BƯỚC 3: Eager Loading Images (Lấy ảnh hàng loạt bằng WHERE IN)
        if (!empty($prodIds)) {
            $productImages = [];
            
            // Loại bỏ ID trùng lặp để query nhẹ hơn
            $uniqueIds = array_unique($prodIds);
            
            // Query lấy toàn bộ ảnh của các sản phẩm này
            $prodImgRows = Query::from("productimages pi")
                ->where(["pi.product_id IN (" . implode(", ", $uniqueIds) . ")"])
                ->getAll();

            // Ánh xạ DB vào Model ProductImage
            foreach ($prodImgRows as $row) {
                $proI = new ProductImage();
                $proI->fill($row);
                array_push($productImages, $proI);
            }

            // BƯỚC 4: Ghép ảnh vào đúng Product tương ứng bên trong CartItem
            foreach ($cartItems as $item) {
                $imgs = [];
                // Duyệt qua danh sách ảnh đã lấy
                foreach ($productImages as $pI) {
                    // Nếu id sản phẩm của ảnh khớp với id sản phẩm trong giỏ
                    if ($pI->product_id == $item->product->id) {
                        array_push($imgs, $pI);
                    }
                }
                // Gán mảng ảnh vào Model Product
                $item->product->productImages = $imgs;
            }
        }

        return $cartItems;
    }

    /**
     * Thêm sản phẩm vào giỏ (Nếu có rồi thì cộng dồn số lượng)
     */
    public static function add($userId, $productId, $quantity = 1)
    {
        // 1. Kiểm tra tồn tại
        $exist = Query::from("cartitems")
            ->where(["user_id = :uid", "product_id = :pid"])
            ->bindValue([":uid" => $userId, ":pid" => $productId])
            ->get();

        $pdo = PDODatabase::getInstance()->getConnection();
        
        if ($exist) {
            // Có rồi -> Update cộng dồn
            $newQty = $exist['quantity'] + $quantity;
            $stmt = $pdo->prepare("UPDATE cartitems SET quantity = :qty WHERE id = :id");
            $stmt->bindValue(':qty', $newQty);
            $stmt->bindValue(':id', $exist['id']);
        } else {
            // Chưa có -> Insert mới
            $stmt = $pdo->prepare("INSERT INTO cartitems (user_id, product_id, quantity) VALUES (:uid, :pid, :qty)");
            $stmt->bindValue(':uid', $userId);
            $stmt->bindValue(':pid', $productId);
            $stmt->bindValue(':qty', $quantity);
        }
        $stmt->execute();
    }

    /**
     * Cập nhật số lượng (Cho nút tăng/giảm ở trang giỏ hàng)
     */
    public static function updateQuantity($cartItemId, $quantity)
    {
        if ($quantity <= 0) {
            self::remove($cartItemId); // Nếu giảm về 0 thì xóa luôn
            return;
        }
        $pdo = PDODatabase::getInstance()->getConnection();
        $stmt = $pdo->prepare("UPDATE cartitems SET quantity = :qty WHERE id = :id");
        $stmt->bindValue(':qty', $quantity);
        $stmt->bindValue(':id', $cartItemId);
        $stmt->execute();
    }

    /**
     * Xóa 1 sản phẩm khỏi giỏ
     */
    public static function remove($cartItemId)
    {
        $pdo = PDODatabase::getInstance()->getConnection();
        $stmt = $pdo->prepare("DELETE FROM cartitems WHERE id = :id");
        $stmt->bindValue(':id', $cartItemId);
        $stmt->execute();
    }

    /**
     * Xóa toàn bộ giỏ hàng (Dùng khi checkout thành công hoặc nút 'Xóa tất cả')
     */
    public static function clear($userId)
    {
        $pdo = PDODatabase::getInstance()->getConnection();
        $stmt = $pdo->prepare("DELETE FROM cartitems WHERE user_id = :uid");
        $stmt->bindValue(':uid', $userId);
        $stmt->execute();
    }
    public static function getTotalQuantity($userId)
    {
        // Sử dụng hàm SUM của SQL để cộng dồn cột quantity
        $result = Query::from("cartitems")
            ->select(["SUM(quantity) as total"])
            ->where(["user_id = :uid"])
            ->bindValue([":uid" => $userId])
            ->get();

        // Nếu null (giỏ trống) thì trả về 0
        return $result['total'] ?? 0;
    }
}