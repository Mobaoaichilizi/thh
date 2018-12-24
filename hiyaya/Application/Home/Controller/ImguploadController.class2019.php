<?php
namespace Home\Controller;
use Think\Controller;
class ImguploadController extends Controller {
	public function _initialize() {
		$map    = array('status' => 1);
			$data   = M('Config')->where($map)->field('type,name,value')->select();
			$config = array();
			if($data && is_array($data)){	
				foreach ($data as $value) {
						$config[$value['name']] = $value['value'];
				}
			}
			
			C($config); //添加配置

		//购物车
	}
    public function upload(){
		$serverid = I('post.serverid');
		$access_token=get_access_token(C('WX_APPID'),C('WX_APPSECRET'));
		if($access_token)
		{
			if (!is_dir('./Uploads/Weixin/'.date("Y-m-d"))){ 
			$res=mkdir('./Uploads/Weixin/'.date("Y-m-d"),0777,true); 
			if (!$res){
				$result['code']=0;
				$result['message']='目录创建失败!';
				outJson($result);
			}	
			}
			$targetName='./Uploads/Weixin/'.date("Y-m-d").'/'.uniqid().'.jpg';
            $url="http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=".$access_token->access_token."&media_id=".$serverid;
            $ch=curl_init($url);
            $fp=fopen($targetName, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
			unset($data);
			$targetName=substr($targetName, 1);
			$data['code']=1;
			$data['message']="上传成功！";
			$data['info']=$targetName;
			outJson($data);
		}else
		{
				unset($data);
				$data['code']=0;
				$data['message']="获取access_token失败";
				outJson($data);
		}			
	}
   
}