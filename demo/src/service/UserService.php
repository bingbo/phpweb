<?php
	class UserService extends IUserService{
		private $userDao;
		private static $num=0;
		
		public function __construct(){
			$this->userDao=new UserDao();
			
			echo 'this is the '.++UserService::$num.' userService</br>';
		}
		
		public function addUser($user){
			$this->userDao->insertUser($user);
			echo 'add one user<br>';
		}
		
		public function getUsers(){
			return $this->userDao->findAllUser();
			echo 'get all users<br>';
		}
		
		public function removeUser($user){
			
		}
		
		public function getUserById($id){
			
		}
		
		public function modifyUser($user){
			
		}
	}