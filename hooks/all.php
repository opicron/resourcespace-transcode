<?php

function HookTranscodeAllAdditionalheaderjs()
{
	global $baseurl;
	?>
		<script src="<?php echo $baseurl?>/plugins/transcode/lib/jquery.ba-dotimeout.min.js"></script>
		<script src="<?php echo $baseurl?>/plugins/transcode/lib/featherlight.js"></script>
		<script src="<?php echo $baseurl?>/plugins/transcode/lib/ion.rangeSlider.js"></script>
		<script src="<?php echo $baseurl?>/plugins/transcode/lib/moment.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo $baseurl?>/plugins/transcode/css/featherlight.css"/>
		<link rel="stylesheet" type="text/css" href="<?php echo $baseurl?>/plugins/transcode/css/ion.rangeSlider.css"/>
		<link rel="stylesheet" type="text/css" href="<?php echo $baseurl?>/plugins/transcode/css/ion.rangeSlider.skinFlat.css"/>
		<!--<link rel="stylesheet" type="text/css" href="<?php echo $baseurl?>/plugins/transcode/css/normalize.css"/>-->
	<?php
}

?>