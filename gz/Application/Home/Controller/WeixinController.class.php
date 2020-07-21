<?php

/*

微信接口

*/
namespace Home\Controller;
use Think\Controller;
class WeixinController extends Controller

{

	private $token; 

	private $data=array(); 

	private $my='大德信息';

	public function index()

	{
		$this->siteUrl='http://'.$_SERVER['HTTP_HOST'];
	    $token=!empty($_GET['token']) ? trim($_GET['token']) : '';
		$this->token=$token;
		$weixin = new \Think\Wechat($this->token);

		$data = $weixin->request();

		$this->data = $weixin->request();

		list($content, $type) = $this->reply($data);

		$weixin->response($content, $type);
        
	}

	private function reply($data)

	{
	  

		if ('CLICK' == $data['Event']) {

		$data['Content'] = $data['EventKey'];

		}elseif ('voice' == $data['MsgType']) {

		$data['Content'] = $data['Recognition'];

		}elseif ('unsubscribe' == $data['Event']) {
		
           $wxuser=D('wxuser');
		   $fdata['is_focus']=0;
		   $wxuser->where("wx_user='".$data['FromUserName']."'")->save($fdata);

		}elseif ('subscribe' == $data['Event']) {

		 $wxreply=M('wx_reply');
		 
		 $wxuser=D('wxuser');
		 
		 $count=$wxuser->where("wx_user='".$data['FromUserName']."'")->count();
		 if($count == 0)
		 {
		    $fdata['wx_user']=$data['FromUserName'];
		    $fdata['gz_user']=substr($data['EventKey'],8);
		    $fdata['add_time']=$data['CreateTime'];
			$wxuser->add($fdata);
		 }else
		 {
			$fdata['is_focus']=1;
		    $wxuser->where("wx_user='".$data['FromUserName']."'")->save($fdata);
		 }
		 $follow_data=$wxreply->where("token='".$this->token."'")->find();

		 return array(html_entity_decode($follow_data['content']),'text');

		}
		
		$key=$data['Content'];
		if($key!='')
		{
		return $this->keyword($key);
		}

	}
	public function keyword($key)
	{
		switch($key)
		{
			case '商城':
			$pro=M('replyinfo')->where(array('infotype'=>'shop','token'=>$this->token))->find();
			$url=$this->siteUrl.'/index.php?g=Wap&m=Store&a=index';
			if ($pro['apiurl'])
			{ 
		     $url=str_replace('&amp;','&',$pro['apiurl']);
			}
			return array(array(array($pro['title'],strip_tags(htmlspecialchars_decode($pro['info'])),$this->siteUrl.C('Uploads_Path').$pro['picurl'],$url)),'news');
			break;
			case '代言名片':
			return $this->tuiguang($this->data['FromUserName']);
			break;
			default:
			$Keywordreply=M('Keywordreply');
			$count=$Keywordreply->where("is_show=1 and keyword like '%$key%'")->count();
			if($count > 0)
			{
				$result=$Keywordreply->where("is_show=1 and keyword='$key'")->find();
				if($result['typeid']==1)
				{
					return array($result['content'],'text');
				}else
				{
					$material=M('material');
					$row=$material->where("typeid=".$result['con_id'])->order('orderby asc')->limit('0,'.$result['limit_s'])->select();
					$res=array();
					foreach($row as $key=>$home)
					{
						$res[] = array($home['title'],$home['description'],$this->siteUrl.C('Uploads_Path').$home['img_thumb'],$home['news_url']);
					}
					return array($res,'news');
				}
			}else
			{
				return array('对不起，您输入的关键词不能识别！','text'); 
			}
		}
	}
	public function tuiguang($wxusername)
	{
		
		$member=D('member');
		$user=$member->where("wx_user='".$wxusername."'")->find();
		if($user['tuig_id']!='')
		{
			return array(array($user['tuig_id']),'image');
		}else
		{
			return $this->shengcheng($wxusername);
			//return array('对不起，您输入的关键词不能识别！','text');
		} 	
	}
	
