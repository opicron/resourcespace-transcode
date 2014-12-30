<?php

function HookTranscodeViewAfterresourceactions (){
	global $ref;
	global $access;
	global $lang;
	global $resource;
	global $transcode_allowed_extensions;
	global $transcode_original;
	global $baseurl_short;

	if (!isset($transcode_allowed_extensions)){
		for($i=0;$i<count($transcode_allowed_extensions);$i++){
			$transcode_allowed_extensions[$i] = strtoupper($transcode_allowed_extensions[$i]);
		}	
	}

	if ($access==0 && in_array(strtoupper($resource['file_extension']),$transcode_allowed_extensions)){
		echo "<li><a onClick='return CentralSpaceLoad(this,true);' href='".$baseurl_short."plugins/transcode/pages/transcode.php?ref=$ref&mode='>&gt; ";
		echo "Transcode";
		echo "</a></li>";
		return true;
	}

}

?>
