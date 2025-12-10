<?php
require_once "Request.php";
/**
 * Summary of API
 * - Mỗi API sẽ có 2 phương thức GET và POST, mỗi phương thức khi gửi từ client lên luôn phải kèm 1 giá trị feature để định nghĩa phương thức đó sẽ làm gì
 */
abstract class API
{
    public function __construct()
    {
        //Chuyen thanh json
        header("Content-Type: application/json");
    }
    /**
     * Summary of GET
     * @param string $feature
     * - ví dụ:
     * public function GET(string $feature){
     *  switch ($feature) {
     *       case "getAllUser": {
     *               $this->response($this->userController->getAllUser());
     *                break;
     *        }
     *   }
     * }
     */
    public abstract function GET(string $feature);
    /**
     * Summary of POST
     * @param string $feature
     * - Xem ví dụ tương tự GET, chỉ khác cách thức
     */
    public abstract function POST(string $feature);
    public function listen()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $feature = Request::data('feature');
        if ($feature == null) {
            $this->featureNotFound();
            return;
        }
        switch ($method) {
            case 'GET':
                $this->GET($feature);
                break;
            case 'POST':
                $this->POST($feature);
                break;
            default:
                http_response_code(405); // Method Not Allowed
                echo json_encode([
                    'error' => "Method $method not allowed"
                ]);
                break;
        }
    }
    protected function response($data, $status = 200)
    {
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
    protected function featureNotFound()
    {
        http_response_code(400);
        echo json_encode([
            'error' => "feature not found"
        ]);
        exit;
    }
}
