## Components UI: https://daisyui.com/components
## Yêu cầu cần có

- Docker/Docker desktop

## Chạy

```
```
docker compose up -d --build
## Cấu trúc hiện tại trong app
- api: nơi thiết lập routes
- configs:
  - db: chứa kết nối PDO.php và utils class Query.php
  - JWT.php: chứa class JWT
- controllers: nơi xử lý logic
- helpers:
  - API.php: abstract class API cho phép xử lý các method POST/GET từ client với query feature là bắt buộc, chi tiết xem ở app/helpers/API.php
  - Cookie.php: utils class Cookie cho phép dễ dàng sử lý với cookie
  - Import.php: utils class cho phép require/once_require nhiều file mà không cần quan tâm với đường dẫn, chi tiết xem ở file
  - Password.php: utils class cho phép encode/decode string
  - Request.php: utils class cho phép lấy dữ liệu từ POST/GET nhanh và đã xử lý htmlspecialchars, không cần quan tâm với lấy POST/GET mà chỉ quan tâm tới nghiệp vụ
- middlewares: nơi chứa các file middleware
  - Authentication.php: middleware cho phép kiểm tra tài khoản đã xác thực hay chưa, setAuthentication, getAuthentication
- models: chứa các entity, trong các model, luôn có phương thức static tables() trả về mảng chuỗi string các thuộc tính tương ứng lúc tạo table trong csdl, chi tiết ví dụ ở app/models
  - Model.php: abstract class Model cho phép các children khi kế thừa sẽ có 2 phương thức và 2 thuộc tính là $created_at,$updated_at, fill($dât), totoArray()
    - fill($data): khi đưa $data($row) từ PDO vào hàm fill, fill sẽ kiểm tra property_exists của key trong $data có trong class hay không, nếu có thì tự động đổ dữ liệu vào. Vì thế khi tạo các models , các thuộc tính nên trùng với tên column trong cơ sở dữ liệu
    - toArray(): hàm sẽ quét các thuộc tính public và trả về mảng dạng key => value, với key là tên thuộc tính và value là giá trị, trong trường hợp giá trị null thì sẽ không đưa vào
- repositories: nơi chứa các câu lệnh truy vấn với database, chi tiết ví dụ ở app/repositories
- views:
  - components: nơi chứa các component
  - layouts: chứa các file liên quan layout, ví dụ head, footer, header, ...
  - pages: nơi chứa các trang hiển thị
