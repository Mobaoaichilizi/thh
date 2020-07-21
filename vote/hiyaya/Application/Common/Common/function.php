<?php
// +----------------------------------------------------------------------
// | 公共函数
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------


/**
 * 密码加密方法
 * @param string $pw 要加密的字符串
 * @return string
 */
function sp_password($pw,$authcode=''){
    if(empty($authcode)){
        $authcode=C("AUTHCODE");
    }
	$result=md5(md5($authcode.$pw));
	return $result;
}

/**
 * 验证码检查，验证完后销毁验证码增加安全性 ,<br>返回true验证码正确，false验证码错误
 * @param  int $id 验证码ID
 * @return boolean <br>true：验证码正确，false：验证码错误
 */
function sp_check_verify_code($verifycode,$id=1){
	$verify = new \Think\Verify();
	return $verify->check($verifycode,$id);
}

//加密
function encryptStr($str,$key='lovefat'){
  $block = mcrypt_get_block_size('des', 'ecb');
  $pad = $block - (strlen($str) % $block);
  $str .= str_repeat(chr($pad), $pad);
  $enc_str = mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
  return base64_encode($enc_str);
}

//解密
function decryptStr($str,$key='lovefat'){
  $str = base64_decode($str);
  $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
  $block = mcrypt_get_block_size('des', 'ecb');
  $pad = ord($str[($len = strlen($str)) - 1]);
  return substr($str, 0, strlen($str) - $pad);
}
/**
 *
 * 清空目录
 * @param $dir 目录路径
 * @param $virtual  是否标准化一下目录路径
 */
function destroy_dir($dir, $virtual = false)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") destroy_dir($dir."/".$object); else unlink($dir."/".$object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

/**
 * 判断是否为手机访问
 * @return  boolean
 */
function sp_is_mobile() {
	static $sp_is_mobile;

	if ( isset($sp_is_mobile) )
		return $sp_is_mobile;

	if ( empty($_SERVER['HTTP_USER_AGENT']) ) {
		$sp_is_mobile = false;
	} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false // many mobile devices (all iPhone, iPad, etc.)
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false ) {
		$sp_is_mobile = true;
	} else {
		$sp_is_mobile = false;
	}

	return $sp_is_mobile;
}

/**
 * 判断是否为微信访问
 * @return boolean
 */
function sp_is_weixin(){
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }
    return false;
}

/**
 * 检查权限
 * @param name string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
 * @param uid  int           认证用户的id
 * @param relation string    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
 * @return boolean           通过验证返回true;失败返回false
 */
function sp_auth_check($uid,$name=null,$relation='or'){
	if(empty($uid)){
		return false;
	}

	if(empty($name)){
		$name=strtolower(MODULE_NAME."/".CONTROLLER_NAME."/".ACTION_NAME);
	}
	$access=M('Access');
	$users=M('Admin');
	$users_list=$users->where("id=".$uid)->find();
	$auth_list=$access->where("role_id=".$users_list['role_id'])->select();
	foreach($auth_list as $rule)
	{
		$list[]=strtolower($rule['node_name']);
	}
	if(!in_array($name,$list) ){
		return false;
	}else{
		return true;
	}
}

/**
 * 前台检查权限
 * @param name string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
 * @param uid  int           认证用户的id
 * @param relation string    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
 * @return boolean           通过验证返回true;失败返回false
 */
function manager_sp_auth_check($uid,$name=null,$relation='or'){
	if(empty($uid)){
		return false;
	}

	if(empty($name)){
		$name=strtolower(MODULE_NAME."/".CONTROLLER_NAME."/".ACTION_NAME);
	}
	$access=M('ShopAccess');
	$users=M('ShopUser');
	$users_list=$users->where("id=".$uid)->find();
	$auth_list=$access->where("role_id=".$users_list['role_id']." and shop_id=".$users_list['shop_id'])->select();
	foreach($auth_list as $rule)
	{
		$list[]=strtolower($rule['node_name']);
	}
	if(!in_array($name,$list) ){
		return false;
	}else{
		return true;
	}
}


/**
 * API前台检查权限
 * @param name string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
 * @param uid  int           认证用户的id
 * @param relation string    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
 * @return boolean           通过验证返回true;失败返回false
 */
function api_sp_auth_check($uid,$name=null,$relation='or'){
	if(empty($uid)){
		return false;
	}

	if(empty($name)){
		$name=strtolower(MODULE_NAME."/".CONTROLLER_NAME."/".ACTION_NAME);
	}
	unset($data);
	$access=M('ShopApiaccess');
	$users=M('ShopUser');
	$users_list=$users->where("id=".$uid)->find();
	$auth_list=$access->where("role_id=".$users_list['role_id']." and shop_id=".$users_list['shop_id'])->select();
	foreach($auth_list as $rule)
	{
		$list[]=strtolower($rule['node_name']);
	}
	if(!in_array($name,$list) ){
		return false;
	}else{
		return true;
	}
}
/**
 * 对所有操作进行统计
 */
 function action_log($uid,$remark,$type=0)
 {
	$data=array(
		'user_id' => $uid,
		'type' => $type,
		'createtime' => time(),
		'remark' => $remark,
	);
	$result=M("log")->add($data);
	if($result!==false)
	{
		return true;
	}else
	{
		return false;
	}
 }
 
