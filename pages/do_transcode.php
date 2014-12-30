<?php

include_once "../../../include/db.php";
include_once "../../../include/authenticate.php";
include_once "../../../include/general.php";
include_once "../../../include/resource_functions.php";
include_once "../../../include/image_processing.php";
//include_once "../include/transform_functions.php";


//function getProgress($cropendtime=null, $cropstarttime=null)
function getProgress($cutstart=null, $cutend=null)
{
//$data[] = array('progress'=>$progress, 'duration'=>$duration, 'time'=>$time);
//echo "Duration: " . $duration . "<br>";
//echo "Current Time: " . $time . "<br>";
//echo "Progress: " . $progress . "%";

	$content = @file_get_contents('/opt/bitnami/apps/resourcespace/htdocs/filestore/block.txt');
		
	$data = array();
	$data['progress'] = 0;
	$data['time'] = 0;
	$data['duration'] = 0;
	$data['content'] = false;
 
	if($content){
		$data['content'] = true;
	    //get duration of source
	    preg_match("/Duration: (.*?), start:/", $content, $matches);

	    $rawDuration = $matches[1];

	    //rawDuration is in 00:00:00.00 format. This converts it to seconds.
	    $ar = array_reverse(explode(":", $rawDuration));
	    $data['duration'] = floatval($ar[0]);
	    if (!empty($ar[1])) $data['duration'] += intval($ar[1]) * 60;
	    if (!empty($ar[2])) $data['duration'] += intval($ar[2]) * 60 * 60;

	    //get the time in the file that is already encoded
	    preg_match_all("/time=(.*?) bitrate/", $content, $matches);

	    $rawTime = array_pop($matches);

	    //this is needed if there is more than one match
	    if (is_array($rawTime)){$rawTime = array_pop($rawTime);}

	    //rawTime is in 00:00:00.00 format. This converts it to seconds.
	    $ar = array_reverse(explode(":", $rawTime));
	    $data['time'] = floatval($ar[0]);
	    if (!empty($ar[1])) $data['time'] += intval($ar[1]) * 60;
	    if (!empty($ar[2])) $data['time'] += intval($ar[2]) * 60 * 60;

	    //cropendtime
	    //if ($cropendtime!=null)
	    if ($cutstart!=$cutend)
	    {

			$cropendtime = date("H:i:s", $cutend);
			$cropstarttime = date("H:i:s", $cutstart);

	    	$ar = array_reverse(explode(":", $cropendtime));
			$data['cropendtime'] = floatval($ar[0]);
	    	if (!empty($ar[1])) $data['cropendtime'] += intval($ar[1]) * 60;
	    	if (!empty($ar[2])) $data['cropendtime'] += intval($ar[2]) * 60 * 60;

	    	$ar = array_reverse(explode(":", $cropstarttime));
			$data['cropstarttime'] = floatval($ar[0]);
	    	if (!empty($ar[1])) $data['cropstarttime'] += intval($ar[1]) * 60;
	    	if (!empty($ar[2])) $data['cropstarttime'] += intval($ar[2]) * 60 * 60;

	    	//cut end-start == cut duration
	    	//duration - cutduration
	    	
	    	//calculate the progress
		    //$data['progress'] = round((($data['time']-$data['cropstarttime'])/($data['cropendtime']-$data['cropendtime'])) * 100);
			if ($data['cropendtime'] >= $data['duration'])
				$data['cropendtime'] = $data['duration'];

		    $data['progress'] = round(($data['time']/($data['cropendtime']-$data['cropstarttime'])) * 100);
	    } else {
		    //calculate the progress
		    $data['progress'] = round(($data['time']/$data['duration']) * 100);
		}
	}
	return $data;
}

function filter_time($value) {
	$p1 = '/^(0?\d|1\d|2[0-3]):[0-5]\d:[0-5]\d$/';
	$p2 = '/^(0?\d|1[0-2]):[0-5]\d\s(am|pm)$/i';
	$res = preg_match($p1, $value) || preg_match($p2, $value);
	if ($res)
	{
		$res = date('H:i:s', strtotime($value));
	}
	else
	{
		return false;
		exit;
	}
	return $res;
}

function get_pid(){

	$pscmd = "ps aux | grep [f]fmpeg | awk '{print \$2}'";
	exec($pscmd, $result);
	if (isset($result[0]) && $result[0]!="")
	{ 		
		return $result[0];
	}

	return false;
}

