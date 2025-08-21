<?php

namespace app\service\pay\strategy;

use app\service\BaseService;
use app\service\pay\PayStrategy;
use GuzzleHttp\Client;
use think\facade\Lang;
use think\facade\Log;
use think\facade\Validate;

class CYStrategy extends BaseService implements PayStrategy
{
    /**
     * 下单
     * @param array $data
     * @param $ret_data
     * @return int
     */
    public function pay(array $data, &$ret_data=[])
    {
        //参数过滤
        $validate = Validate::rule([
            'order_id'      => 'require|string',
            'pay_url'       => 'require|string',
            'notify_url'    => 'require|string', //异步回调地址
            'return_url'    => 'string',         //支付成功跳转地址
            'merchant_id'   => 'require|string',
            'secret'        => 'require|string',
            'channel_code'  => 'require|string',
            'amount'        => 'require',
            'product_name'  => 'require|string',
            'uid'           => 'require|string',
            'member_no'     => 'require|string',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), -2);
            }

            //组装请求参数
            $param = [
                'appId'             => $data['merchant_id'], //账户id
                'orderId'           => $data['order_id'],    //上送订单号
                'notifyUrl'         => $data['notify_url'],  //回调url
                'pageUrl'           => $data['return_url'],   //支付成功跳转地址
                'amount'            => $data['amount'],      //付款金额
                'applyDate'         => date('YmdHis'), //请求时间【格式：yyyymmddhhmmss】
                'passCode'          => $data['channel_code'],  //通道编码
                'mcPayName'         => $data['member_no'],   //付款人姓名(这里用会员号, 后台方便查)
            ];
            //签名
            $param['sign'] = $this->sign($param, $data['secret']);
            //拉单
            $headers = ['Content-Type: application/json'];

            $client = new Client();
            $res = $client->post($data['pay_url'], [
                'headers' => $headers,
                'json' => $param,
            ]);
            if(empty($res->getBody())) {
                $this->exception('请求失败', -2);
            }

            $arr = json_decode($res->getBody(), true);
            if($arr['code'] !== 0) //0=下单成功, 反之下单失败
            {
                $arr['code'] = $arr['code'] ?? '';
                $this->exception("数据验证失败 [CODE={$arr['code']}]", -3);
            }

            $plf_order_info = $arr['data'];
            $ret_data = [
                'trade_no'  => isset($plf_order_info['orderId']) ? $plf_order_info['orderId'] : '', //支付网关交易号
                'pay_url'   => isset($plf_order_info['info']['payUrl']) ? $plf_order_info['info']['payUrl'] : '', //收银台地址
            ];
        }
        catch (\Exception $e) {
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

    /**
     * 使用json格式接收，不应使用传统接收form表单的格式(application/x-www-form-urlencoded)接收
     * 默认情况下，支付失败不回调，只回调支付成功
     * @param array $data
     * @param $secret
     * @param $ret_data
     * @return int
     * @throws \Exception
     */
    public function callback(array $data, $secret, &$ret_data=[])
    {
        $status = 1;
        try {
            //打印(第三方回调)

            //检测签名
            if ($data['sign'] != $this->sign($data, $secret)) {
                $this->exception('数据签名验证不通过', -1);
            }
            //支付网关的支付状态 1=审核中 2=成功 4=失败
            if ($data['status'] != 2) {
                $this->exception('异步通知状态错误', -2);
            }
            $ret_data = [
                'order_id'      => $data['apporderid'], //我方订单号
                'trade_no'      => $data['tradesno'], //交易号
                'notify_result' => 'success',
            ];
        }
        catch (\Exception $e) {
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

    /**
     * 订单查询
     * @param array $data
     * @param $ret_data
     * @return int
     */
    public function order_query(array $data, &$ret_data=[])
    {
        $validate = Validate::rule([
            'order_id'      => 'require|string',
            'trade_no'      => 'string',
            'query_url'     => 'require|string',
            'merchant_id'   => 'require|string',
            'secret'        => 'require|string',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), -1);
            }

            //组装请求参数
            $param = [
                'userId'    => $data['merchant_id'], //商户号
                'orderId'   => $data['order_id'],    //上送订单号
            ];
            //签名
            $param['sign'] = $this->sign($param, $data['secret']);
            //订单查询
            $headers = ['Content-Type: application/json'];

            $client = new Client();
            $res = $client->post($data['query_url'], [
                'headers' => $headers,
                'json' => $param,
            ]);
            if(empty($res->getBody())) {
                $this->exception('请求失败', -2);
            }

            $arr = json_decode($res->getBody(), true);
            if(!isset($arr['code']) || $arr['code'] !== 0) //0 为成功，此状态仅作为判断是否请求成功，不代表是否付款成功
            {
                $this->exception('数据验证失败', -3);
            }

            $plf_order_info = $arr['data'];
            $ret_data = [
                'order_id'      => $plf_order_info['orderNo'], //我方订单号
                'trade_no'      => $plf_order_info['orderId'], //平台交易流水号
                'trade_state'   => $plf_order_info['orderStatus'] == '2' ? 1 : 0, //1=审核中 2=成功 4=失败 其他=出款中
                'response_info' => $plf_order_info //平台响应数据
            ];
        }
        catch (\Exception $e) {
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

    /**
     * 签名
     * @param array $data
     * @param $app_key
     * @param $exclude
     * @return string
     */
    private function sign(array $data, $app_key, $exclude = ['sign'])
    {
        //干掉sign参数
        if (!empty($exclude) && is_array($exclude))
        {
            foreach ($exclude as $key)
            {
                unset($data[$key]);
            }
        }
        ksort($data); //依键名做正序

        $query_str = http_build_query($data); //转成 a=xxx&b=xxx
        $query_arr = explode('&', $query_str);
        //由于http_build_query会对参数进行一次urlencode，所以这里需要加多一层urldecode
        $query_arr = array_map(function ($item) {
            return urldecode($item); //例：%E6%9D%8E%E8%81%B0%E6%98%8E => 李聰明
        }, $query_arr);

        $sign_text = implode('&', $query_arr);
        $sign_text .= $app_key;
        return strtolower(md5($sign_text)); //md5不支持解密回原来字符串
    }
}
