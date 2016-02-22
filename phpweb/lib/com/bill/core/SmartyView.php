<?php
require_once(VENDOR_PATH.'smarty'.DIR_SEP.'libs'.DIR_SEP.'Smarty.class.php');
class SmartyView extends View{
    private $smarty =null;
    /**
     +----------------------------------------------------------
     * 模板变量赋值
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $name
     * @param mixed $value
     +----------------------------------------------------------
     */
	public function __construct() {
		$this->smarty=new Smarty();

		C('LEFT_DELIMITER')?$this->smarty->left_delimiter=C('LEFT_DELIMITER'):'';
		C('RIGHT_DELIMITER')?$this->smarty->right_delimiter=C('RIGHT_DELIMITER'):'';
		$this->smarty->setTemplateDir(C('TEMPLATE_DIR')?C('TEMPLATE_DIR'):TMPL_PATH.DIR_SEP.'templates');
		$this->smarty->setCompileDir(C('COMPILE_DIR')?C('COMPILE_DIR'):TMPL_PATH.DIR_SEP.'templates_c');
		$this->smarty->addPluginsDir(C('PLUGINS_DIR')?C('PLUGINS_DIR'):TMPL_PATH.DIR_SEP.'plugins');
		$this->smarty->setConfigDir(C('CONFIG_DIR')?C('CONFIG_DIR'):TMPL_PATH.DIR_SEP.'configs');
		$this->smarty->setCacheDir(C('CACHE_DIR')?C('CACHE_DIR'):TMPL_PATH.DIR_SEP.'cache');
		
		C('FORCE_COMPILE')?$this->smarty->force_compile = C('FORCE_COMPILE'):'';
		C('DEBUGGING')?$this->smarty->debugging = C('DEBUGGING'):'';
		C('CACHING')?$this->smarty->caching = C('CACHING'):'';
		C('CACHE_LIFETIME')?$this->smarty->cache_lifetime = C('CACHE_LIFETIME'):'';
	}
    public function assign($name,$value=''){
        return $this->smarty->assign($name, $value);
    }

    



    /**
     +----------------------------------------------------------
     * 加载模板和页面输出 可以返回输出内容
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $templateFile 模板文件名
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function display($templateFile='',$charset='',$contentType='') {
        //G('viewStartTime');
        // 视图开始标签
        //tag('view_begin',$templateFile);
        // 解析并获取模板内容
        //$content = $this->fetch($templateFile);
        // 输出模板内容
        //$this->show($content,$charset,$contentType);
		
		$this->smarty->display($templateFile);
        // 视图结束标签
        //tag('view_end');
    }

    /**
     +----------------------------------------------------------
     * 输出内容文本可以包括Html
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $content 输出内容
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function show($content,$charset='',$contentType=''){
        if(empty($charset))  $charset = C('DEFAULT_CHARSET');
        if(empty($contentType)) $contentType = C('TMPL_CONTENT_TYPE');
        // 网页字符编码
        header('Content-Type:'.$contentType.'; charset='.$charset);
        header('Cache-control: private');  //支持页面回跳
        header('X-Powered-By:ThinkPHP');
        // 输出模板文件
        echo $content;
    }

    /**
     +----------------------------------------------------------
     * 解析和获取模板内容 用于输出
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $templateFile 模板文件名
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function fetch($templateFile='') {

        // 模板文件解析标签
        //tag('view_template',$templateFile);
        // 模板文件不存在直接返回
        //if(!is_file($templateFile)) return NULL;
        // 页面缓存
        ob_start();
        ob_implicit_flush(0);
		
		$content=$this->smarty->fetch($templateFile);
        // 获取并清空缓存
        //$content = ob_get_clean();
        // 内容过滤标签
        //tag('view_filter',$content);
        // 输出模板文件
        return $content;
    }
}