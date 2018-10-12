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
		
		
		
		include_once(VENDOR_PATH."vendor/autoload.php");
		$oss=new \OSS\OssClient('LTAIbcaJ17DMTSFr','XKR5IlIYBP8cETMHhFHQwsEpCuYowL','http://sxgzzyy.oss-cn-shanghai.aliyuncs.com',true);
		$type=strtolower(strstr($_FILES['download']['name'],'.'));
		
		if ($type!=".png" && $type!=".jpg" && $type!=".jpeg")
		{
	
			$return['status'] = 0;
			$return['info']="只支持png和jpg两种格式！";
			
		}
		else
		{
			$rand=rand(1000,9999);
			$filename=time().$rand;
			
			$pic_path="oss/".date("Y-m-d")."/".$filename.$type;
			$info = $oss->uploadfile('sxgzzyy',$pic_path,$_FILES['download']['tmp_name']);
			$return['data'] = array('path'=>$pic_path);
            $return['info'] = '上传成功！';
		}

        /* 返回JSON数据 */

        $this->ajaxReturn($return);
		
		
	}

}
?>