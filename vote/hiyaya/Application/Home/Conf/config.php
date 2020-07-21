<?php
return array(
	//'配置项'=>'配置值'
	  /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
		'__PUB__' => __ROOT__ . '/Public',
        '__STATIC__' => __ROOT__ . '/Public/' .MODULE_NAME,
        '__IMG__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/images',
        '__CSS__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
        '__JS__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/script',
    ),
);