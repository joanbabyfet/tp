<?php
namespace app\common\service;

use app\common\lib\cls_response;
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use think\facade\Lang;
use think\facade\Validate;

class PushService extends BaseService
{
    /**
     * 发送推送信息
     * @param $data
     * @param $ret_data
     * @return int|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send($data, &$ret_data = [])
    {
        //参数过滤
        $validate = Validate::rule([
            'token'     => 'require|string',
            'title'     => 'require|string',
            'body'      => 'require|string',
            'image'     => 'require|string',
            'data'      => 'array',             //选填
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $project_id     = config('config.firebase.project_id'); //项目ID
            $fcmUrl = "https://fcm.googleapis.com/v1/projects/${project_id}/messages:send";
            $headers = [
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type'  => 'application/json',
            ];
            $payload = [
                'message' => [
                    'token' => $data['token'],
                    'notification' => [
                        'title' => $data['title'],
                        'body'  => $data['body'],
                        'image' => $data['image'],
                    ]
                ],
            ];
            if(!empty($data['data'])) {
                $payload['message']['data'] = $data['data'];
            }

            $client = new Client();
            $res = $client->post($fcmUrl, [
                'headers' => $headers,
                'json' => $payload,
            ]);
            if(empty($res->getBody())) {
                $this->exception('请求失败', -2);
            }

            $arr = json_decode($res->getBody(), true);
            $ret_data = $arr;
        }
        catch (\Exception $e) {
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

    private function getAccessToken()
    {
        $keyPath = config('config.firebase.key_path');
        putenv('GOOGLE_APPLICATION_CREDENTIALS='. $keyPath);
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        $auth = ApplicationDefaultCredentials::getCredentials($scopes);
        $token = $auth->fetchAuthToken();
        return $token['access_token'] ?? null;
    }
}
