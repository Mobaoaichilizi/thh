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
		
		include_once(VENDOR_PATH."vendor/autoload.php");
		$oss=new \OSS\OssClient('LTAIbcaJ17DMTSFr','XKR5IlIYBP8cETMHhFHQwsEpCuYowL','http://sxgzzyy.oss-cn-shanghai.aliyuncs.com',true);
		$type=strtolower(strstr($picname,'.'));
		if ($type!=".png"&&$type!=".jpg"&&$type!=".jpeg")
		{
			unset($data);
			$data['code']=0;
			$data['message']="只支持png和jpg两种格式！";
			outJson($data);	
		}
		else
		{
			$rand=rand(1000,9999);
			$filename=time().$rand;
			
			$pic_path="oss/".date("Y-m-d")."/".$filename.$type;
			$oss->uploadfile('sxgzzyy',$pic_path,$_FILES['pic']['tmp_name']);
		}


		$Upload = new Upload($setting,$pic_driver, $config = null);
		
		$info   = $Upload->upload($_FILES);
		dump($_FILES);
		 if($info){
			foreach ($info as $key => &$value) {
                /* 记录文件信息 */

                $value['path'] = substr($setting['rootPath'], 1).$value['savepath'].$value['savename'];	//在模板里的url路径
            }
            $return['data'] = $info['download'];
            $return['info'] = $info['download']['name'];
           dump($return);
        } else {
            $return['status'] = 0;
            $return['info']   = $Upload->getError();
        }

        /* 返回JSON数据 */

        $this->ajaxReturn($return);
		
		
	}

}
?>