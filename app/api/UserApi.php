<?php
//Chỉ require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php'; ở mỗi API hoặc pages
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::helpers(["API", "Request"]);
Import::controllers(["UserController"]);
class UserApi extends API
{
  private UserController $userController;
  public function __construct(UserController $userController)
  {
    parent::__construct();
    $this->userController = $userController;
  }
  public function GET(string $feature)
  {
    switch ($feature) {
      case "getAllUser": {
          $this->response($this->userController);
          break;
        }
    }
  }
  public function POST(string $feature)
  {
    throw new \Exception('Not implemented');
  }
}
$userController = new UserController();
$api = new UserApi($userController);
$api->listen();
