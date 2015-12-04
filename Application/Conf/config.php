<?php
return array(
	//'配置项'=>'配置值'
	//'SHOW_PAGE_TRACE' =>true,

	'URL_MODEL' => '2',
	//'SESSION_AUTO_START' => true,
	
	'APP_GROUP_LIST' => 'Home,Admin',
	'DEFAULT_GROUP' => 'Home',

	'URL_HTML_SUFFIX' => 'html',
    // 'TMPL_ACTION_ERROR' => 'Public:error', // 默认错误跳转对应的模板文件
    // 'TMPL_ACTION_SUCCESS' => 'Public:success', // 默认成功跳转对应的模板文件

	'DB_TYPE'           => 'mysql', 
	//'DB_HOST'           => '115.28.88.146',
	'DB_HOST'           => 'localhost',
	'DB_NAME'           => 'anyooh',
	'DB_USER'           => 'anyooh',
	'DB_PWD'            => 'anyoohadmin',
	// 'DB_USER'           => 'root',
	// 'DB_PWD'            => '',
	'DB_PORT'           => '3306',
	'DB_PREFIX'         => 'm_',
	'DB_CHARSET'=> 'utf8', 
	 
    'AUTH_ON' => true, //认证开关
    'AUTH_TYPE' => 1, // 认证方式，1为时时认证；2为登录认证。
    'AUTH_GROUP' => 'm_auth_group', //用户组数据表名
    'AUTH_GROUP_ACCESS' => 'm_auth_group_access', //用户组明细表
    'AUTH_RULE' => 'm_auth_rule', //权限规则表
    'AUTH_USER' => 'm_member', //用户信息表
    'Administrator' => array('1'),

	'WEB_URL' => 'http://localhost/',
	'UPLOAD_PATH' => 'Public/Uploads/',
	'PAGE_SIZE' => 10,
 
);
?>
