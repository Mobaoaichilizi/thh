<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------


return [
    // 视图输出字符串内容替换
    'view_replace_str'       => [
        '{__ADMIN_PATH__}'   => '/static/layuiadmin',//后台
        '{__STATIC_CSS__}'  => '/static/css',//全站通用
		'{__STATIC_JS__}'  => '/static/js',//全站通用
		'{__STATIC_IMG__}'  => '/static/images',//全站通用
        '{__PUBLIC_PATH}'  => '/static',//静态资源路径
    ],
    /* 图片上传相关配置 */
	'PICTURE_UPLOAD_DRIVER'=>'local',

	'PICTURE_UPLOAD' => [
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 20*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Picture/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ], //图片上传相关配置（文件上传类配置）
];
