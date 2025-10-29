<?php
declare (strict_types = 1);

namespace app\common\service;

use app\common\lib\cls_response;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use think\facade\Lang;
use think\facade\Validate;

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
            $status = $this->get_exception_status($e);
            //写入日志
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
            ]);
        }
    }

    /**
     * 发送邮件
     * @param array $data
     * @return int|mixed
     * @throws \Exception
     */
    public function send(array $data)
    {
        //参数过滤
        $validate = Validate::rule([
            'to'        => 'require|string',
            'subject'   => 'require|string',
            'body'      => 'require|string',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $to         = $data['to'];
            $subject    = $data['subject'];
            $body       = $data['body'];

            $this->mail->addAddress($to);       // 添加收件人
            $this->mail->isHTML(true);   // 设置邮件内容为HTML格式
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->send();
        } catch (Exception $e) {
            $status = $this->get_exception_status($e);
            //写入日志
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'    => $data
            ]);
        }
        return $status;
    }
}
