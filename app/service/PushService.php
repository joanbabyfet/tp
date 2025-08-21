<?php
namespace app\service;

use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use think\facade\Log;

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
        $project_id     = config('myconfig.firebase.project_id'); //项目ID

        $status = 1;
        try {
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
            if(!empty($val['data'])) {
                $payload['message']['data'] = $val['data'];
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

    private function getAccessToken()
    {
        $keyPath = config('myconfig.firebase.key_path');
        putenv('GOOGLE_APPLICATION_CREDENTIALS='. $keyPath);
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        $auth = ApplicationDefaultCredentials::getCredentials($scopes);
        $token = $auth->fetchAuthToken();
        return $token['access_token'] ?? null;
    }
}
