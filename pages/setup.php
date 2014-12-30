<?php
#
# Setup page for transform plugin
#

// Do the include and authorization checking ritual.
include '../../../include/db.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}
include '../../../include/general.php';

// Specify the name of this plugin, the heading to display for the page.
$plugin_name = 'transcode';
$page_heading = "Transcode configuration";

// Build the $page_def array of descriptions of each configuration variable the plugin uses.
#$page_def[] = config_add_text_input('cropper_default_target_format', 'Default Target Format');
$page_def[] = config_add_boolean_select('transcode_original', "Transcode original?");
$page_def[] = config_add_text_list_input('transcode_allowed_extensions', "Allowed transcode file formats");

// Commented out lines above that either don't seem to work or I'm unsure how to test

// Do the page generation ritual
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $page_heading);
include '../../../include/footer.php';
