<?php
declare (strict_types = 1);

namespace app\model;

use app\common\lib\cls_util;
use think\Model;

/**
 * @mixin \think\Model
 */
class UserLoginModel extends BaseModel
{
    protected $pk = 'id'; //设置主键
    protected $table = 'tp_user_login'; //设置完整数据表名
    public function getLoginTimeAttr($value) { //获取器, 格式：get字段名(首字母大写)Attr
        return cls_util::datetime($value, 'Y-m-d H:i');
    }
    //数据格式化
    public function formatInfo($info)
    {
        return $info;
    }
}
