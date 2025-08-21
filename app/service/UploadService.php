<?php
declare (strict_types = 1);

namespace app\service;

use think\facade\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class UploadService extends BaseService
{
    /**
     * 上传图片
     * @param array $data
     * @param array $ret_data
     * @return int|mixed
     */
    public function upload($file, array $data, &$ret_data=[])
    {
        $file_url = config('myconfig.file_url');

        $status = 1;
        try {
            $dir = $data['dir'] ?? 'image';
            $thumb_w = empty($data['thumb_w']) ? 0 : (int)$data['thumb_w'];
            $thumb_h = empty($data['thumb_h']) ? 0 : (int)$data['thumb_h'];
            $max_size = 1024 * 1024 * config('myconfig.upload_max_size'); //5M默认，这里只是上传限制，还需要修改你的php.ini文件
            $dir_num = config('myconfig.dir_num'); //上传目录数量
            $upload_dir = app()->getRootPath() . 'public/uploads'."/{$dir}";

            // 目录不存在则生成
            if (!$this->path_exists($upload_dir))
            {
                $this->exception('保存目录不存在', -2);
            }

            $filesize = $file->getSize(); //原文件大小
            $realname = $file->getOriginalName(); //原文件名 testimg.jpg
            $file_ext = $file->getOriginalExtension();  //扩展名 jpg
            $tmp_name  = $file->getRealPath(); //临时文件名 /Applications/MAMP/tmp/php/php1Z8ML9
            $allow_exts = explode('|', config('myconfig.allow_exts'));
            if (!in_array($file_ext, $allow_exts))
            {
                $this->exception('上传的文件格式不符合规定', -3);
            }

            // 判断文件大小
            if ($max_size != 0)
            {
                if ($filesize > $max_size)
                {
                    $this->exception('上传的文件太大', -4);
                }
            }

            //md5_file要给绝对定址, 否则会报错, 兼容可自定义文件名
            $filename = md5_file($tmp_name).'.'.$file_ext;

            // 如果需要分隔目录上传
            if ($dir_num > 0) {
                $dir_num = $this->str2number($filename, $dir_num);
                $this->path_exists($upload_dir.'/'.$dir_num);
                $filename = $dir_num.'/'.$filename;
            }
            else {
                $filename = $dir.'/'.$filename;
            }

            $dir_num = empty($dir_num) ? '':$dir_num;
            //將文件從暫存位置（由PHP設定來決定）移動至你指定的永久保存位置
            if ($file->move($upload_dir.'/'.$dir_num, $filename))
            {
                @chmod($upload_dir.'/'.$filename, 0777);

                //同步到s3
                $this->sync_upload($filename);

                $filelink = ($dir_num > 0) ? $file_url."/{$dir}/{$filename}" : $file_url."/{$filename}";
                if ($thumb_w > 0 || $thumb_h > 0)
                {
                    [$status, $filename, $filelink] = $this->thumb($upload_dir, $filename, $file_ext, $thumb_w, $thumb_h);

                    if($status < 0)
                    {
                        $this->exception('缩图保存目录不存在', -7);
                    }
                }

                //返回数据
                $ret_data = [
                    'realname' => $realname,
                    'filename' => $filename,
                    'filelink' => $filelink,
                ];
            }
        }
        catch (\Exception $e) {
            $status = $this->get_exception_status($e);
            //记录日志
            Log::error(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'args'    => func_get_args()
            ]);
        }
        return $status;
    }

    /**
     * 缩图
     * @param $upload_dir
     * @param $filename
     * @param string $file_ext
     * @param int $thumb_w
     * @param int $thumb_h
     * @return array
     */
    private function thumb( $upload_dir, $filename, $file_ext = 'jpg', $thumb_w = 0, $thumb_h = 0 )
    {
        $file_url = config('myconfig.file_url');
        $dir_num = config('myconfig.dir_num'); //上传目录数量
        $filelink = '';

        $status = 1;
        try
        {
            //$pathinfo = getimagesize($upload_dir.'/'.$filename);
            //$width  = $pathinfo[0]; //上傳圖片原始寬
            //$height = $pathinfo[1]; //上傳圖片原始高

            // 缩略图的临时文件名
            $filename_tmp = md5($filename).'.'.$file_ext;
            $upload_temp_path = app()->getRootPath() . 'public/uploads/temp';

            // 缩略图的临时目录不存在则生成
            if (!$this->path_exists($upload_temp_path))
            {
                $this->exception('缩图保存目录不存在', -5);
            }

            $manager = new ImageManager(new Driver());
            $img = $manager->read($upload_dir.'/'.$filename);
            if ( $thumb_w > 0 && $thumb_h > 0 ) {
                $img->resize($thumb_w, $thumb_h)->save($upload_temp_path.'/'.$filename_tmp);
            }
            elseif ( $thumb_w > 0 && $thumb_h == 0 ) {  // 只设置了宽度，自动计算高度，高度等比例缩放
                $img->scale(width: $thumb_w)->save($upload_temp_path.'/'.$filename_tmp);
            }
            elseif ( $thumb_h > 0 && $thumb_w == 0 ) {  // 只设置了高度，自动计算宽度，宽度等比例缩放
                $img->scale(height: $thumb_h)->save($upload_temp_path.'/'.$filename_tmp);
            }

            $filename = md5_file($upload_temp_path.'/'.$filename_tmp).".".$file_ext;

            // 如果需要分隔目录上传
            if ($dir_num > 0)
            {
                $dir_num = $this->str2number($filename, $dir_num);
                if (!$this->path_exists($upload_dir.'/'.$dir_num))
                {
                    $this->exception('缩图保存目录不存在', -6);
                }
                $filename = $dir_num.'/'.$filename;
            }

            //不同路徑的話，移動檔案並更名
            rename($upload_temp_path.'/'.$filename_tmp, "{$upload_dir}/{$filename}");

            $filelink = $file_url."/image/{$filename}";
        }
        catch (\Exception $e) {
            $status = $this->get_exception_status($e);
            //记录日志
            Log::error(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'args'    => func_get_args()
            ]);
        }
        return [$status, $filename, $filelink];
    }

    /**
     * 字符串转数字，用于分表和图片分目录
     * @param $str
     * @param $maxnum
     * @return string
     */
    private function str2number($str, $maxnum = 128)
    {
        // 位数
        $bitnum = 1;
        if ($maxnum >= 100)
        {
            $bitnum = 3;
        }
        elseif ($maxnum >= 10)
        {
            $bitnum = 2;
        }

        // sha1:返回一个40字符长度的16进制数字
        $str = sha1(strtolower($str));
        // base_convert:进制建转换，下面是把16进制转成10进制，方便做除法运算
        // str_pad:把字符串填充为指定的长度，下面是在左边加0，共 $bitnum 位
        $str = str_pad((string)base_convert(substr($str, -2), 16, 10), $bitnum, "0", STR_PAD_LEFT);
        return $str;
    }

    /**
     * 检查路径是否存在
     * @param $path
     * @return false
     */
    private function path_exists($path)
    {
        $pathinfo = pathinfo($path . '/tmp.txt');

        if ( !empty( $pathinfo ['dirname'] ) )
        {
            if (file_exists ( $pathinfo ['dirname'] ) === false)
            {
                //第2个参数目录权限为数字类型, 否则无效
                $mode = intval(0777, 8);
                if (@mkdir ( $pathinfo ['dirname'], $mode, true ) === false)
                {
                    return false;
                }
            }
        }
        return $path;
    }
}
