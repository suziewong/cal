<?php
	include_once '../sys/core/init.inc.php';



	$cal = new Calendar($dbo, "2010-01-01 12:00:00");


	/*if( is_object ($cal))
	{
		echo "<pre>".var_dump($cal)."</pre>";
	}*/
	$page_title = "Events Calendar";
	$css_files = array('style.css','admin.css');
	include_once 'assets/common/header.inc.php';
?>
<div id='content'>
<?php

	echo $cal->buildCalendar();
?>
</div>
<p>
<?php echo isset($_SESSION['user'])? "Logged In!":"Logged Out!";?>
</p>
<?php
	include_once 'assets/common/footer.inc.php';
?>
