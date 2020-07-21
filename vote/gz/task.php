<?php
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
ignore_user_abort();//关掉浏览器，PHP脚本也可以继续执行.
set_time_limit(0);// 通过set_time_limit(0)可以让程序无限制的执行下去
$interval=1800;// 每隔半个小时运行

do{
  $run = 1; 
  if(!$run)
  {
	die('停止运行11');
	exit;
  }
  $host = "http:/api.sxgzyl.com/";
  curlGet($host."index.php?g=Doctor&m=Settimetest&a=member_time"); 
  curlGet($host."index.php?g=Doctor&m=Settimetest&a=auto_refund"); 
  curlGet($host."index.php?g=Doctor&m=Settimetest&a=auto_complete"); 
  // curlGet($host."index.php?g=Doctor&m=Settimetest&a=pre_reward"); 
  //ToDo
  sleep($interval);// 等待半个小时
}
while(true);
?>