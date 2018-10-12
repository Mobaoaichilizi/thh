<?php

$db=include "db.php";

$configs=array(
	//'配置项'=>'配置值'

    'MODULE_DENY_LIST'   => array('Common'),  //禁止访问的模块
    'MODULE_ALLOW_LIST'  => array('Home','Admin','User','Doctor'), //允许访问的模块	
	'DEFAULT_MODULE'     => 'Home', // 默认模块
	'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
	'DEFAULT_ACTION'        =>  'index', // 默认操作名称
	'DEFAULT_M_LAYER'       =>  'Model', // 默认的模型层名称
	'DEFAULT_C_LAYER'       =>  'Controller', // 默认的控制器层名称
	'DEFAULT_FILTER'        =>  'htmlspecialchars', // 默认参数过滤方法 用于I函数...htmlspecialchars
	
	
	
	// 配置邮件发送服务器
    'MAIL_HOST' =>'smtp.qq.com',//smtp服务器的名称
    'MAIL_SMTPAUTH' =>TRUE, //启用smtp认证
    'MAIL_USERNAME' =>'173066315@qq.com',//你的邮箱名
    'MAIL_PASSWORD' =>'shan891216',//邮箱密码
    'MAIL_CHARSET' =>'utf-8',//设置邮件编码
    'MAIL_ISHTML' =>TRUE, // 是否HTML格式邮件
	
	/* URL配置 */

	'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 0, //URL模式
	
	'VAR_MODULE'            =>  'g',     // 默认模块获取变量
	'VAR_CONTROLLER'        =>  'm',    // 默认控制器获取变量
	'VAR_ACTION'            =>  'a',    // 默认操作获取变量
	
	
	
);
return array_merge($db,$configs);