function get_last_line($file){
	$line = '';

	$f = fopen($file, 'r');
	$cursor = -1;

	fseek($f, $cursor, SEEK_END);
	$char = fgetc($f);

	/**
	 * Trim trailing newline chars of the file
	 */
	while ($char === "\n" || $char === "\r") {
	    fseek($f, $cursor--, SEEK_END);
	    $char = fgetc($f);
	}

	/**
	 * Read until the start of file or first newline char
	 */
	while ($char !== false && $char !== "\n" && $char !== "\r") {
	    /**
	     * Prepend the new char
	     */
	    $line = $char . $line;
	    fseek($f, $cursor--, SEEK_END);
	    $char = fgetc($f);
	}

	return $line;
}

function check_access(){
	global $usergroup;
	global $transcode_restricteduse_groups;

	// verify that the requested ResourceID is numeric.
	$ref = $_POST['ref'];
	if (!is_numeric($ref)){ echo "Error: non numeric ref."; exit; }
	# Load edit access level
	$edit_access=get_edit_access($ref);
	# Load download access level
	$access=get_resource_access($ref);
	$transcoderestricted=in_array($usergroup,$transcode_restricteduse_groups);
	$original = $_POST['original'];
	// if they can't download this resource, they shouldn't be doing this
	// also, if they are trying to modify the original but don't have edit access
	// they should never get these errors, because the links shouldn't show up if no perms
	if ($access!=0 || ($original && !$edit_access)){
		include "../../../include/header.php";
		echo "Permission denied.";
		include "../../../include/footer.php";
		exit;
	} 
	# Is this a download only?
	$download=(getval("download","")!="");
	if (!$download && !$edit_access){
		include "../../../include/header.php";
		echo "Permission denied.";
		include "../../../include/footer.php";
		exit;
	}		
}


//**
// GET DURATION
//**

if (isset($_POST['action']) && $_POST['action'] == 'getduration')
{
	// get input file (original)
	$ref = $_POST['ref'];		
	$orig_ext = sql_value("select file_extension value from resource where ref = '$ref'",'');
	$input = get_resource_path($ref,true,'',false,$orig_ext);

	$cmd = "ffmpeg -i ".$input." 2>&1 | grep -o -P '(?<=Duration: ).*?(?=,)'";
	exec($cmd, $result, $status);

	header('Content-Type: application/json');
	echo json_encode(array('status'=>'success', 'result'=>$result[0] ));
	exit;
}

//**
// SAVE TRANSCODE
//**

if (isset($_POST['action']) && $_POST['action'] == 'savetranscode')
{
	$tmpdir = get_temp_dir();
	$resource = $_POST['ref'];
	$ref = add_alternative_file($resource,"description");		

	global $_FILES;
		$_FILES = array( 
					'userfile' => array( 
						'name' => 'test.mp4', 
						'tmp_name' => $tmpdir.'/test.mp4'
						/*'type' => 'text/plain', 
						'size' => 42, 
						'error' => 0 */
					) 
				);

	save_alternative_file($resource,$ref);

	header('Content-Type: application/json');
	echo json_encode(array('status'=>'success' ));
	exit;
}

//**
// STOP TRANSCODE
//**

if (isset($_POST['action']) && $_POST['action'] == 'stoptranscode')
{
	$pscmd = "kill ".$_POST['pid'];
	exec($pscmd, $result);

	header('Content-Type: application/json');
	echo json_encode(array('status'=>'success'));
	exit;
}

//**
// TRANSCODE
//**

