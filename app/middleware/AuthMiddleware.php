<?php
declare (strict_types = 1);

namespace app\middleware;

use app\common\lib\cls_auth;
use app\common\lib\cls_response;
use app\common\traits\ResponseJson;
use think\facade\Lang;

class AuthMiddleware
{
    use ResponseJson;

    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return \think\response\Json
     */
    public function handle($request, \Closure $next)
    {
        $auth = $request->header('authorization');
        if (empty($auth)) {
            return $this->invalid_params();
        }
        $token = str_replace('Bearer ','', $auth);
        $status = cls_auth::check_token($token, $ret_data);
        if($status < 0) {
            return $this->error(Lang::get('common_no_auth'), cls_response::SYS_TOKEN_INVALID);
        }
        if (!empty($ret_data['uid'])) {
            $request->auth = $ret_data['uid'];
        }
        // 继续执行下一个中间件或路由
        return $next($request);
    }
}
