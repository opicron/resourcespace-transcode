<?php

include_once "../../../include/db.php";
include_once "../../../include/authenticate.php";
include_once "../../../include/general.php";
include_once "../../../include/resource_functions.php";
include_once "../../../include/image_processing.php";

//include_once "../include/transform_functions.php";

// verify that the requested ResourceID is numeric.
$ref = $_REQUEST['ref'];
if (!is_numeric($ref)){ echo "Error: non numeric ref."; exit; }

# Load edit access level
$edit_access=get_edit_access($ref);

# Load download access level
$access=get_resource_access($ref);

$transcoderestricted=in_array($usergroup,$transcode_restricteduse_groups);

// are they requesting to change the original?
if (isset($_REQUEST['mode']) && strtolower($_REQUEST['mode']) == 'original'){
    $original = true;
} else {
    $original = false;
}

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

#action code 
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'dotranscode')
{


	//exit();
	
	//back to view
	//header("Location:../../../pages/view.php?ref=$ref\n\n");
	//exit;

} //else {

	# main form
	include "../../../include/header.php";

	if (!$original)
	{
		if (!hook('replacebacklink')) {
			$search=getvalescaped("search","");
			$offset=getvalescaped("offset",0,true);
			$order_by=getvalescaped("order_by","relevance");
			$sort=getval("sort",$default_sort);
			$archive=getvalescaped("archive",0,true);
			?><p><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["backtoresourceview"]?></a></p><?php
		}
	} else {
		?>
		<p><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/edit.php?ref=<?php echo urlencode($ref)?>">&lt;&nbsp;<?php echo $lang["backtoeditresource"]?></a></p>
		<?php		
	}
	?>

	<H2>Transcode <?php echo ($original==true) ? "original " : ""; ?>video</H2>
	<p>
	</p>
	<form name="transcodeform" id='transcodeform' method="post" action="<?php echo $baseurl_short ?>plugins/transcode/pages/do_transcode.php">

	<input type='hidden' name='pid' id='pid' value='' />
	<input type='hidden' name='ref' id='ref' value='<?php echo $ref; ?>' />
	<input type='hidden' name='action' value='dotranscode' />
	<input type='hidden' name='original' id='original' value='<?php echo $original; ?>' />

	<table width="600px">
	</tr>

	<tr>
	 <td valign="top">
	  <label for="ffmpegcmd">FFMPEG command</label>
	 </td>
	 <td valign="top">
	  <input  type="text" name="ffmpegcmd" maxlength="150" size="60" value="-preset superfast -threads 0 -acodec aac -ac 1 -strict experimental -vcodec libx264 -ab 64k">
	  <!--<textarea wrap="hard" name="ffmpegcmd" style="width:400px" maxlength="150" size="60" rows="2">
-preset superfast -threads 0 -acodec aac -ac 1 -strict experimental -vcodec libx264 -ab 64k
	  </textarea>-->
	  <!-- -acodec libfaac -->
	 </td>
	</tr>

	<tr>
		<td>
 		 <label for="title">Alternative title</label>
		</td>
	 <td id="buttons" style="text-align:left">
	 	<input value="" type="text" name="title" maxlength="50" size="30">	  	
	  <!--<input id="view" disabled="true" type="button" value="View"/>-->
	 </td>
	</tr>

	<tr>
	 <td valign="top">
	 	<br/>
	 </td>
	</tr>
	
	<!--
	<tr>
	 <td valign="top">
	  Cut video
	 </td>
	 <td valign="top">
	  <input type="text" name="cutstart" maxlength="10" size="2" value="00:00:00">
	  to <input type="text" name="cutend" maxlength="10" size="2" value="">
	  (hh:mm:ss)
	 </td>
	</tr> 
	-->

	<tr>
	 <td valign="top">
	  Cut video
	 </td>
	 <td valign="top" style="">
	  <input type="text" id="cutrange" name="cutrange" maxlength="10" size="2" value="">	  
	 </td>
	</tr> 

	<tr>
	 <td valign="top">
	 	<br/>
	 </td>
	</tr>

	<tr>
	 <td valign="top">
	  <label for="autocrop">Autocrop</label>
	  <input checked="checked" type="checkbox" id="autocrop" name="autocrop" value="autocrop">
	 </td>
	 <td valign="center">	 	
  		<input width="10" type="text" id="autocropseek" name="autocropseek" maxlength="10" size="2" value="">	  
	 </td>
	</tr>

	<tr>
	 <td valign="top">
	 	<br/>
	 </td>
	</tr>

	<tr>
	 <td valign="top">
	 	<br/>
	 </td>
	</tr>

	<tr>
	<td>
		<input id="submit" type="submit" value="Transcode"/>
	  	<input id="stop" type="button" value="Abort"/>
	</td>
	 <td>
	 	<div id="progressBar" class="jquery-ui-like"><div></div></div>
	 </td>
	</tr>
	
	</table>
	</form>

	<p>
	</p>

	

	<div id="test" style="color:#DDD;">
	</div>

	<!--
	<div style="display:none;">
		<div id="video-lightbox"> 
		</div>
	</div>
	-->


	<?php
	include "../../../include/footer.php";
