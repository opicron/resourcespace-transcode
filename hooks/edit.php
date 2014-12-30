<?php
function HookTranscodeEditAfterreplacefile (){
	global $ref;
	global $access;
	global $lang;
	global $resource;
	global $transcode_allowed_extensions;
	global $transcode_original;
	global $baseurl_short;

	if (!isset($transcode_allowed_extensions)){
		// in case these have been overriden, make sure these are all in uppercase.
		for($i=0;$i<count($transcode_allowed_extensions);$i++){
			$transcode_allowed_extensions[$i] = strtoupper($transcode_allowed_extensions[$i]);
		}	
	}

	if ($access==0 && in_array(strtoupper($resource['file_extension']),$transcode_allowed_extensions)){
		echo "<br /><a onClick='return CentralSpaceLoad(this,true);' href='${baseurl_short}plugins/transcode/pages/transcode.php?ref=$ref&mode=original'>&gt; ";
		echo "Transcode";
		echo "</a>";
		return true;
	}


}

?>
