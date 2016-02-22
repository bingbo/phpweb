<?php
	class UserDao extends DbMysqli implements IUserDao{
		private $users;
		private static $num=0;
		
		public function __construct(){
			parent::__construct();
			$this->users=array();
			
			echo 'this is the '.++UserDao::$num.' userDao</br>';
		}
		
		public function insertUser($user){
			$this->users[]=$user;
		}
		
		public function findAllUser(){
			$result=$this->query("select * from user");
			
			return $result;
		}
		
		public function deleteUser($user){
			
		}
		
		public function findUserById($id){
			
		}
		
		public function changeUser($user){
			
		}
	}