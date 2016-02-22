<?php
	interface IUserDao{
		
		
		public  function insertUser($user);
		
		public  function findAllUser();
		
		public  function deleteUser($user);
		
		public  function findUserById($id);
		
		public  function changeUser($user);
	}