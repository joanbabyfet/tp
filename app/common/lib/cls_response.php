<?php

namespace app\common\lib;

/**
 * 响应类
 */
class cls_response
{
    const SUCCESS = 0;
    const ERROR = -1;               //通用错误码(不可预知错误提示，或连接异常时)
    CONST SUCCESS_MSG = 'success';
    CONST ERROR_MSG = 'error';
    const USER_INFO_ERR     = -101;  // 用户信息错误
    const SYS_MAINTAIN      = -1001; // 维护中
    const SYS_IS_BUSY       = -1002; // 系统繁忙,请稍后重试
    const SYS_PARAMS_ERROR  = -1003; // 请求参数格式错误
    const SYS_LOGIN_FAIL    = -1004; // 登录失败！请重试
    const SYS_TOKEN_INVALID = -1005; // token失效
    const SYS_DATA_ERROR    = -1006; // 数据错误
    const SYS_NO_PERMISSION = -1007; // 无权限
    const THIRD_EXCEPTION   = -1098; // 三方异常
    const UNKNOWN_ERROR   = -1099; // 未知错误
}
