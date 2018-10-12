<?php
echo "a";
require_once 'autoload.php';
echo "b";
$oss=new \OSS\OssClient("LdtUUn4Mn1ghFEpv1","4MUHRNWFH5JOvIE6UMTyB1J1aJrl9M","http://intpaypos.oss-cn-shanghai.aliyuncs.com",true);
var_dump($_FILES);
/*var_dump($oss->uploadfile("intpaypos","abcdef.png","../uploadfile/2016-11-09/20161109183209.png"));*/
try {
		$oss->uploadfile("intpaypos","aa.png",$_FILES['pic']['tmp_name']);
	}catch(Exception $e){var_dump($e);}
?>
