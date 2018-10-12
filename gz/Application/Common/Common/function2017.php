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
	$access=M('shopaccess');
	$users=M('shopuser');
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
	$json=json_decode(curlGet($url));
	if (!$json->errmsg){
		return $json;
	}else {
		unset($data);
		$data['code']=0;
		$data['message']="获取access_token发生错误：错误代码'.$json->errcode.',微信返回错误信息：'.$json->errmsg";
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
		//print_r($tmpInfo);
		curl_close($ch);
		return $tmpInfo;
		
	}

/** 
 * 生成不重复的随机数 
 * @param  int $start  需要生成的数字开始范围 
 * @param  int $end    结束范围 
 * @param  int $length 需要生成的随机数个数 
 * @return array       生成的随机数 
 */
function get_rand_number($start=1,$end=10,$length=4){  
    $connt=0;  
    $temp=array();  
    while($connt<$length){  
        $temp[]=mt_rand($start,$end);  
        $data=array_unique($temp);  
        $connt=count($data);  
    }  
    sort($data);  
    return $data;  
} 
/**
 * 时间转换成几分钟前、几小时前、几天前
*/
function time_tran($the_time) {  
    $now_time = time();    
    $show_time = strtotime($the_time);  
    $now_time = $now_time;  
    $show_time = $the_time;  
    $dur = $now_time - $show_time; 
    if ($dur < 0) {  
        return date('Y-m-d', $the_time);  
    } else {  
        if ($dur < 60) {  
            return $dur . '秒前';  
        } else {  
            if ($dur < 3600) {  
                return floor($dur / 60) . '分钟前';  
            } else {  
                if ($dur < 86400) {  
                    return floor($dur / 3600) . '小时前';  
                } else {  
                    if ($dur < 259200) {//3天内  
                        return floor($dur / 86400) . '天前';  
                    } else {  
                        return date('Y-m-d', $the_time);  
                    }  
                }  
            }  
        }  
    }  
}  
/**
** 发送短信接口
*/
function sendsms($mobile,$message,$Ext)
{
	$content = iconv("utf-8","gbk",'【广正云医】'.$message);
	$url="http://42.96.248.183:8080/sendsms.php?userid=".C("WEB_SMS_ID")."&username=".C("WEB_SMS_USER")."&passwordMd5=".md5(C("WEB_SMS_PASS"))."&mobile=".$mobile."&message=".$content."&Ext=".$Ext;
	$file_contents = file_get_contents($url);
	return $file_contents;
}
/**
** 当前日期获取周几
*/
function weekday($date)
{
	$i=date('w',$date);
	switch ($i)
	{
	case 0: $str = "星期日"; break;
	case 1: $str = "星期一"; break;
	case 2: $str = "星期二"; break;
	case 3: $str = "星期三"; break;
	case 4: $str = "星期四"; break;
	case 5: $str = "星期五"; break;
	case 6: $str = "星期六"; break;
	}
	return $str;
}
function get_week_date()
{
    $data['1'] = date("Y-m-d",time());
    $data['2'] = date("Y-m-d",strtotime("+1 day"));
    $data['3'] = date("Y-m-d",strtotime("+2 day"));
    $data['4'] = date("Y-m-d",strtotime("+3 day"));
    $data['5'] = date("Y-m-d",strtotime("+4 day"));
    $data['6'] = date("Y-m-d",strtotime("+5 day"));
    $data['7'] = date("Y-m-d",strtotime("+6 day"));
    // $data['7'] = date("Y-m-d",strtotime("+7 day"));
    $arr = array();
    foreach($data as $k=>$v)
    {
        $arr[$k]['week'] =  get_week($v);
        $arr[$k]['date'] = date('m-d',strtotime($v));
    }
    return $arr;
}
function get_week($date){
      $date_str=date('Y-m-d',strtotime($date));
      $arr=explode("-", $date_str);
      //参数赋值
      //年
      $year=$arr[0];
      //月，输出2位整型，不够2位右对齐
      $month=sprintf('%02d',$arr[1]);
      //日，输出2位整型，不够2位右对齐
      $day=sprintf('%02d',$arr[2]);
      //时分秒默认赋值为0；
      $hour = $minute = $second = 0;
      //转换成时间戳
      $strap = mktime($hour,$minute,$second,$month,$day,$year);
      //获取数字型星期几
      $number_wk=date("w",$strap);
      //自定义星期数组
      $weekArr=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
      //获取数字对应的星期
      return $weekArr[$number_wk];
  }	
//积分明细
function setpoints($uid,$role,$score,$txt,$opid=0){
     $data=array(
           'uid'    => $uid, //会员ID，就你要给那个会员操作积分就传入那个会员的ID
           'role'   => $role,
           'score'  => $score,//操作的积分数量,正数为加分，负数为减分；
           'optxt'  => $txt,   //操作理由，简单的积分操作理由；
           'optime' => time(), //操作时间
           'opid'   => $opid  //操作员ID,如果为0表示系统操作；
     );
     M('ScoreList')->add($data);    //写入积分操作明细数据；
     M('User')->where("id=".$uid." and 2='".$role."'")->setInc('score',$score);   //更新会员表积分字段；
     M('User')->where("id=".$uid." and 1='".$role."'")->setInc('score',$balance);   //更新会员表余额字段；
}
?>