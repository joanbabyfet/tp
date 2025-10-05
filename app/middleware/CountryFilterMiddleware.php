<?php
declare (strict_types = 1);

namespace app\middleware;

use app\common\lib\cls_response;
use app\common\lib\cls_util;
use think\facade\Lang;

class CountryFilterMiddleware
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
        //$ip = $request->ip();
        $ip = '14.161.27.200'; //测试用(VN)
        $country = cls_util::ip2country($ip);
        // 如果国家在黑名单里面，而且不在白名单里面
        if ( in_array($country, config('config.country_blacklist')) && !in_array($country, config('config.country_whitelist')) )
        {
            // 国家有时候又判断错误的情况，可以把这些判断错误的IP过滤
            if ( in_array($ip, config('config.ip_whitelist')) )
            {
                return $next($request); //继续执行下一个中间件或路由
            }
            return \json([
                'code'      => cls_response::SYS_NO_PERMISSION,
                'msg'       => Lang::get('common_no_permission'),
                'timestamp' => time(),
            ], 403);
        }
        return $next($request); //继续执行下一个中间件或路由
    }
}
