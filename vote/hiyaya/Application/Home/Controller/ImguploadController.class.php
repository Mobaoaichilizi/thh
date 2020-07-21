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
			//$base64_string='1.jpg';
			//$targetName=substr($targetName, 1);
			$base64_string=base64EncodeImage($targetName);
			$host = "http://idcardocr.market.alicloudapi.com";

				$path = "/id_card_ocr";

				$method = "POST";

				$appcode = "b640229a4e2e411281fc4160209dbfc7";

				$headers = array();

				array_push($headers,"Authorization:APPCODE ".$appcode);

				//根据API的要求，定义相对应的Content-Type

				array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");

				$querys = "";

				$bodys = "imgData=".urlencode($base64_string)."&type=1";

				$url = $host . $path;

				$curl = curl_init();

				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

				curl_setopt($curl, CURLOPT_URL, $url);

				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

				curl_setopt($curl, CURLOPT_FAILONERROR, false);

				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

				curl_setopt($curl, CURLOPT_HEADER, false);

				if (1 == strpos("$".$host, "https://"))

				{

					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

				}

				curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);

				$json_str=curl_exec($curl);

				$json_arr=json_decode($json_str,true);
			if($json_arr['showapi_res_body']['idNo'])
			{
				//$targetName=substr($targetName, 1);
				$data['code']=1;
				$data['message']="上传成功！";
				$data['info']=$json_arr;
				outJson($data);
			}else
			{
				unset($data);
				$data['code']=0;
				$data['message']="身份证不清晰！";
				outJson($data);
			}
		}else
		{
				unset($data);
				$data['code']=0;
				$data['message']="获取access_token失败";
				outJson($data);
		}			
	}
   
}