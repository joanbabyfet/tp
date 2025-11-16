<?php
declare (strict_types = 1);

namespace app\model;

use app\common\lib\cls_util;
use think\Model;

/**
 * @mixin \think\Model
 */
class SendCodeModel extends BaseModel
{
    protected $pk = 'id'; //设置主键
    protected $table = 'tp_send_code'; //设置完整数据表名
    public function getCreateTimeAttr($value) { //获取器, 格式：get字段名(首字母大写)Attr
        return cls_util::datetime($value, null, 'Y-m-d H:i');
    }

    //来源映射
    public const SOURCE_MAP = [
        'spug'      => 1,
        'unimtx'    => 2,
        'gmail'     => 3,
    ];

    //数据格式化
    public function formatInfo($info)
    {
        return $info;
    }
}