/**
 * 系统邮件发送函数
 * @param string $to    接收邮件者邮箱
 * @param string $name  接收邮件者名称
 * @param string $subject 邮件主题 
 * @param string $body    邮件内容
 * @param string $attachment 附件列表
 * @return boolean 
 */
function sendMail($to,$name,$title, $content) { 
        vendor('PHPMailer.class#phpmailer'); //从PHPMailer目录导class.phpmailer.php类文件
        $mail = new PHPMailer(); //实例化
        $mail->IsSMTP(); // 启用SMTP
        $mail->Host=C('MAIL_HOST'); //smtp服务器的名称（这里以QQ邮箱为例）
        $mail->SMTPAuth = C('MAIL_SMTPAUTH'); //启用smtp认证
        $mail->Username = C('MAIL_USERNAME'); //你的邮箱名
        $mail->Password = C('MAIL_PASSWORD') ; //邮箱密码
        $mail->From = C('MAIL_USERNAME'); //发件人地址（也就是你的邮箱地址）
        $mail->FromName = $name; //发件人姓名
        $mail->AddAddress($to,"尊敬的客户");
        $mail->WordWrap = 50; //设置每行字符长度
        $mail->IsHTML(C('MAIL_ISHTML')); // 是否HTML格式邮件
        $mail->CharSet=C('MAIL_CHARSET'); //设置邮件编码
        $mail->Subject =$title; //邮件主题
        $mail->Body = $content; //邮件内容
        $mail->AltBody = "这是一个纯文本的身体在非营利的HTML电子邮件客户端"; //邮件正文不支持HTML的备用显示
        return($mail->Send());
}

//计算两个左边之间的距离
function GetDistance($lat1, $lng1, $lat2, $lng2){ 
	$EARTH_RADIUS=6378.137;
	$PI=3.1415926535898;
    $radLat1 = $lat1 * (PI / 180);
    $radLat2 = $lat2 * (PI / 180);
   
    $a = $radLat1 - $radLat2; 
    $b = ($lng1 * ($PI / 180)) - ($lng2 * ($PI / 180)); 
   
    $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1)*cos($radLat2)*pow(sin($b/2),2))); 
    $s = $s * $EARTH_RADIUS;  //乘上地球半径，单位为公里
    $s = round(round($s * 10000) / 10000,2);   //单位为公里(km)
    return $s;  //单位为km
}

//数组重新排序
function sortArrByField(&$array, $field, $desc = false)
{   
	$fieldArr = array();  
	foreach ($array as $k => $v) {    
		$fieldArr[$k] = $v[$field]; 
	}   
	$sort = $desc == false ? SORT_ASC : SORT_DESC;  
	array_multisort($fieldArr, $sort, $array);
} 

//带星号截取
function str_pad_star($string, $length = 3, $star = '***', $type = 'both'){

    if($type == 'both'){

        return substr($string, 0,$length) . $star . substr($string, -$length);

    }elseif($type == 'left'){

        return substr($string, 0,$length) . $star;

    }elseif($type == 'right'){

        return $star . substr($string, -$length);

    }

}

