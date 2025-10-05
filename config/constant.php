<?php
//公共常量

// 客户端语言
//defined('LANG') or define('LANG', req::language());
// 客户端国家
defined('COUNTRY') or define('COUNTRY', request()->country());
