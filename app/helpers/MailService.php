<?php
// 1. Nạp thủ công 3 file lõi của PHPMailer
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/lib/PHPMailer/src/Exception.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/lib/PHPMailer/src/PHPMailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/lib/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {
    
    // CẤU HÌNH GMAIL (Sửa ở đây)
    private static $SMTP_USER = 'lnhanh2411@gmail.com';  // <--- Điền Email của bạn
    private static $SMTP_PASS = 'fxbv sifg bjmk oapa'; // <--- Điền Mật khẩu ứng dụng (Xem Bước 5)

    // Thêm tham số $orderItems vào hàm
    public static function sendOrderCompleted($toEmail, $customerName, $orderId, $orderItems = []) {
        $mail = new PHPMailer(true);

        try {
            // --- CẤU HÌNH (Giữ nguyên như cũ) ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = self::$SMTP_USER;
            $mail->Password   = self::$SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(self::$SMTP_USER, 'AMusic Admin');
            $mail->addAddress($toEmail, $customerName);

            // --- XỬ LÝ HTML DANH SÁCH SẢN PHẨM ---
            $listItemsHtml = '';
            $totalOrder = 0;
            
            foreach ($orderItems as $item) {
                // Format tiền cho đẹp (VD: 5,000,000 đ)
                $price = number_format($item->product_price);
                $totalItem = number_format($item->product_total_price);
                $totalOrder += $item->product_total_price;
                
                $listItemsHtml .= "
                    <tr style='border-bottom: 1px solid #eee;'>
                        <td style='padding: 10px;'>{$item->product_name}</td>
                        <td style='padding: 10px; text-align: center;'>{$item->quantity}</td>
                        <td style='padding: 10px; text-align: right;'>{$price} đ</td>
                        <td style='padding: 10px; text-align: right; font-weight: bold;'>{$totalItem} đ</td>
                    </tr>
                ";
            }
            // Cộng thêm ship 30k (nếu logic bên bạn có ship)
            $totalOrderDisplay = number_format($totalOrder + 30000); 

            // --- NỘI DUNG MAIL ---
            $mail->isHTML(true);
            $mail->Subject = "Đơn hàng #$orderId của bạn đã giao thành công";
            
            $body = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px;'>
                    <h2 style='color: #0d6efd; border-bottom: 2px solid #0d6efd; padding-bottom: 10px;'>Cảm ơn bạn đã mua hàng!</h2>
                    <p>Xin chào <strong>$customerName</strong>,</p>
                    <p>Đơn hàng <strong>#$orderId</strong> của bạn đã được giao thành công. Dưới đây là chi tiết đơn hàng:</p>
                    
                    <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                        <thead>
                            <tr style='background-color: #f8f9fa; text-align: left;'>
                                <th style='padding: 10px;'>Sản phẩm</th>
                                <th style='padding: 10px; text-align: center;'>SL</th>
                                <th style='padding: 10px; text-align: right;'>Đơn giá</th>
                                <th style='padding: 10px; text-align: right;'>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            $listItemsHtml
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan='3' style='padding: 10px; text-align: right;'>Phí vận chuyển:</td>
                                <td style='padding: 10px; text-align: right;'>30,000 đ</td>
                            </tr>
                            <tr style='background-color: #e9ecef;'>
                                <td colspan='3' style='padding: 10px; text-align: right; font-weight: bold;'>TỔNG CỘNG:</td>
                                <td style='padding: 10px; text-align: right; font-weight: bold; color: #dc3545;'>$totalOrderDisplay đ</td>
                            </tr>
                        </tfoot>
                    </table>

                    <p style='margin-top: 30px;'>Nếu bạn hài lòng với sản phẩm, hãy ghé thăm chúng tôi lần tới nhé!</p>
                    <p>Trân trọng,<br>Đội ngũ AMusic</p>
                </div>
            ";
            
            $mail->Body = $body;
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mail Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    public static function sendResetPassword($toEmail, $userName, $resetLink) {
        $mail = new PHPMailer(true);
        try {
            // Cấu hình SMTP (Giữ nguyên như hàm sendOrderCompleted)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = self::$SMTP_USER; 
            $mail->Password   = self::$SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // Người gửi & Người nhận
            $mail->setFrom(self::$SMTP_USER, 'AMusic Support');
            $mail->addAddress($toEmail, $userName);

            // Nội dung
            $mail->isHTML(true);
            $mail->Subject = 'Yêu cầu đặt lại mật khẩu - AMusic';
        
            $body = "
                <h3>Chào $userName,</h3>
                <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                <p>Vui lòng nhấn vào nút bên dưới để đặt lại mật khẩu (Link có hiệu lực trong 1 giờ):</p>
                <p>
                    <a href='$resetLink' style='background-color: #0D8ABC; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                        Đặt lại mật khẩu
                    </a>
                </p>
                <p>Hoặc truy cập link: <a href='$resetLink'>$resetLink</a></p>
                <p>Nếu bạn không yêu cầu, vui lòng bỏ qua email này.</p>
            ";
        
            $mail->Body = $body;
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mail Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
?>