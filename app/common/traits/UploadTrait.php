<?php

namespace app\common\traits;

use app\common\lib\Response;
use app\service\UploadService;

trait UploadTrait
{
    /**
     * 上传图片
     * @return \think\response\Json
     */
    public function upload()
    {
        $file = $this->request->file('file');
        $param = $this->request->param();

        $upload_service = new UploadService();
        $status = $upload_service->upload($file, $param, $ret_data);
        if($status < 0) {
            return response::error($upload_service->get_err_msg($status), $status);
        }
        return response::success('',0, $ret_data);
    }
}