?>

<script type="text/javascript" language="javascript">

	jQuery(document).ready(function()
	{

		function progress(percent, element) {
		    var progressBarWidth = percent * jQuery(element).width() / 100;
		    jQuery(element).find('div').animate({ width: progressBarWidth }, 500).html(percent + "%&nbsp;");
		    if (percent==100)
		    {		    	
		    	jQuery(element).find('div').animate({ opacity: 1 }, 10,function(){	
        				jQuery(element).find('div').css("background-image","none");
			   	});
			}
			if (percent==0)
			{
				jQuery(element).find('div').animate({ opacity: 1 }, 10,function(){	
        				jQuery(element).find('div').css("background-image","url(../gfx/pbar-ani.gif)");
			   	});	
			}
		}

		function spinner(count, element)
		{
			if (isNaN(count))
				count = 0;

		 	count = parseInt(count)+1;
		 	if (count > 3)
		 		count = 0;

			var html = jQuery(element).text();

    		html = html.slice(0,-1);		
    		var postfix = "";	    		
    		switch(parseInt(count)) {
    			case 0: postfix = '/'; break;
    			case 1: postfix = '\\'; break;
    			case 2: postfix = '/'; break;
    			case 3: postfix = '\\'; break;
    			//case 4: postfix = '/'; break;
    			//case 5: postfix = '/'; break;
    		}
			jQuery(element).html('<p>'+html+postfix+'</p>');    		

			return count;
		}	

		(function($){
			$.fn.serializeObject = function () {
				"use strict";

				var result = {};
				var extend = function (i, element) {
					var node = result[element.name];

			// If node with same name exists already, need to convert it to an array as it
			// is a multi-value field (i.e., checkboxes)

					if ('undefined' !== typeof node && node !== null) {
						if ($.isArray(node)) {
							node.push(element.value);
						} else {
							result[element.name] = [node, element.value];
						}
					} else {
						result[element.name] = element.value;
					}
				};

				$.each(this.serializeArray(), extend);
				return result;
			};
		})(jQuery);		

		function ObjectDump(obj, name) {
		  this.result = "[ " + name + " ]\n";
		  this.indent = 0;
		 
		  this.dumpLayer = function(obj) {
		    this.indent += 2;
		 
		    for (var i in obj) {
		      if(typeof(obj[i]) == "object") {
		        this.result += "\n" +
		          "              ".substring(0,this.indent) + i +
		          ": " + "\n";
		        this.dumpLayer(obj[i]);
		      } else {
		        this.result +=
		          "              ".substring(0,this.indent) + i +
		          ": " + obj[i] + "\n";
		      }
		    }
		 
		    this.indent -= 2;
		  }
		 
		  this.showResult = function() {
		    var pre = document.createElement('pre');
		    pre.innerHTML = this.result;
		    document.body.appendChild(pre);
		   	jQuery('pre').attr('style',"font-family: terminal; text-align: left; padding-left: 800px; font-size: 16px;");
		  }
		 
		  this.dumpLayer(obj);
		  this.showResult();
		} // end object dump

		function saveTranscode()
		{
			var formData = {};
			formData.action = 'savetranscode';
			formData.ref = jQuery('input[name=ref]').val();
			formData.original = jQuery('input[name=original]').val();
			formData.name = jQuery('input[name=title]').val();
			formData.description = "generated by Transcode";
			//getvalescaped("alt_type","")
		
			jQuery.ajax({
	 	        //url: formURL,
	 	        url: "/resourcespace/plugins/transcode/pages/do_transcode.php",
		    	type: "POST",
		        data: formData,
		        dataType: "json",
			    success: function(data) {			    	
			    	//ObjectDump(data,'data');			    			    	
			    },
		        error: function() {
	    	    	alert('error');
	      		}
		    });
		    return false;
		}

		/*
		function createPlayerDOM() {
		    var playerContainer = null;
		    playerContainer = document.createElement('div');
		    var playerDOM = null;
		    playerDOM = document.createElement('video');
		    playerDOM.id = 'video-player';
		    playerDOM.width = 480;
		    playerDOM.height = 278;
		    playerDOM.className += 'video-js ';
		    playerDOM.className += 'vjs-default-skin ';
		    playerDOM.setAttribute('preload', 'meta_data');
		    playerDOM.controls = true;
		    // Create the video sources
		    var video_mp4 = document.createElement('source');
		    video_mp4.setAttribute('src', '/resourcespace/filestore/tmp/test.mp4');
		    video_mp4.setAttribute('type', 'video/mp4');
		    //var video_webm = document.createElement('source');
		    //video_webm.setAttribute('src', 'http://video-js.zencoder.com/oceans-clip.webm');
		    //video_webm.setAttribute('type', 'video/webm');
		    //var video_ogg = document.createElement('source');
		    //video_webm.setAttribute('src', 'http://video-js.zencoder.com/oceans-clip.ogv');
		    //video_webm.setAttribute('type', 'video/ogg');
		    playerDOM.appendChild(video_mp4);
		    //playerDOM.appendChild(video_webm);
		    //playerDOM.appendChild(video_ogg);
		    playerContainer.appendChild(playerDOM);
		    return playerContainer;
		}
		*/
		
		// solve bug where buttons are disabled..
		jQuery('#submit').attr('disabled',false);
		//jQuery('#view').attr('disabled',true);
		jQuery('#stop').attr('hidden',true);

		//get ffmpeg duration!
		function getDuration()
		{
			var formData = {};
			formData.ref = jQuery('input[name=ref]').val();
			formData.action = 'getduration';
			//alert('test');
			jQuery.ajax({
	 	        url: "/resourcespace/plugins/transcode/pages/do_transcode.php",
		    	type: "POST",
		        data: formData,
		        dataType: "json",
			    success: function(data) {			    	
			    	//ObjectDump(data,'data');			    			    	
			    	jQuery("#cutrange").ionRangeSlider({
						type: "double",
					    min: +moment.utc("0", "X").format("X"),
			    		max: +moment.utc("01-01-1970 "+data.result, "D-M-YYYY HH:mm:ss").format("X"),
			    		from: +moment.utc("0", "X").format("X"),
			    		to: +moment.utc("0", "X").format("X"),
			    		grid: true,
			    		grid_num: 4,
			    		prettify: function (num) {
			        		return moment.utc(num, "X").format("HH:mm:ss");
			    		}
					});
			    },
		        error: function() {
	    	    	alert('error');
	      		}
		    });
		    return false;
		}

		getDuration();

		jQuery("#autocropseek").ionRangeSlider({
			type: "double",
		    min: +moment.utc("0", "X").format("X"),
    		max: +moment.utc("60", "X").format("X"),
    		from: +moment.utc("4", "X").format("X"),
    		to: +moment.utc("8", "X").format("X"),
    		grid: true,
    		grid_num: 2,
    		prettify: function (num) {
        		return moment.utc(num, "X").format("HH:mm:ss");
    		},
    		onChange: function (data) {
      		 	//ObjectDump(data, 'data');
      		 	//alert(data.from);
      		 	//var slider = jQuery("#autocropseek").data("ionRangeSlider");
      			//slider.update({disable:false, to: data.to});      			
		    }
		});

		jQuery("#autocrop").change(function(){

			var slider = jQuery("#autocropseek").data("ionRangeSlider");
			
			if (jQuery('#autocrop').is(":checked"))
			{
				//slider.update({disable: false});
				slider.update({disable:false, from: 4, to: 8});
			}
			else
			{
				//slider.update({disable: true});
				slider.update({disable:true, from: 0, to: 0});
			}

		});



		/*
		jQuery("#view").live('click',function(){		
			jQuery.featherlight('#video-lightbox', {
				closeOnClick: 'background',
			    afterContent: function (event) {
			        //console.log("open");
			        jQuery('.featherlight-inner').append(createPlayerDOM());
			        player = videojs('#video-player', { "autoHeight": "true", "aspectRatio": 'auto', "controls": true, "autoplay": true, "preload": "meta_data"}, function () {
			            //console.log('Good to go!');
			            // if you don't trust autoplay for some reason
			        });
			        player.volume(0.1);
			    },
			    afterOpen: function (event) {
			    },
			    beforeClose: function (event) {
			        player.pause();
			        player.dispose();
			}});
        });
		*/
	
		//submit do_transcode form
		jQuery("#transcodeform").submit(function(e)
		{	
			GetTranscodeProgress(jQuery("#transcodeform").serializeObject(), false);
			e.preventDefault(); 
		    //e.unbind();

		    return false;
		});

		//stop transcode
		jQuery("#stop").click(function(e)
		{	
			var formData = {};
			formData.pid = jQuery('input[name=pid]').val();
			formData.action = 'stoptranscode';
			//alert('test');
			jQuery.ajax({
	 	        //url: formURL,
	 	        url: "/resourcespace/plugins/transcode/pages/do_transcode.php",
		    	type: "POST",
		        //data: jQuery.param(formData), //formData, 
		        //data: JSON.stringify(formData),
		        data: formData,
		        dataType: "json",
			    success: function(data) {			    	

			    	//ObjectDump(data,'data');			    			    	
			    },
		        error: function() {
	    	    	alert('error');
	      		}
		    });
		    return false;
		});

		//function GetTranscodeProgress(formPointer, loop) {
		function GetTranscodeProgress(formData, loop) {
			// TODO: take form pointers and data out function
			//var formData = jQuery("#transcodeform").serialize();
		    //var formURL = jQuery(formPointer).attr("action");
		    //var formData = jQuery("#transcodeform").serializeArray();

			if (loop==true)
				formData.initiated = true;
			else 
			{
				formData.initiated = false;				
				//set button status							
				jQuery("#test").html('<p>:: Initializing</p>');			 	
			 	jQuery('#submit').attr('hidden',true);
			 	//jQuery('#view').attr('disabled',true);
			 	jQuery('#stop').attr('hidden',false);			 	
			}
			
		    jQuery.ajax({
	 	        //url: formURL,
	 	        url: "/resourcespace/plugins/transcode/pages/do_transcode.php",
		    	type: "POST",
		        //data: jQuery.param(formData), //formData, 
		        //data: JSON.stringify(formData),
		        data: formData,
		        dataType: "json",
			    success: function(data) {			    	

			    	//ObjectDump(data,'data');			    	

			    	//if pid set write it to form
			    	if (data.pid)
			    	{
						jQuery('input[name=pid]').val(data.pid);
			    	}

			    	//if (data.initiated==false) alert(data.initiated);
			    	if (
			    		// initial call
			    		data.initiated==false || 
			    		// transcoding
			    		(data.status=='transcoding') 
			    		// && data.ffmpeg.time != 0 && data.ffmpeg.progress != 0
			    	){
			    		//if (data.initiated=="true")
			    		//	progress(data.ffmpeg.progress, '#progressBar');
			    		
			    		if (data.initiated==false)
							progress(0, '#progressBar');
						//else, to avoid showing progress bar on first run
						else if (jQuery("#progressBar").is(':hidden'))
			    		{
			    			//show progress bar
							jQuery("#progressBar").fadeIn(2000);
							jQuery("#test").html('<p>:: Transcoding video&nbsp;&nbsp;&nbsp;</p>');			    			
			    		}

			    		if (jQuery("#progressBar").is(':visible'))
			    		{
				    		progress(data.ffmpeg.progress, '#progressBar');				    		
				    		data.spinner = spinner(data.spinner, "#test");
				    	}

						jQuery.doTimeout(2000, function() {
							//TODO: pass cut and cutendtime through..
							//maybe pass on data result from json
							//from object to serialized string
							//de-serialize in start function..							
							GetTranscodeProgress(data, true);
						});

						//skip rest of function
						return;
					
					} else if (data.status=='done' && data.ffmpeg.progress == 100) {

						// FINISHED
						saveTranscode();
						
			    		progress(data.ffmpeg.progress, '#progressBar');
			    		//var link = '<input id="view" type="button" value="View"/>';
			    		jQuery("#test").html('<p>:: Transcode completed</p>');						
			    		
			    		// enable buttons
			    		//jQuery('#view').attr('disabled',false);
			    		jQuery("#progressBar").find('div').animate({ opacity: 1 }, 1000,function(){	
			    			jQuery("#progressBar").fadeOut(2000);
			    		});
			    	
		    		} else if (data.status=='aborted') {	

			    		//hide background
						jQuery("#progressBar").find('div').animate({ opacity: 1 }, 1000,function(){	
	        				jQuery("#progressBar").find('div').css("background-image","none");
				   		});

			    		jQuery("#test").html('<p>:: Transcode aborted ['+data.ffmpeg.reason+']</p>');
			    		jQuery("#progressBar").fadeOut(2000);

					} else {

						jQuery("#test").html('<p>:: Failed! Reasons unknown</p>');						
						jQuery("#progressBar").fadeOut(2000);

					}
					//reset buttons
					jQuery('#stop').attr('hidden',true);
					jQuery('#submit').attr('hidden',false);
		    	
			    },
		        error: function() {
	    	    	alert('error');
	      		}
		    });
		}
		

	});
</script>

