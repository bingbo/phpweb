<div>


	{%foreach from=$context item=user%}
		id:{%$user.id%}<br>
		name:{%$user.name%}<br>
		password:{%$user.password%}<br>
		email:{%$user.email%}<br>
		--------------------------------------------------------<br>
	{%/foreach%}


</div>