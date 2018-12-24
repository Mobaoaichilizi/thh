<?php
// +----------------------------------------------------------------------
// | 文件上传配置
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Think\Upload;
class FileController extends AdminbaseController {
	
	public function uploadPicture()
	{
		$return  = array('status' => 1, 'info' => '上传成功', 'data' => '');
		
		$pic_driver = C('PICTURE_UPLOAD_DRIVER');
		$setting=C('PICTURE_UPLOAD');
		
		$Upload = new Upload($setting,$pic_driver, $config = null);
		
		$info   = $Upload->upload($_FILES);
		 if($info){
			foreach ($info as $key => &$value) {
                /* 记录文件信息 */

                $value['path'] = substr($setting['rootPath'], 1).$value['savepath'].$value['savename'];	//在模板里的url路径
            }
            $return['data'] = $info['download'];
            $return['info'] = $info['download']['name'];
        } else {
            $return['status'] = 0;
            $return['info']   = $Upload->getError();
        }

        /* 返回JSON数据 */

        $this->ajaxReturn($return);
		
		
	}

}
?>