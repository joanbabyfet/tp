<?php
declare (strict_types = 1);

namespace app\service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use think\facade\Log;

class MailService extends BaseService
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        try {
            $this->mail->SMTPDebug = 0;                                 // 启用调试模式 (0:关闭, 2:详细)
            $this->mail->isSMTP();                                      // 使用SMTP协议发送
            $this->mail->Host       = config('mail.host');        // SMTP服务器地址
            $this->mail->SMTPAuth   = true;                             // 启用SMTP验证
            $this->mail->Username   = config('mail.username');    // SMTP用户名(邮箱地址)
            $this->mail->Password   = config('mail.password');    // SMTP密码(授权码)
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;      // 启用TLS加密
            $this->mail->Port       = config('mail.port');        // SMTP服务器端口
            $this->mail->CharSet    = 'UTF-8';                          // 邮件编码
            $this->mail->setFrom(config('mail.from_email'), config('mail.from_name')); // 发件人邮箱和名称
        } catch (Exception $e) {
            //记录日志
            Log::error('邮件初始化失败: ' . $e->getMessage());
        }
    }

    /**
     * 发送邮件
     * @param $to
     * @param $subject
     * @param $body
     * @return bool
     */
    public function send($to, $subject, $body)
    {
        $status = 1;
        try {
            $this->mail->addAddress($to);       // 添加收件人
            $this->mail->isHTML(true);   // 设置邮件内容为HTML格式
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->send();
        } catch (Exception $e) {
            $status = $this->get_exception_status($e);
            //记录日志
            Log::error(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'args'    => func_get_args()
            ]);
        }
        return $status;
    }
}