if (isset($_POST['action']) && $_POST['action'] == 'dotranscode')
{
	$logfile = '/opt/bitnami/apps/resourcespace/htdocs/filestore/block.txt';
	$tmpdir = get_temp_dir();
	
	//due to ajax/html we have to typecheck with ===
	//booleans are strings after ajax
	$initiated = false;
	if (isset($_POST['initiated']) && $_POST['initiated']==='true')
	{
		$initiated = true;
		//echo 'initiated';
	}

	//first the post data so we overwrite the arrays
	foreach ($_POST as $key => $value) 
	{
		if (isset($$key)) continue;
		$return_array[$key] = $value;
	}

	// get ffmpeg process id
	$pid = get_pid();
	if ($pid)
		$return_array['pid'] = $pid;


	if (!$initiated && !$pid)
	{
		check_access();

		//if ($config_windows)
	  	//{
  		//	# Windows systems have a hard time with the long paths used for video generation. This work-around creates a batch file containing the command, then executes that.
		//  	file_put_contents(get_temp_dir() . "/ffmpeg.bat",$shell_exec_cmd);
		//  	$shell_exec_cmd=get_temp_dir() . "/ffmpeg.bat";
	  	//}

		$ffmpeg_fullpath = get_utility_path("ffmpeg");

		// set splice string
		$cutvalue = "";		
		$cut = false;
		
		$time = explode(';', $_POST['cutrange']);
		//$starttime=filter_time($_POST['cutstart']);
		//$endtime=filter_time($_POST['cutend']);
		$starttime = date('H:i:s', $time[0]);
		$endtime = date('H:i:s', $time[1]);

		//if ($starttime!==false && $endtime!==false && $starttime!=$endtime)
		if ($starttime!=$endtime)
		{
			$cut = true;
			$cutvalue = "-ss ".$starttime." -to ".$endtime;
		}

		// get input file (original)
		$ref = $_POST['ref'];		
		$orig_ext = sql_value("select file_extension value from resource where ref = '$ref'",'');
		$input = get_resource_path($ref,true,'',false,$orig_ext);

		// set crop string
		$cropvalue = "";		
		if (isset($_POST['autocrop']))
		{
			$time = explode(';', $_POST['autocropseek']);

			//cropdetect			
			$result = exec($ffmpeg_fullpath." -i ".$input." -ss ".$time[0]." -to ".$time[1]." -vf cropdetect -f null - 2>&1 | awk '/crop/ { print \$NF }' | tail -1");
			if ($result!="")
			{
				$return_array['ffmpeg']['cropabort'] = false;
				$tmparr = explode('crop=',$result); 
				$cropvalue = $tmparr[1];
				$cropvalue = '-filter:v crop='.$cropvalue;				
			} else {
				// no crop result
				$return_array['ffmpeg']['cropabort'] = true;

				//run cmd again log to file
				$result = exec($ffmpeg_fullpath." -i ".$input." -ss ".$time[0]." -to ".$time[1]." -vf cropdetect -f null 1> ".$logfile." - 2>&1");

				// wait until all data is written to output file
				sleep(1);

				//check log file why proces is not running..
				$line = get_last_line($logfile);

				//if (preg_match("/already exists/i", $line))
				$return_array['ffmpeg']['reason'] = $line;
			}
		}
		
		// finalize trancode command
		// $! returns the pid of the last backgrounded process >> http://stackoverflow.com/questions/4107936/php-background-process-pid-problem		
		$cmd = 'nohub | '.$ffmpeg_fullpath.' -y -i '.$input.' '.$cropvalue.' '.$cutvalue.' '.$_POST['ffmpegcmd'].' '.$tmpdir.'/test.mp4 1> '.$logfile.' 2>&1 & echo ${!};'; // awk '{echo $2}'
		exec($cmd, $result, $status);
		
		$return_array['ffmpeg']['status'] = $status;		
		if(preg_match_all('/\d+/', $result[0], $numbers))
		{
			if (!isset($return_array['pid']) || $return_array['pid']=="")
    			$return_array['pid'] = end($numbers[0]);
    	}

		$return_array['initiated'] = false;
		$return_array['cut'] = $cut;
		$return_array['cutend'] = $endtime;
		$return_array['cutstart'] = $starttime;
		$return_array['cropvalue'] = $cropvalue;

	} else {	
		
		$time = explode(';', $_POST['cutrange']);

		//poll the output file for progress
		//$data = getProgress($_POST['cutend'], $_POST['cutstart']);
		$data = getProgress($time[0], $time[1]);

		if ($data['content'])
		{
			//set the trancoding variables
			$return_array['status'] = 'transcoding';
			$return_array['ffmpeg']['duration'] = $data['duration'];
			$return_array['ffmpeg']['time'] = $data['time'];
			$return_array['ffmpeg']['progress'] = $data['progress'];			

		    if ($data['progress'] >= 100)
		    	$return_array['status'] = 'done';
		}
		
		if (!$pid && $data['progress']!=100)
		{	
			//no pid and not complete
			$return_array['status'] = "aborted";			
			
			//check log file why proces is not running..
			$line = get_last_line($logfile);

			//if (preg_match("/already exists/i", $line))
			$return_array['ffmpeg']['reason'] = $line;
		}
	}	

	header('Content-Type: application/json');
	echo json_encode($return_array);
		
} 

?>
