<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<title>Код авторизации</title>
<style>
*{
	margin: 0px;
	padding: 0px;
}
body{
   text-align: center;
}
body, html{
	height: 100%;
	width: 100%;
}
.outer_div {
	width:100%;
	height:100%;
	text-align:center;
}
.inner_div {
	display:-moz-inline-box;
	display:inline-block;
	vertical-align:middle;
	zoom:1;
	//display:inline;
	position: relative;
	padding: 5px;
}
.ext_div {
	display:-moz-inline-box;
	display:inline-block;
	height:100%;
	width:0px;
	vertical-align:middle;
	zoom:1;
	//display:inline;
}
.info p
{
	margin-bottom: 50px;
	font-size: 16pt;
	color: #777;
	font-family: sans;
}
.code
{
	display: inline-block;
	padding: 5px;
	border: 2px dotted #ccc;
	font-size: 30;
	color: #777;
	font-family: sans-serif;
}
</style>
</head>
<body>
<div class="outer_div">
	<div class="inner_div"><div class="info"><p>Скопируйте этот код в форму редактирования облачного хранилища в поле "Код":</p></div><div class="code"><?php echo $_REQUEST['code']?></div></div>
	<div class="ext_div"></div>
</div>
</body>
</html>
<?php die();?>