<?php
// +----------------------------------------------------------------------
// | 二维码文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class QrcodeController extends DoctorbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->user = D('User');
		$this->member = M('Member');
		$this->uid = $this->user->where("md5(md5(id))='".$uid."'")->getfield("id");
		
	}
	

public function qrcode()
	{
		$uid = $this->uid;
		$info=$this->user->where("id=".$uid)->find();
		$info['openid'] = md5($uid);
		$img = $this->member->where("user_id=".$uid)->getField('img_thumb');
		include './Core/Library/Vendor/phpqrcode/phpqrcode.php';   
		$value = $uid; //二维码内容 
		// $value = md5($info['openid']) ; //二维码内容 
		  $errorCorrectionLevel = 'L';//容错级别  
		  $matrixPointSize = 15;//生成图片大小   //生成二维码图片   
		  \QRcode::png($value, './Uploads/Weixin/'.$info['openid'].'.png', $errorCorrectionLevel, $matrixPointSize, 2);  
		  $logo = 'http://sxgzzyy.oss-cn-shanghai.aliyuncs.com/'.$img;//准备好的logo图片 
		 
		  $QR = './Uploads/Weixin/'.$info['openid'].'.png';//已经生成的原始二维码图   
		  if ($logo !== FALSE) {      
		  $QR = imagecreatefromstring(file_get_contents($QR));   
		  $logo = imagecreatefromstring(file_get_contents($logo));    
		  $QR_width = imagesx($QR);//二维码图片宽度    
		  $QR_height = imagesy($QR);//二维码图片高度     
		  $logo_width = imagesx($logo);//logo图片宽度     
		  $logo_height = imagesy($logo);//logo图片高度    
		  $logo_qr_width = $QR_width / 5;     
		  $scale = $logo_width/$logo_qr_width;    
		  $logo_qr_height = $logo_height/$scale;    
		  $from_width = ($QR_width - $logo_qr_width) / 2;       //重新组合图片并调整大小   
		  imagecopyresampled($QR, $logo, 220, 1620,0, 0, 200,200, $logo_width, $logo_height);  
		  // }   //输出图片
		  // imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
			}
		  imagepng($QR,'./Uploads/Weixin/'.$info['openid'].'.png'); 
		  unset($data);
		  $data['ewm_img']='/Uploads/Weixin/'.$info['openid'].'.png';
		  M('Member')->where("user_id=".$uid)->save($data);
		  
		  $result = $this->member->Field('ewm_img,img_thumb,name')->where("user_id=".$uid)->find();
		  $result['share_number'] = $this->user->where('id='.$uid)->getField('share_number');
		  $result['ewm_img'] = $this->host_url.$result['ewm_img'];
		  if($result){
		  	unset($data);
		  	$data['code'] = 1;
		  	$data['message'] = "生成成功！";
		  	$data['info'] = $result;
		  	outJson($data);
		  }
		

	}


}
?>