//微信获取access_token
function get_access_token($AppId,$AppSecret)
{
	$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$AppId."&secret=".$AppSecret;
	$json=json_decode(curlGet($url),true);
	if (!$json['errmsg']){
		return $json;
	}else {
		unset($data);
		$data['code']=0;
		$data['message']="获取access_token发生错误：错误代码".$json['errmsg'].",微信返回错误信息：".$json['errmsg'];
		outJson($data);
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
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$temp = curl_exec($ch);
		curl_close($ch);
		return $temp;
	}
function curlPost($url, $data){
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
		//print_r($errorno);
		curl_close($ch);
		return $tmpInfo;
		
	}


//获取支付名称
function get_pay_typename($typeid)
{
	$result=M("Setting")->where("id=".$typeid)->find();
	if($result)
	{
		return $result['title'];
	}else
	{
		return '';
	}
}
//获取项目名称
function get_project_name($project_id)
{
	$result=M("ShopItem")->where("id=".$project_id)->find();
	if($result)
	{
		return $result['item_name'];
	}else
	{
		return '';
	}
}
//发送短信验证码
function SendSms($mobile,$message,$Ext)
{
	$user_nameid=M("Config")->where("id=10")->getField("value");
	$user_name=M("Config")->where("id=8")->getField("value");
	$user_password=M("Config")->where("id=9")->getField("value");
	$content = iconv("utf-8","gbk",$message);
	$url="http://42.96.248.183:8080/sendsms.php?userid=".$user_nameid."&username=".$user_name."&passwordMd5=".md5($user_password)."&mobile=".$mobile."&message=".$content."&Ext=".$Ext;
	$file_contents = file_get_contents($url);
	return $file_contents;
}
//会员卡号
function get_member_sn($start_id,$i)
{
    $new_sn =M("ShopMember")->where("shop_id=".$i)->order("id desc")->find();
    if($new_sn)
	{	
		$new_sn['member_no']=substr($new_sn['member_no'],-12);
		return $start_id.str_pad($new_sn['member_no']+1,12,"0",STR_PAD_LEFT);
	}else
	{
		return $start_id.str_pad($i.'00000001',12,"0",STR_PAD_LEFT);
	}
}

//极光推送
function jpush_request_post($url="",$param="",$header="")
{
	if (empty($url)||empty($param))
	{
		return false;
	}
	$postUrl = $url;
	$curlPost = $param;
	$ch = curl_init();//初始化curl
	curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
	curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
	curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
	// 增加 HTTP Header（头）里的字段 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	// 终止从服务端进行验证
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	$data = curl_exec($ch);//运行curl
	curl_close($ch);
	return $data;
}
//技师端推送
function jpush_send($title,$message,$audience,$platform="all",$extras="",$sendno=1) 
{
	$url='https://api.jpush.cn/v3/push';
	$base64=base64_encode("59e81ce7067e1cb5f6525ee8:c3087eb57873ce8ff58c8b74");
	$header=array("Authorization:Basic $base64","Content-Type:application/json");
	if($platform=="ios")
	{//ios的消息是可以透传的，所以只要传输一次就可以了，不需要二次message
		$param='{"platform":["ios"],"audience":'.$audience.',"notification":{"ios":{"alert":"'.$message.'","sound":"happy","badge":"+1","extras":'.$extras.'}},"message":{"msg_content":"'.$message.'","title":"'.$title.'","extras":'.$extras.'},"options":{"time_to_live":60,"sendno":'.$sendno.'}}';
	}
	else if($platform=="android")
	{//android的消息没有办法透传，所以必须传送两次
		$param='{"platform":["android"],"audience":'.$audience.',"notification":{"android":{"alert":"'.$message.'","title":"", "builder_id":3,"extras":'.$extras.'}},"message":{"msg_content":"'.$message.'","title":"'.$title.'","extras":'.$extras.'},"options":{"time_to_live":60,"sendno":'.$sendno.'}}';
	}
	else if($platform=="all")
	{//如果是全平台推送的话,进行全部推送，这个时候如果是安卓手机的话就不能进行message透传了，也就是说，应用打开的时候是收不到message参数的
		$param='{"platform":"all","audience":'.$audience.',"notification":{"ios":{"alert":"'.$message.'","sound":"happy","badge":"+1","extras":'.$extras.'},"android":{"alert":"'.$message.'","title":"", "builder_id":3,"extras":'.$extras.'}},"options":{"time_to_live":60,"sendno":'.$sendno.'}}';
	}
	$res=jpush_request_post($url,$param,$header);
	//file_put_contents('1.txt',$res);
	$res_arr=json_decode($res, true);
	return $res_arr;
}
//商家推送
function jpush_shop_send($title,$message,$audience,$platform="all",$extras="",$sendno=1) 
{
	$url='https://api.jpush.cn/v3/push';
	$base64=base64_encode("5bce6721433cd4d34c65b46f:ff335d6c02ec65c8fbc987ce");
	$header=array("Authorization:Basic $base64","Content-Type:application/json");
	if($platform=="ios")
	{//ios的消息是可以透传的，所以只要传输一次就可以了，不需要二次message
		$param='{"platform":["ios"],"audience":'.$audience.',"notification":{"ios":{"alert":"'.$message.'","sound":"happy","badge":"+1","extras":'.$extras.'}},"message":{"msg_content":"'.$message.'","title":"'.$title.'","extras":'.$extras.'},"options":{"time_to_live":60,"sendno":'.$sendno.'}}';
	}
	else if($platform=="android")
	{//android的消息没有办法透传，所以必须传送两次
		$param='{"platform":["android"],"audience":'.$audience.',"notification":{"android":{"alert":"'.$message.'","title":"", "builder_id":3,"extras":'.$extras.'}},"message":{"msg_content":"'.$message.'","title":"'.$title.'","extras":'.$extras.'},"options":{"time_to_live":60,"sendno":'.$sendno.'}}';
	}
	else if($platform=="all")
	{//如果是全平台推送的话,进行全部推送，这个时候如果是安卓手机的话就不能进行message透传了，也就是说，应用打开的时候是收不到message参数的
		$param='{"platform":"all","audience":'.$audience.',"notification":{"ios":{"alert":"'.$message.'","sound":"happy","badge":"+1","extras":'.$extras.'},"android":{"alert":"'.$message.'","title":"", "builder_id":3,"extras":'.$extras.'}},"options":{"time_to_live":60,"sendno":'.$sendno.'}}';
	}
	$res=jpush_request_post($url,$param,$header);
	//file_put_contents('1.txt',$res);
	$res_arr=json_decode($res, true);
	return $res_arr;
}

?>