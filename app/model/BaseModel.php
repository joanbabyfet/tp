<?php
declare (strict_types = 1);

namespace app\model;

use think\facade\Db;
use think\Model;

/**
 * @mixin \think\Model
 */
class BaseModel extends Model
{
    public function InsertOrUpdate(array $data, $update_fields)
    {
        $table = $this->getTable();
        $fields = array_keys($data);
        $values = array_values($data);

        $placeholders = array_fill(0, count($fields), '?');
        $field_str = implode(', ', array_map(function($field) { return "`{$field}`"; }, $fields));
        $value_str = implode(', ', $placeholders);

        $update_condition = '';
        if (is_array($update_fields)) {
            $temp = [];
            foreach ($update_fields as $field) {
                $temp[] = "`{$field}` = VALUES(`{$field}`)";
            }
            $update_condition = implode(', ', $temp);
        } elseif (is_string($update_fields)) {
            $update_condition = $update_fields; // Raw SQL string for update
        }

        $sql = "INSERT INTO {$table} ({$field_str}) VALUES ({$value_str}) ON DUPLICATE KEY UPDATE {$update_condition}";
        return Db::execute($sql, $values);
    }
}
