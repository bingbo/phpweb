<?php
	abstract class IUserService{
		
		
		public abstract function addUser($user);
		
		public abstract function getUsers();
		
		public abstract function removeUser($user);
		
		public abstract function getUserById($id);
		
		public abstract function modifyUser($user);
		
	}