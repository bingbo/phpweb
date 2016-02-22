<?php
	class User{
		private $id;
		private $name;
		private $password;
		private $email;
		private static $num=0;
		
		public function __construct($name='',$password='',$email=''){
			$this->id=self::$num++;
			$this->name=$name;
			$this->password=$password;
			$this->email=$email;
			
			echo 'this is the '.User::$num.' user</br>';
		}
		
		public function __set($propName,$propValue){
			if(property_exists($this,$propName)){
				$this->$propName=$propValue;
			}
		}
		
		public function __get($propName){
			return isset($this->$propName)?$this->$propName:null;
		}
	}