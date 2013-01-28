<?php
ini_set('error_reporting', E_ALL & ~E_NOTICE);
header('Content-type: text/javascript');

require_once('../scriptargs.php');
?>
jQuery(function($) {
    $(document).ready(function() {
<?php if ($subpage) { ?>
	$('dt.gallery-icon a').colorbox({rel:'portfolinator_items'});
<?php } else { ?>
        $('.<?php echo $wrap_class ?> .<?php echo $item_class ?> a').colorbox();
<?php } ?>
    });
});
