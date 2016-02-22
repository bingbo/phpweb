<?php


//记录开始运行时间
$GLOBALS['_beginTime'] = microtime(TRUE);
// 记录内存初始使用
define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
if(MEMORY_LIMIT_ON) $GLOBALS['_startUseMems'] = memory_get_usage();
defined('DIR_SEP') or define('DIR_SEP',DIRECTORY_SEPARATOR);
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']).DIR_SEP);
defined('APP_DEBUG') or define('APP_DEBUG',false); // 是否调试模式

defined('FRAME_PATH') or define('FRAME_PATH',dirname(__FILE__).DIR_SEP);
defined('SRC_PATH') or define('SRC_PATH',FRAME_PATH.'lib'.DIR_SEP.'com'.DIR_SEP.'bill'.DIR_SEP);
// 加载运行时文件
require SRC_PATH.'runtime'.DIR_SEP.'RunTime.php';