<?php
// +----------------------------------------------------------------------
// | 图片上传接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class VideofileController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);//单点登录
		parent::_initialize();
		$this->information = D('User');
		$this->member = D('Member');
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	
public function upload_video(){
	$uid = $this->uid;
	$picname=$_FILES['pic']['name'];
	$picsize=$_FILES['pic']['size'];
	$service=$_POST['service'];
if($picname!="")
{
	include_once(VENDOR_PATH."vendor/autoload.php");
	$oss=new \OSS\OssClient('LTAIbcaJ17DMTSFr','XKR5IlIYBP8cETMHhFHQwsEpCuYowL','http://sxgzzyy.oss-cn-shanghai.aliyuncs.com',true);
	if($picsize>40960000000000)
	{
		unset($data);
		$data['code']=0;
		$data['message']="视频大小不能超过400M！";
		outJson($data);	
	}
	else
	{
		//$type=strtolower(strstr($picname,'.'));
			$type='.mp4';
			$rand=rand(1000,9999);
			$filename=time().$rand;
			if($service=='oss')
			{
				$pic_path="oss/".date("Y-m-d")."/".$filename.$type;
				$oss->uploadfile('sxgzzyy',$pic_path,$_FILES['pic']['tmp_name']);
			}
			else
			{
				$dir="./Uploads/oss/".date("Y-m-d")."/";				
				if(!is_dir($dir))
				{
					mkdir($dir,0777);
				}
				$pics=$filename.$type;
				//上传路径
				$pic_path=$dir.$pics;
				if(move_uploaded_file($_FILES['pic']['tmp_name'],$pic_path))
				{
					$pic_path=$pic_path;

				}
				else
				{
					unset($data);
					$data['code']=0;
					$data['message']="上传失败！";
					outJson($data);	
				}
			}
			
			unset($data);
			$data['code']=1;
			$data['message']="获取成功！";
			$data['pic']=$pic_path;
			$data['filename']=$filename;
			outJson($data);	
			
		
	}
}
else
{
	unset($data);
	$data['code']=0;
	$data['message']="请选择上传文件！";
	outJson($data);	
}
	
}		
}
?>