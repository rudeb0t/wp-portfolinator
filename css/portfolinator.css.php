<?php
ini_set('error_reporting', E_ALL & ~E_NOTICE);
header('Content-type: text/css');

require_once('../scriptargs.php');
?>
.<?php echo $wrap_class ?>,
.<?php echo $item_class ?> {
	padding: 0;
	margin: 0;
}
.<?php echo $wrap_class ?> {
	display: block;
}
ul.<?php echo $wrap_class ?> {
	list-style-type: none;
}
ul.<?php echo $wrap_class ?>:before,
.<?php echo $wrap_class ?>:before,
ul.<?php echo $wrap_class ?>:after,
.<?php echo $wrap_class ?>:after {
content: '\0020';
display: block;
overflow: hidden;
visibility: hidden;
width: 0;
height: 0;
}
ul.<?php echo $wrap_class ?>:after,
.<?php echo $wrap_class ?>:after {
	clear: both;
}
ul.<?php echo $wrap_class ?> li.<?php echo $item_class ?>,
.<?php echo $item_class ?> {
	width: <?php echo $w ?>px;
	height: <?php echo $h ?>px;
	overflow: hidden;
	float: left;
	margin: 10px;
}
