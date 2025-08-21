<?php
declare (strict_types = 1);

namespace app\middleware;

use think\facade\App;
use think\facade\Cookie;
use think\facade\Lang;

class LangMiddleware
{
    private $config;
    private $lang;
    public function __construct(App $app)
    {
        $this->config = config('lang');
        $this->lang = '';
    }

    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        $lang_set = $this->detect($request);

        if($this->lang != $lang_set) {
            $this->lang = $lang_set;
            //使用哪种语言
            Lang::setLangSet((string)$this->lang);
        }
        //$this->save_cookie($lang_set);
        return $next($request);
    }

    /**
     * 检测各参数使用哪种语言
     * @param $request
     * @return mixed|string
     */
    protected function detect($request)
    {
        $lang_set = '';

        if($request->get($this->config['detect_var'])) {                //获取GET参数 xxx.com/?lang=zh-cn
            $lang_set = $request->get($this->config['detect_var']);
        } elseif($request->header($this->config['header_var'])) {       //获取请求头参数 think-lang
            $lang_set = $request->header($this->config['header_var']);
        } elseif($request->cookie($this->config['cookie_var'])) {       //获取cookie参数 think_lang
            $lang_set = $request->cookie($this->config['cookie_var']);
        } elseif($request->server('HTTP_ACCEPT_LANGUAGE')) {            //获取请求头参数 Accept-Language
            $lang_set = $request->server('HTTP_ACCEPT_LANGUAGE');
        }

        //执行正则表达式匹配
        if(preg_match('/^([a-z\d\-]+)/i', $lang_set, $matches)) {
            $lang_set = strtolower($matches[1]);
            if(isset($this->config['accept_language'][$lang_set])) {
                $lang_set = $this->config['accept_language'][$lang_set];
            }
        } else {
            $lang_set = $this->config['default_lang'];
        }
        return $lang_set;
    }

    /**
     * 保存到cookie
     * @param $lang_set
     * @return void
     */
    private function save_cookie($lang_set)
    {
        if($this->config['use_cookie']) {
            Cookie::forever($this->config['cookie_var'], $lang_set);
        }
    }
}
