<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\lib\RedisLock;

use app\common\lib\Util;
use app\job\MailJob;
use app\job\PushJob;
use app\job\SmsJob;
use app\job\TgJob;
use app\model\AdModel;
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
use think\facade\Cache;
use think\facade\Db;
use think\facade\Lang;
use think\facade\Queue;
use think\Request;

class TestController extends BaseController
{
    public function demo()
    {
        //获取列表
        //$data = Db::name('example')->where('status', 1)->select()->toArray();
        //$data = Db::name('example')->where('author', 'not null')->select()->toArray();
        //$data = Db::name('example')->where('id', 'in', [6, 7])->select()->toArray();
        //$data = Db::name('example')->whereIn('id', '6, 7')->select()->toArray();
        //$data = Db::name('example')->where('id', 'exp', 'IN (6, 7, 8)')->select()->toArray();
        //$data = Db::name('example')->whereExp('id', 'IN (6, 7, 8)')->select()->toArray();
        //原生, 如果当前采用分布式数据库，并且设置读写分离，query方法默认是在读服务器(从)执行, 如果从主库读取使用 true
        //$data = Db::query('select * from mo_example where status=:id', ['id' => 1], true);

        //获取单条
        //$data = Db::name('example')->where('id', 6)->findOrEmpty();
        //获取单条某字段值
        //$data = Db::name('example')->where('id', 6)->value('id');
        //$data = Db::name('example')->where('status', 1)->column('name', 'id');
        //获取列表且id为索引值
        //$data = Db::name('example')->where('status', 1)->column('*', 'id');
//        echo '<pre>';
//        print_r($data);
//        exit;

        //分批处理, 每次处理 100 条记录
//        Db::name('example')->chunk(100, function($examples) {
//            foreach ($examples as $example) {
//                $example['update_time'] = time();
//                Db::name('example')->update($example);
//            }
//        });

        //添加
//        $data = ['name' => '我是标题', 'content' => '我是内容'];
//        Db::name('example')->insert($data);
//        Db::name('example')->insertGetId($data);

        $data = ['name' => '我是标题'];
        Db::name('ad_set')->insert($data);

        //批量添加, 返回添加条数
//        $data = [
//            ['name' => '我是标题1', 'content' => '我是内容1'],
//            ['name' => '我是标题2', 'content' => '我是内容2'],
//            ['name' => '我是标题3', 'content' => '我是内容3'],
//            ['name' => '我是标题4', 'content' => '我是内容4'],
//            ['name' => '我是标题5', 'content' => '我是内容5'],
//            ['name' => '我是标题6', 'content' => '我是内容6'],
//            ['name' => '我是标题7', 'content' => '我是内容7'],
//            ['name' => '我是标题8', 'content' => '我是内容8'],
//            ['name' => '我是标题9', 'content' => '我是内容9'],
//            ['name' => '我是标题10', 'content' => '我是内容10'],
//            ['name' => '我是标题11', 'content' => '我是内容11'],
//            ['name' => '我是标题12', 'content' => '我是内容12'],
//            ['name' => '我是标题13', 'content' => '我是内容13'],
//        ];
//        Db::name('example')->insertAll($data);
//        Db::name('example')->limit(100)->insertAll($data); //分批写入 每次最多100条数据

        //更新
//        Db::name('example')
//            ->where('id', 1)
//            ->update(['name' => '我是标题2']);
//        Db::name('example')
//            ->where('id', 1)
//            ->data(['name' => '我是标题3'])
//            ->update();
        //原生
        //Db::execute("update mo_example set name='thinkphp' where id = 8");

        //删除
        //Db::name('example')->delete(1);
        //Db::name('example')->delete([2, 3]);
        //Db::name('example')->where('id',4)->delete();
        //Db::name('example')->where('id','=',5)->delete();
//        Db::name('example')
//            ->where('id', 6)
//            ->useSoftDelete('delete_time',time())
//            ->delete();
//        Db::name('example')
//            ->useSoftDelete('delete_time',time())
//            ->delete([7, 8]);

        //写入字符串, 缓存60秒
        //Cache::store('redis')->set('name', 'kosplay', 60);
//        $data = Cache::store('redis')->get('name');
//        //Cache::store('redis')->delete('name');
//        print_r($data);

        //测试队列
//        $data = [];
//        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
//        $res = Queue::push(Job1::class, $data, $queue = null);
//        var_dump($res);

        //获取GET请求参数
        //$data = $this->request->param();
        //$data = $this->request->param('name');

        //获取POST请求参数
        //$data = $this->request->post();
        //$data = $this->request->post('name');

        //获取json数据
//        $jsonData = $this->request->getContent();
//        $data = json_decode($jsonData, true);
//        $name = $data['name'] ?? '';
//        $age = $data['age'] ?? 0;
        //print_r($data);

//        $validate = new User;
//        if (!$validate->check($data)) {
//            $this->error($validate->getError());
//        }

//        $validate = new Validate([
//            'name' => 'require|max:25',
//            'email' => 'email'
//        ]);
//
//        if (!$validate->check($data)) {
//            dump($validate->getError());
//        }

        //测试fcm
//        $data = [
//            'token' => 'fNrBZGpWQe-YNLRaU_K-_c:APA91bFjXKh-IpWch7eKC5r1lsKm05EpjSBS-wAWzVtaJiWzoM9yPJ-xMMpNEnL3u7AXo4ez7xK4141sWDHNAOFsfKh5ZmU6j1sY02M-rkjCWuO3X6BqUwI',
//            'title' => '测试2',
//            'body'  => '测试测试测试',
//            'image' => 'https://muki.tw/favicon.ico',
//            'data'  => [
//                'k1' => 'v1',
//            ],
//        ];
//        $push_service = new PushService();
//        $status = $push_service->send($data);
//        if($status < 0) {
//            return $this->error($push_service->get_err_msg($status), $status);
//        }
//        return $this->success();

        //发送邮件
//        $to = 'alan025.infinity@gmail.com';
//        $subject = '我是标题';
//        $body = '我是内容';
//        $mail_service = new MailService();
//        $status = $mail_service->send($to, $subject, $body);
//        if($status < 0) {
//            return $this->error($mail_service->get_err_msg($status), $status);
//        }
//        return $this->success();

        //测试队列
//        $data = [
//            'phone'  => '886958035350',
//            'code'   => rand(100000, 999999),
//        ];
//        for($i = 0; $i < 2; $i++) {
//            Queue::push(SmsJob::class, $data, $queue = 'sms');
//        }
//        return $this->success();

//        $data = [
//            'to'        => 'alan025.infinity@gmail.com',
//            'subject'   => '我是标题',
//            'body'      => '我是内容',
//        ];
//        for($i = 0; $i < 5; $i++) {
//            Queue::push(MailJob::class, $data, $queue = 'mail');
//        }
//        return $this->success();

//        $data = [
//            'token' => 'fNrBZGpWQe-YNLRaU_K-_c:APA91bFjXKh-IpWch7eKC5r1lsKm05EpjSBS-wAWzVtaJiWzoM9yPJ-xMMpNEnL3u7AXo4ez7xK4141sWDHNAOFsfKh5ZmU6j1sY02M-rkjCWuO3X6BqUwI',
//            'title' => '测试3',
//            'body'  => '测试测试测试',
//            'image' => 'https://muki.tw/favicon.ico',
//            'data'  => [
//                'k1' => 'v1',
//            ],
//        ];
//        //database 驱动时，返回值为 1|false  ;   redis 驱动时，返回值为 随机字符串|false
//        Queue::push(PushJob::class, $data, $queue = 'push');
//        return $this->success();

        //测试短信
//        $phone = '886958035350';
//        //$phone = '17403780452';
//        $code = rand(100000, 999999);
//        $type = $this->request->param('type');
//        $strategy = SmsFactory::strategy($type); //选择策略
//        $smsContext = new SmsContext($strategy);
//        $status = $smsContext->send($phone, $code);
//        if($status < 0) {
//            return $this->error($strategy->get_err_msg($status), $status);
//        }
//        return $this->success();

        //测试支付
//        $data = [
//            'order_id'      => '2506101219447486507',
//            'trade_no'      => '2025061012194580284',
//            'query_url'     => 'https://deal.sbsdsdbaba.xyz/v3/server/order',
//            'merchant_id'   => 'MOTV',
//            'secret'        => 'A948C149766C1E7B2AFDD0F96FD7459E',
//        ];
//        $strategy = PayFactory::strategy('CY'); //选择策略
//        $payContext = new PayContext($strategy);
//        $status = $payContext->order_query($data, $ret_data);
//        if($status < 0) {
//            return $this->error($strategy->get_err_msg($status), $status);
//        }
//        return $this->success($ret_data);

//        $data = [
//            'order_id'      => '2507151011058142405',
//            'pay_url'       => 'https://deal.sbsdsdbaba.xyz/v3/server/deal',
//            'notify_url'    => 'https://api.motvapp.com/api/v1/callbackCY', //异步回调地址
//            'return_url'    => 'https://motvapp.com/payok',                 //支付成功跳转地址
//            'merchant_id'   => 'MOTV',
//            'secret'        => 'A948C149766C1E7B2AFDD0F96FD7459E',
//            'channel_code'  => 'ALIPAY_SCAN',
//            'amount'        => 10,
//            'product_name'  => '1',
//            'uid'           => 'ff4cfb72c2fbab338493b6722c494362',
//            'member_no'     => '110724042',
//        ];
//        $strategy = PayFactory::strategy('CY'); //选择策略
//        $payContext = new PayContext($strategy);
//        $status = $payContext->pay($data, $ret_data);
//        if($status < 0) {
//            return $this->error($strategy->get_err_msg($status), $status);
//        }
//        return $this->success($ret_data);

        //des加密与解密
//        $data = 'kosplay';
//        $res = util::encrypt($data);
//        pr($res);

//        $data = '7vQqHjiXYmk=';
//        $res = util::decrypt($data);
//        pr($res);

        //发送tg
//        $chat_id = 1482669960;
//        $text = '测试用';
//        $tgService = new TgService();
//        $status = $tgService->send($chat_id, $text, $ret_data);
//        if($status < 0) {
//            return $this->error($tgService->get_err_msg($status), $status);
//        }
//        return $this->success($ret_data);

//        $data = [
//            'chat_id' => 1482669960,
//            'text' => '测试用',
//        ];
//        Queue::push(TgJob::class, $data, $queue = 'tg');
        //return $this->success();

        // 遇锁立刻返回
//        if(!RedisLock::lock('test', 12, 2))
//        {
//            return $this->error('获取锁失败', -1);
//        }
//        //业务
//        RedisLock::unlock('test');
//        return $this->success();

        //添加
        //方法1
        $ad_model = new AdModel();
        $ad_model->title     = '我是广告';
        $ad_model->save();
        $last_insert_id = $ad_model->id; //获取自增id

        //方法2
//        $data = [
//            'title' => '我是广告'
//        ];
//        $ad_model = new AdModel();
//        $ad_model->save($data);

        //方法3(推荐使用)
//        $ad_model = AdModel::create([
//            'title' => '我是广告'
//        ]);
//        $last_insert_id = $ad_model->id; //获取自增id

        //批量添加或更新(带主键字段)
        //方法1
//        $ad_model = new AdModel;
//        $data = [
//            ['title' => '我是广告1'],
//            ['title' => '我是广告2']
//        ];
//        $ad_model->saveAll($data);

//        $ad_model = new AdModel;
//        $data = [
//            ['id' => 7, 'title' => '我是广告11'],
//            ['id' => 8,'title' => '我是广告22']
//        ];
//        $ad_model->saveAll($data);

        //更新
        //方法1
//        $data = [
//            'title' => '我是广告11'
//        ];
//        $ad_model = new AdModel;
//        $ad_model->save($data, ['id' => 1]);

        //方法2(推荐使用)
//        $data = [
//            'title' => '我是广告22'
//        ];
//        AdModel::where('id', '=', 1)->update($data);

        //真删除
        //AdModel::destroy(1);
        //AdModel::destroy([2, 3]);
        //AdModel::destroy([]);
        //AdModel::destroy(0);
        //AdModel::where('id', '=', 4)->delete();

        //软删除
        //AdModel::destroy(5);
        //AdModel::where('id', '=', 6)->delete(); //该方法无效

        //获取单条
//        $ad = AdModel::find(7);
//        $ad = empty($ad) ? [] : $ad->toArray();
//        $ad = AdModel::where('title', '=', '2222我是广告')->find();
//        $ad = empty($ad) ? [] : $ad->toArray();

        //获取列表
        //$ad = AdModel::where('status', 1)->limit(10)->order('id', 'desc')->select();
        //$ad = AdModel::where('title', '我是广告')->select();
        //$ad = AdModel::where('title', '我是广告')->getLastSql(); //打印sql语句
        //$ad = AdModel::getByTitle('我是广告'); //getBy固定 字段名首字母大写

        //获取分页
//        $page = 1;
//        $page_size = 2;
//        //$where['status'] = ['=', 1];
//        $where = [
//            'status' => 1
//        ];
//        $order_by = [
//            'id' => 'desc'
//        ];
//        $offset = ($page - 1) * $page_size;
//        $ad = AdModel::where($where)->limit($offset , $page_size)->order($order_by)->field('id, title')->select();
//        //获取总条数
//        $count = AdModel::where($where)->count();
//        $res = [
//            'count' => $count,
//            'list' => $ad
//        ];

        //获取某字段值
//        $where = [
//            'id' => 6
//        ];
//        $ad_set_id = AdModel::where($where)->value('ad_set_id');
//        $res = ['ad_set_id' => $ad_set_id];

        //获取某字段值, 读取主库, 刚写入数据之后，从库数据还没来得及同步, 开启自动主库读取 'read_master' => true
//        $ad_model = new AdModel();
//        $ad_model->title     = '我是广告333';
//        $ad_model->save();
//        $where = [
//            'id' => $ad_model->id
//        ];
//        $res = AdModel::master()->where($where)->find();

        //获取最大值
//        $where = [
//            'status' => 1
//        ];
//        $create_time = AdModel::where($where)->max('create_time');
//        $res = ['create_time' => $create_time];

        // 启动事务
//        Db::startTrans();
//        try {
//            //执行多个数据库操作
//            AdModel::destroy(6);
//            AdModel::where('id', 6)->setInc('weight', 10);
////            Db::table('ad')->find(5);
////            Db::table('ad')->delete(6);
//
//            // 提交事务
//            Db::commit();
//        } catch (\Exception $e) {
//            pr(1);
//            // 回滚事务
//            Db::rollback();
//        }

        //单个表事务
//        Db::startTrans();
//        try {
//            //共享锁（用于读取，防止修改）, 排他锁（用于读取和修改，阻止其他事务的读取和修改）
//            //for update nowait 锁住表或者锁住行，只允许当前事务进行操作（读写），其他事务被拒绝，事务占据的statement连接也会被断开
//            //lock()调用会在sql语句后面，加上 for update, lock生效过程，其他并发请求的Update操作都会出于阻塞，等待的状态.
//            //明确指定主键，并且有此记录，行级锁
//            //$res = AdModel::lock(false)->where('id', 6)->field('id, title')->find();
//            //明确指定主键/索引，若查无此记录，无锁
//            //$res = AdModel::lock(true)->where('id', 16)->field('id, title')->find();
//            //无主键/索引，表级锁。影响非常大
//            $res = AdModel::lock(false)->where('title', '我是广告')->field('id, title')->select();
//            //主键/索引不明确，表级锁。影响非常大
//            //$res = AdModel::lock(true)->where('title', 'like', '%我是广告%')->field('id, title')->select();
//            //sleep(3);  //休眠3秒, id=6的行 会锁表3秒
//            // 提交事务
//            Db::commit();
//        } catch (\Exception $e) {
//            // 回滚事务
//            Db::rollback();
//        }

        //跳转到外部地址
        //return redirect('https://www.baidu.com');

        return $this->success();
    }
}