		public function shengcheng($wxusername)
	{
	 $wx_user=$wxusername;
	 
	
	 $member=D('member');
	 $this->wxuser=D('user')->where(array('id'=>'1'))->find();
	 $url_get='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->wxuser['appid'].'&secret='.$this->wxuser['appsecret'];
	 $json=json_decode($this->curlGet($url_get));
	 $access_token=$json->access_token;
	 $count=$member->where("wx_user='".$wx_user."'")->count();
	 if($count == 0)
	 {
		$user_rt=$this->curlGet('https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$wx_user);
		$user_jsonrt=json_decode($user_rt,1);
		$result=$member->field('user_uid')->order('id desc')->find();
		$data['user_uid']=$result['user_uid']+1;
		$data['wx_user']= $user_jsonrt['openid'];
		$data['nickname']= $user_jsonrt['nickname'];
		$data['headimgurl']= $user_jsonrt['headimgurl'];
		$data['add_time']= date('Y-m-d H:i:s');
		$url = $data['headimgurl'];
		$saveName = $data['wx_user'].'.jpg';
		$path = C('Uploads_Path');
		$data['mem_img']=$saveName;
		$this->put_file_from_url_content($url,$saveName,$path);
		$count=$member->where("wx_user='".$wx_user."'")->count();
		if($count==0)
		{
		$member->add($data);
		}
	 }
 
		$user=$member->where("wx_user='".$wx_user."'")->find();
		if($user['ticket']=='')
		{
		//$data='{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$wx_user.'"}}}';
		
		$datat.='{';
		$datat.='"action_name": "QR_LIMIT_STR_SCENE",';
		$datat.='"action_info": {';
		$datat.='"scene": {';
		$datat.='"scene_str": "'.$wx_user.'"';
		$datat.='}';
		$datat.='}';
		$datat.='}';
		
		
		$url='https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
	
		$rt=$this->api_notice_increment($url,$datat);
		
		$jsonrestt=json_decode($rt,1);
		
		$ticket=UrlEncode($jsonrestt['ticket']);
		$datt['ticket']=$ticket;
		$result='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
		$saveName = $wx_user.'_tg.jpg';
		$path = C('Uploads_Path');
		$datt['memtg_img']=$saveName;
		
		$this->put_file_from_url_content($result,$saveName,$path);
		//生成推广图片
		
		$pathname=C('Uploads_Path').'tgt/'.$wx_user.'.jpg';
		$datt['tuig_img']=$pathname;
		
		$imgs = C('Uploads_Path').$saveName;
		$imgsc = C('Uploads_Path').$user['mem_img'];
		$target = C('Uploads_Path').'tuiguang.jpg';//背景图片
		$target_img = imagecreatefromjpeg($target);
		$source=imagecreatefromjpeg($imgs);
		$sourcesize=getimagesize($imgs);

		$sourcec=imagecreatefromjpeg($imgsc);
		$sourcesizec=getimagesize($imgsc);

		$textcolor=imagecolorallocate($target_img,236,253,24);
		$font =C('Uploads_Path').'msyhbd.ttf';
		imagettftext($target_img,25,0,267,81, $textcolor, $font,$user['nickname']);

		imagecopyresized($target_img,$source,175,615,0,0,290,290,$sourcesize[0],$sourcesize[1]);
		imagecopyresized($target_img,$sourcec,27,24,0,0,145,145,$sourcesizec[0],$sourcesizec[1]);
		imagejpeg($target_img,$pathname);

		
		
		
		$hosturl=$_SERVER['DOCUMENT_ROOT'];
		$type='image';
		$filepath=$hosturl.substr($pathname,1,500);
		$filedata=array('media'=>"@".$filepath);
		$url="http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=".$json->access_token."&type=".$type;
		
		$rt=$this->api_notice_increment($url,$filedata);
		
		$jsonrestt=json_decode($rt,1);
		$datt['tuig_id']=$jsonrestt['media_id'];
		$member->where("wx_user='".$wx_user."'")->save($datt);
		return array(array($datt['tuig_id']),'image');
	}
	}
	
	function curlGet($url){
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$temp = curl_exec($ch);
		return $temp;
	}
	
	function api_notice_increment($url, $data){
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$tmpInfo = curl_exec($ch);
		//$errorno=curl_errno($ch);
		//print_r($tmpInfo);
		curl_close($ch);
		return $tmpInfo;
		
	}
		function put_file_from_url_content($url, $saveName, $path) {
    // 设置运行时间为无限制
		set_time_limit ( 0 );  
		$url = trim ( $url );                                   	
		$curl = curl_init ();
		// 设置你需要抓取的URL
		curl_setopt ( $curl, CURLOPT_URL, $url );
		// 设置header
		curl_setopt ( $curl, CURLOPT_HEADER, 0 );
		// 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		// 运行cURL，请求网页
		$file = curl_exec ( $curl );
		// 关闭URL请求
		curl_close ( $curl );
		// 将文件写入获得的数据
		$filename = $path . $saveName;
		$write = @fopen ($filename, "w");
		if ($write == false) {
			return false;
		}
		if (fwrite ( $write, $file ) == false) {
			return false;
		}
		if (fclose ( $write ) == false) {
			return false;
		}
	}

}

?>