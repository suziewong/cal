<?php
    include_once '../sys/core/init.inc.php';

    $page_title = "Add/Edit Event";

    $css_files = array("style.css");

    include_once 'assets/common/header.inc.php';

    $cal = new Calendar($dbo);

?>
<div id="content">
<?php echo $cal->displayForm();?>

</div>

<?php
    include_once 'assets/common/footer.inc.php';
?>
