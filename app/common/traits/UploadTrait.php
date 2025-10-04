<?php

namespace app\common\traits;


trait UploadTrait
{
    /**
     * 上传图片
     * @return \think\response\Json
     */
    public function upload()
    {
        $file = request()->file('file');
        $param = request()->param();

        $status = $this->uploadService->upload($file, $param, $ret_data);
        if($status < 0) {
            return $this->error($this->uploadService->get_err_msg($status), $status);
        }
        return $this->success($ret_data);
    }
}
