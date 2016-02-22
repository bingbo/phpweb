<?php

class DbMysqli extends Db{

    /**
     +----------------------------------------------------------
     * 架构函数 读取数据库配置信息
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $config 数据库配置数组
     +----------------------------------------------------------
     */
    public function __construct($config=''){
		parent::__construct($config);
        if ( !extension_loaded('mysqli') ) {
            //throw_exception('_NOT_SUPPERT_:mysqli');
        }
        if(!empty($config)) {
            $this->config   =   $config;
        }
    }

    /**
     +----------------------------------------------------------
     * 连接数据库方法
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function connect($config='',$linkNum=0) {
        if ( !isset($this->conns[$linkNum]) ) {
            if(empty($config))  $config =   $this->config;
			
            $this->conns[$linkNum] = new mysqli($config['hostname'],$config['username'],$config['password'],$config['database'],$config['hostport']?intval($config['hostport']):3306);
            if (mysqli_connect_errno()) //throw_exception(mysqli_connect_error());
            $dbVersion = $this->conns[$linkNum]->server_version;
            if ($dbVersion >= '4.1') {
                // 设置数据库编码 需要mysql 4.1.0以上支持
                $this->conns[$linkNum]->query("SET NAMES '".C('DB_CHARSET')."'");
            }
            //设置 sql_model
            if($dbVersion >'5.0.1'){
                $this->conns[$linkNum]->query("SET sql_mode=''");
            }
            // 标记连接成功
            $this->connected    =   true;
            //注销数据库安全信息
            if(1 != C('DB_DEPLOY_TYPE')) unset($this->config);
        }
        return $this->conns[$linkNum];
    }

    /**
     +----------------------------------------------------------
     * 释放查询结果
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function free() {
        $this->query->free_result();
        $this->query = null;
    }

    /**
     +----------------------------------------------------------
     * 执行查询 返回数据集
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $str  sql指令
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function query($str) {
        $this->initConnect(false);
		
        if ( !$this->_conn ) return false;
        $this->queryStr = $str;
		
        //释放前次的查询结果
        if ( $this->query) $this->free();
    

        $this->query = $this->_conn->query($str);
		
        // 对存储过程改进
        /* if( $this->_linkID->more_results() ){
            while (($res = $this->_linkID->next_result()) != NULL) {
                $res->free_result();
            }
        } */
        
        if ( false === $this->query) {
            $this->error();
            return false;
        } else {
            $this->numRows  = $this->query->num_rows;
            $this->numCols    = $this->query->field_count;
            return $this->getAll();
        }
    }

    /**
     +----------------------------------------------------------
     * 执行语句
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $str  sql指令
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function execute($str) {
        $this->initConnect(true);
        if ( !$this->_conn ) return false;
        $this->queryStr = $str;
        //释放前次的查询结果
        if ( $this->query ) $this->free();
        //N('db_write',1);
        // 记录开始执行时间
        //G('queryStartTime');
        $result =   $this->_conn->query($str);
        //$this->debug();
        if ( false === $result ) {
            $this->error();
            return false;
        } else {
            $this->numRows = $this->_conn->affected_rows;
            $this->lastInsID = $this->_conn->insert_id;
            return $this->numRows;
        }
    }

    /**
     +----------------------------------------------------------
     * 启动事务
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function startTrans() {
        $this->initConnect(true);
        //数据rollback 支持
        if ($this->transTimes == 0) {
            $this->_conn->autocommit(false);
        }
        $this->transTimes++;
        return ;
    }

    /**
     +----------------------------------------------------------
     * 用于非自动提交状态下面的查询提交
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function commit() {
        if ($this->transTimes > 0) {
            $result = $this->_conn->commit();
            $this->_conn->autocommit( true);
            $this->transTimes = 0;
            if(!$result){
                //throw_exception($this->error());
            }
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * 事务回滚
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function rollback() {
        if ($this->transTimes > 0) {
            $result = $this->_conn->rollback();
            $this->transTimes = 0;
            if(!$result){
                //throw_exception($this->error());
            }
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * 获得所有的查询数据
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @param string $sql  sql语句
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    private function getAll() {
        //返回数据集
        $result = array();
        if($this->numRows>0) {
            //返回数据集
            for($i=0;$i<$this->numRows ;$i++ ){
                $result[$i] = $this->query->fetch_assoc();
            }
            $this->query->data_seek(0);
        }
        return $result;
    }

    /**
     +----------------------------------------------------------
     * 取得数据表的字段信息
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function getFields($tableName) {
        $result =   $this->query('SHOW COLUMNS FROM '.$tableName);
        $info   =   array();
        if($result) {
            foreach ($result as $key => $val) {
                $info[$val['Field']] = array(
                    'name'    => $val['Field'],
                    'type'    => $val['Type'],
                    'notnull' => (bool) ($val['Null'] === ''), // not null is empty, null is yes
                    'default' => $val['Default'],
                    'primary' => (strtolower($val['Key']) == 'pri'),
                    'autoinc' => (strtolower($val['Extra']) == 'auto_increment'),
                );
            }
        }
        return $info;
    }

    /**
     +----------------------------------------------------------
     * 取得数据表的字段信息
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function getTables($dbName='') {
        $sql    = !empty($dbName)?'SHOW TABLES FROM '.$dbName:'SHOW TABLES ';
        $result =   $this->query($sql);
        $info   =   array();
        if($result) {
            foreach ($result as $key => $val) {
                $info[$key] = current($val);
            }
        }
        return $info;
    }



    /**
     +----------------------------------------------------------
     * 关闭数据库
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function close() {
        if ($this->_conn){
            $this->_conn->close();
        }
        $this->_conn = null;
    }

    /**
     +----------------------------------------------------------
     * 数据库错误信息
     * 并显示当前的SQL语句
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function error() {
        $this->error = $this->_conn->error;
        if( '' != $this->queryStr){
            $this->error .= "\n [ SQL语句 ] : ".$this->queryStr;
        }
        return $this->error;
    }

    


}