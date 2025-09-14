<?php
declare (strict_types = 1);

namespace app\model;

use app\common\lib\Util;
use think\model\concern\SoftDelete;

/**
 * @mixin \think\Model
 */
class AdSetModel extends BaseModel
{
    use SoftDelete;
    protected $pk = 'id'; //设置主键
    protected $table = 'tp_ad_set'; //设置表名
    protected $deleteTime = 'delete_time'; //软删除字段
    protected $defaultSoftDelete = 0; //软删除字段默认值
    public const STATUS_TEXT = [0=>'禁用', 1=>'启用'];
    public function getCreateTimeAttr($value) { //获取器, 格式：get字段名(首字母大写)Attr
        return Util::datetime($value, 'Y-m-d H:i');
    }
    public function getUpdateTimeAttr($value) { //获取器, 格式：get字段名(首字母大写)Attr
        return Util::datetime($value, 'Y-m-d H:i');
    }
    public function getDeleteTimeAttr($value) { //获取器, 格式：get字段名(首字母大写)Attr
        return Util::datetime($value, 'Y-m-d H:i');
    }
}
