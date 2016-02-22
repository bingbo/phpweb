<?php
return array(
	//'配置项'=>'配置值'
	//smarty配置
	'LEFT_DELIMITER'           => '{%',
	'RIGHT_DELIMITER'          => '%}',
	'FORCE_COMPILE'			   => false,
	'DEBUGGING'				   => false,
	'CACHING'				   => false,
	'CACHE_LIFETIME'		   => 120,
	'TEMPLATE_DIR'   		   => '',
	'COMPILE_DIR'			   => '',
	'PLUGINS_DIR'			   => '',
	'CONFIG_DIR'			   => '',
	'CACHE_DIR'				   => '',
	
	//数据库配置
	'DB_TYPE'=>'mysql',
	'DB_HOST'=>'localhost',
	'DB_NAME'=>'test',
	'DB_USER'=>'root',
	'DB_PWD'=>'123456',
	'DB_PORT'=>3306,
    'SHOW_PAGE_TRACE' =>true,
    'DB_FIELDS_CACHE'       => false,        // 不启用字段缓存
);
?>
