Plugin to transcode videos and add them as alternative files

Note:
To make the native upload code in resourcespace function we have to edit the resourcespace code as such.

resource_functions.php:
-$result=move_uploaded_file($processfile['tmp_name'], $path);
+$result=rename($processfile['tmp_name'], $path);

Else the transcode file will not be 'uploaded'. I wanted to use resourcespaces own functionality instead of my own, thus this hack has needed. 

Please let me know if you have a workaround which does _not_ include copying resourcespace alternative file creation functionality.
