<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers/Import.php';
Import::helpers(["Cookie"]);
// Xoá JWT token dùng để xác thực
Cookie::delete("token");
header("Location: ../Home.php");
exit;