<?php

if (!defined('FRAME_PATH')) exit();
if(version_compare(PHP_VERSION,'5.2.0','<'))  die('require PHP > 5.2.0 !');

//  版本信息
define('FRAME_VERSION', '1.0');
define('FRAME_RELEASE', '20130820');

//   系统信息
if(version_compare(PHP_VERSION,'5.4.0','<') ) {
    @set_magic_quotes_runtime (0);
    define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()?True:False);
}
/*
define('IS_CGI',substr(PHP_SAPI, 0,3)=='cgi' ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);
*/
// 项目名称
defined('APP_NAME') or define('APP_NAME', basename(dirname($_SERVER['SCRIPT_FILENAME'])));
/*
if(!IS_CLI) {
    // 当前文件名
    if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            //CGI/FASTCGI模式下
            $_temp  = explode('.php',$_SERVER['PHP_SELF']);
            define('_PHP_FILE_',  rtrim(str_replace($_SERVER['HTTP_HOST'],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',    rtrim($_SERVER['SCRIPT_NAME'],'/'));
        }
    }
    if(!defined('__ROOT__')) {
        // 网站URL根目录
        if( strtoupper(APP_NAME) == strtoupper(basename(dirname(_PHP_FILE_))) ) {
            $_root = dirname(dirname(_PHP_FILE_));
        }else {
            $_root = dirname(_PHP_FILE_);
        }
        define('__ROOT__',   (($_root=='/' || $_root=='\\')?'':$_root));
    }

    //支持的URL模式
    define('URL_COMMON',      0);   //普通模式
    define('URL_PATHINFO',    1);   //PATHINFO模式
    define('URL_REWRITE',     2);   //REWRITE模式
    define('URL_COMPAT',      3);   // 兼容模式
}
*/
// 路径设置 可在入口文件中重新定义 所有路径常量都必须以/ 结尾
defined('CORE_PATH') or define('CORE_PATH',SRC_PATH.'core'.DIR_SEP); // 系统核心类库目录
defined('EXTEND_PATH') or define('EXTEND_PATH',FRAME_PATH.'extend'.DIR_SEP); // 系统扩展目录
defined('VENDOR_PATH') or define('VENDOR_PATH',EXTEND_PATH.'vendor'.DIR_SEP); // 第三方类库目录
defined('LIB_PATH') or define('LIB_PATH',    APP_PATH.'src'.DIR_SEP); // 项目类库目录
defined('CONF_PATH') or define('CONF_PATH',  LIB_PATH.'conf'.DIR_SEP); // 项目配置目录
defined('TMPL_PATH') or define('TMPL_PATH',APP_PATH.'tpl/'); // 项目模板目录
defined('STATIC_PATH') or define('STATIC_PATH',APP_PATH.'static/'); // 项目静态目录
defined('LOG_PATH') or define('LOG_PATH',  APP_PATH.'logs/'); // 项目日志目录


// 为了方便导入第三方类库 设置Vendor目录到include_path
set_include_path(get_include_path() . PATH_SEPARATOR . VENDOR_PATH);

// 加载运行时所需要的文件 并负责自动目录生成
function load_runtime_file() {
    // 加载系统基础函数库
	require_once(SRC_PATH.'util'.DIR_SEP.'common.php');
    require_once(CORE_PATH.'Frame.php');
    // 读取核心编译文件列表
    /*$list = array(
        CORE_PATH.'Core/Think.class.php',
        CORE_PATH.'Core/ThinkException.class.php',  // 异常处理类
        CORE_PATH.'Core/Behavior.class.php',
    );
    // 加载模式文件列表
    foreach ($list as $key=>$file){
        if(is_file($file))  require_cache($file);
    }
    // 加载系统类库别名定义
    alias_import(include THINK_PATH.'Conf/alias.php');
	*/
    // 检查项目目录结构 如果不存在则自动创建
    if(!is_dir(LIB_PATH)) {
        // 创建项目目录结构
        build_app_dir();
	}

}


// 创建项目目录结构
function build_app_dir() {
    // 没有创建项目目录的话自动创建
    if(!is_dir(APP_PATH)) mkdir(APP_PATH,0777);
	
    if(is_writeable(APP_PATH)) {
        $dirs  = array(
            LIB_PATH,
            CONF_PATH,
            TMPL_PATH,
            LOG_PATH,
            LIB_PATH.'model'.DIR_SEP,
            LIB_PATH.'controller'.DIR_SEP,
            LIB_PATH.'service'.DIR_SEP,
            LIB_PATH.'dao'.DIR_SEP,
            );
        foreach ($dirs as $dir){
            if(!is_dir($dir))  mkdir($dir,0777);
        }
        // 目录安全写入
        defined('BUILD_DIR_SECURE') or define('BUILD_DIR_SECURE',false);
        if(BUILD_DIR_SECURE) {
            defined('DIR_SECURE_FILENAME') or define('DIR_SECURE_FILENAME','index.html');
            defined('DIR_SECURE_CONTENT') or define('DIR_SECURE_CONTENT',' ');
            // 自动写入目录安全文件
            $content = DIR_SECURE_CONTENT;
            $a = explode(',', DIR_SECURE_FILENAME);
            foreach ($a as $filename){
                foreach ($dirs as $dir)
                    file_put_contents($dir.$filename,$content);
            }
        }
        // 写入配置文件
        if(!is_file(CONF_PATH.'config.php'))
            file_put_contents(CONF_PATH.'config.php',"<?php\nreturn array(\n\t//'配置项'=>'配置值'\n);\n?>");
        // 写入测试Action
        if(!is_file(LIB_PATH.'action/IndexAction.php'))
            build_first_action();
    }else{
        header('Content-Type:text/html; charset=utf-8');
        exit('项目目录不可写，目录无法自动生成！<BR>请使用项目生成器或者手动生成项目目录~');
    }
}

// 创建测试Action
function build_first_action() {
    $content = file_get_contents(FRAME_PATH.'tpl/default_index.tpl');
    file_put_contents(LIB_PATH.'action/IndexAction.php',$content);
}

// 加载运行时所需文件
load_runtime_file();
// 记录加载文件时间
//G('loadTime');
// 执行入口

Frame::start();