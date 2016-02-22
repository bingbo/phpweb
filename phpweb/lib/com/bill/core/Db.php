<?php


/**
 +------------------------------------------------------------------------------
 * phpweb 数据库中间层实现类
 +------------------------------------------------------------------------------

 */
class Db {
    
	protected $conns		=array();
	
	protected $sql		='';
	
	// 是否已经连接数据库
    protected $connected       = false;
	
	// 数据库类型
    protected $dbType           = null;
	 // 数据库连接参数配置
	protected $config             = '';
	
	   // 当前连接ID
    protected $_conn            =   null;
	
	// 当前查询
    protected $query          = null;
	
    // 返回或者影响记录数
    protected $numRows        = 0;
    // 返回字段数
    protected $numCols          = 0;
	
	// 最后插入ID
    protected $lastInsID         = null;
	
    // 事务指令数
    protected $transTimes      = 0;
	
	// 错误信息
    protected $error              = '';
	
    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $config 数据库配置数组
     +----------------------------------------------------------
     */
    public function __construct($config=''){
	
        $this->config = $this->parseConfig($db_config);
    }



    



    /**
     +----------------------------------------------------------
     * 分析数据库配置信息，支持数组和DSN
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @param mixed $db_config 数据库配置信息
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    private function parseConfig($db_config='') {
        if ( !empty($db_config) && is_string($db_config)) {
            // 如果DSN字符串则进行解析
            $db_config = $this->parseDSN($db_config);
        }elseif(is_array($db_config)) { // 数组配置
             $db_config = array(
                  'dbms'        => $db_config['db_type'],
                  'username'  => $db_config['db_user'],
                  'password'   => $db_config['db_pwd'],
                  'hostname'  => $db_config['db_host'],
                  'hostport'    => $db_config['db_port'],
                  'database'   => $db_config['db_name'],
                  'dsn'         => $db_config['db_dsn'],
                  'params'   => $db_config['db_params'],
             );
        }elseif(empty($db_config)) {
            // 如果配置为空，读取配置文件设置
            if( C('DB_DSN') && 'pdo' != strtolower(C('DB_TYPE')) ) { // 如果设置了DB_DSN 则优先
                $db_config =  $this->parseDSN(C('DB_DSN'));
            }else{
                $db_config = array (
                    'dbms'        =>   C('DB_TYPE'),
                    'username'  =>   C('DB_USER'),
                    'password'   =>   C('DB_PWD'),
                    'hostname'  =>   C('DB_HOST'),
                    'hostport'    =>   C('DB_PORT'),
                    'database'   =>   C('DB_NAME'),
                    'dsn'          =>   C('DB_DSN'),
                    'params'     =>   C('DB_PARAMS'),
                );
            }
        }
        return $db_config;
    }

    /**
     +----------------------------------------------------------
     * 初始化数据库连接
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param boolean $master 主服务器
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function initConnect($master=true) {
        if(1 == C('DB_DEPLOY_TYPE'))
            // 采用分布式数据库
            $this->_conn = $this->multiConnect($master);
        else
            // 默认单数据库
            if ( !$this->connected ) $this->_conn = $this->connect();
    }

    /**
     +----------------------------------------------------------
     * 连接分布式服务器
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param boolean $master 主服务器
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function multiConnect($master=false) {
        static $_config = array();
        if(empty($_config)) {
            // 缓存分布式数据库配置解析
            foreach ($this->config as $key=>$val){
                $_config[$key]      =   explode(',',$val);
            }
        }
        // 数据库读写是否分离
        if(C('DB_RW_SEPARATE')){
            // 主从式采用读写分离
            if($master)
                // 主服务器写入
                $r  =   floor(mt_rand(0,C('DB_MASTER_NUM')-1));
            else
                // 读操作连接从服务器
                $r = floor(mt_rand(C('DB_MASTER_NUM'),count($_config['hostname'])-1));   // 每次随机连接的数据库
        }else{
            // 读写操作不区分服务器
            $r = floor(mt_rand(0,count($_config['hostname'])-1));   // 每次随机连接的数据库
        }
        $db_config = array(
            'username'  =>   isset($_config['username'][$r])?$_config['username'][$r]:$_config['username'][0],
            'password'   =>   isset($_config['password'][$r])?$_config['password'][$r]:$_config['password'][0],
            'hostname'  =>   isset($_config['hostname'][$r])?$_config['hostname'][$r]:$_config['hostname'][0],
            'hostport'    =>   isset($_config['hostport'][$r])?$_config['hostport'][$r]:$_config['hostport'][0],
            'database'   =>   isset($_config['database'][$r])?$_config['database'][$r]:$_config['database'][0],
            'dsn'          =>   isset($_config['dsn'][$r])?$_config['dsn'][$r]:$_config['dsn'][0],
            'params'     =>   isset($_config['params'][$r])?$_config['params'][$r]:$_config['params'][0],
        );
        return $this->connect($db_config,$r);
    }
	


    /**
     +----------------------------------------------------------
     * DSN解析
     * 格式： mysql://username:passwd@localhost:3306/DbName
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @param string $dsnStr
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function parseDSN($dsnStr) {
        if( empty($dsnStr) ){return false;}
        $info = parse_url($dsnStr);
        if($info['scheme']){
            $dsn = array(
            'dbms'        => $info['scheme'],
            'username'  => isset($info['user']) ? $info['user'] : '',
            'password'   => isset($info['pass']) ? $info['pass'] : '',
            'hostname'  => isset($info['host']) ? $info['host'] : '',
            'hostport'    => isset($info['port']) ? $info['port'] : '',
            'database'   => isset($info['path']) ? substr($info['path'],1) : ''
            );
        }else {
            preg_match('/^(.*?)\:\/\/(.*?)\:(.*?)\@(.*?)\:([0-9]{1, 6})\/(.*?)$/',trim($dsnStr),$matches);
            $dsn = array (
            'dbms'        => $matches[1],
            'username'  => $matches[2],
            'password'   => $matches[3],
            'hostname'  => $matches[4],
            'hostport'    => $matches[5],
            'database'   => $matches[6]
            );
        }
        $dsn['dsn'] =  ''; // 兼容配置信息数组
        return $dsn;
     }


   /**
     +----------------------------------------------------------
     * 析构方法
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __destruct() {
        // 释放查询
        $this->free();
        // 关闭连接
        $this->close();
    }

    // 关闭数据库 由驱动类定义
    //public function close(){};
}