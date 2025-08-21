<?php
declare (strict_types = 1);

namespace app\middleware;

use app\common\lib\Auth;
use app\common\lib\Response;
use think\exception\HttpResponseException;
use think\facade\Lang;

class AuthMiddleware
{
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
            return response::invalid_params();
        }
        $token = str_replace('Bearer ','', $auth);
        $status = Auth::checkToken($token, $ret_data);
        if($status < 0) {
            return response::error(Lang::get('common_no_auth'), response::INVALID_TOKEN);
        }
        if (!empty($ret_data['uid'])) {
            $request->auth = $ret_data['uid'];
        }
        // 继续执行下一个中间件或路由
        return $next($request);
    }
}
