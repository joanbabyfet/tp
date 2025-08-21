<?php
declare (strict_types = 1);

namespace app\middleware;

class IpFilterMiddleware
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
        // 如果IP在黑名单里面，而且不在白名单里面
        if (in_array($request->ip(), config('myconfig.ip_blacklist')) && !in_array($request->ip(), config('myconfig.ip_whitelist')))
        {
            return \json([
                'code'      => ERROR,
                'msg'       => '您访问的页面不存在',
                'timestamp' => time(),
            ], 404);
        }
        return $next($request); //继续执行下一个中间件或路由
    }
}
