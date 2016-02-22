<?php
	class UserController extends Controller{
		private $userService;
		
		public function _initialize(){
			$this->userService=new UserService();
		}
		
		public function execute($params){
			echo 'hello';
		}
		
		public function add($params){
			
			$u1=new User('bill','1111','bill@126.com');
			$this->userService->addUser($u1);
			$this->assign('context','aa');
			Log::write(get_class($this).': '.'write a user',Log::ERR,3,LOG_PATH.'error.log');
			$this->display('user/add.tpl');
		}
		
		public function getAll($params){
			$result=$this->userService->getUsers();
			$this->assign('context',$result);
			
			$this->display('user/show.tpl');
			
		}
		
		
		
	
	}
