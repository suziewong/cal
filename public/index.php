<?php
	include_once '../sys/core/init.inc.php';
   // var_dump($_POST);
    $str = $_POST['year']."-".$_POST['month']."-01 00:00:00";
   // echo $str;
	$cal = new Calendar($dbo, $str);


	/*if( is_object ($cal))
	{
		echo "<pre>".var_dump($cal)."</pre>";
	}*/
	$page_title = "Events Calendar";
	$css_files = array('style.css','admin.css','ajax.css');
	include_once 'assets/common/header.inc.php';
?>
<div id='content'>
<?php

	echo $cal->buildCalendar();
?>
</div>
<center> 
<form action="index.php" method="post">
<select name="year">
    <option value="2010">2010</option>
    <option value="2012">2012</option>
</select>
<select name="month">
    <option value="01">1月</option>
    <option value="03">3月</option>
</select>
<input type="submit" />
</form>
</center>
<p>
<?php echo isset($_SESSION['user'])? "Logged In!":"Logged Out!";?>
</p>
<?php
	include_once 'assets/common/footer.inc.php';
?>
