<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\lib\Response;
use app\common\lib\Util;
use app\job\MailJob;
use app\job\PushJob;
use app\job\SmsJob;
use app\job\TgJob;
use app\service\MailService;
use app\service\pay\PayContext;
use app\service\pay\PayFactory;
use app\service\pay\strategy\CYStrategy;
use app\service\PushService;
use app\service\sms\SmsContext;
use app\service\sms\SmsFactory;
use app\service\sms\strategy\SpugStrategy;
use app\service\sms\strategy\UnimtxStrategy;
use app\service\TgService;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use think\App;
use think\facade\Lang;
use think\facade\Queue;
use think\Request;

class Test extends Base
{
    public function demo()
    {

    }
}
