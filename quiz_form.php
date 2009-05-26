<?php
require('wpframe.php');
stopDirectCall(__FILE__);

$action = 'new';
if($_REQUEST['action'] == 'edit') $action = 'edit';

$dquiz = array();
if($action == 'edit') {
	$dquiz = $wpdb->get_row($wpdb->prepare("SELECT name,description,final_screen FROM wp_quiz_quiz WHERE ID=%d", $_REQUEST['quiz']));
	$final_screen = stripslashes($dquiz->final_screen);
} else {
	$final_screen = t("Congratulations - you have completed %%QUIZ_NAME%%.\n\nYou scored %%SCORE%% out of %%TOTAL%%.\n\nYour performance have been rated as '%%RATING%%'");
}

?>

<div class="wrap">
<h2><?php echo t(ucfirst($action) . " Quiz"); ?></h2>
<input type="hidden" id="title" name="ignore_me" value="This is here for a workaround for a editor bug" />

<?php
wp_admin_css('thickbox');
wp_print_scripts('jquery-ui-core');
wp_print_scripts('jquery-ui-tabs');
wp_print_scripts('post');
wp_print_scripts('editor');
if(function_exists('add_thickbox')) add_thickbox();
wp_print_scripts('media-upload');
if (function_exists('wp_tiny_mce')) wp_tiny_mce();
?>

<form name="post" action="<?php echo $GLOBALS['wpframe_plugin_folder'] ?>/quiz_action.php" method="post" id="post">
<div id="poststuff">
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">

<div class="postbox">
<div class="handlediv" title="Click to toggle"></div>
<h3 class="hndle"><span><?php e('Quiz Name') ?></span></h3>
<div class="inside">
<input type='text' name='name' value='<?php echo stripslashes($dquiz->name); ?>' />
</div></div>

<div class="postbox">
<div class="handlediv" title="Click to toggle"></div>
<h3 class="hndle"><span><?php e('Description') ?></span></h3>
<div class="inside">
<textarea name='description' rows='5' cols='50' style='width:100%'><?php echo stripslashes($dquiz->description); ?></textarea>
</div></div>

<div class="postbox">
<div class="handlediv" title="Click to toggle"></div>
<h3 class="hndle"><span><?php e('Final Screen') ?></span></h3>
<div class="inside">
<?php the_editor($final_screen); ?>

<p><strong><?php e('Usable Variables...') ?></strong></p>
<table>
<tr><th style="text-align:left;"><?php e('Variable') ?></th><th style="text-align:left;"><?php e('Value') ?></th></tr>
<tr><td>%%SCORE%%</td><td><?php e('The number of correct answers') ?></td></tr>
<tr><td>%%TOTAL%%</td><td><?php e('Total number of questions') ?></td></tr>
<tr><td>%%PERCENTAGE%%</td><td><?php e('Correct answer percentage') ?></td></tr>
<tr><td>%%GRADE%%</td><td><?php e('1-10 value. 1 is 10% or less, 2 is 20% or less, and so on') ?>.</td></tr>
<tr><td>%%WRONG_ANSWERS%%</td><td><?php e('Number of answers you got wrong') ?></td></tr>
<tr><td>%%RATING%%</td><td><?php e("A rating of your performance - it could be 'Failed'(0-39%), 'Just Passed'(40%-50%), 'Satisfactory', 'Competent', 'Good', 'Excellent' and 'Unbeatable'(100%)") ?></td></tr>
<tr><td>%%QUIZ_NAME%%</td><td><?php e('The name of the quiz') ?></td></tr>
<tr><td>%%DESCRIPTION%%</td><td><?php e('The text entered in the description field.') ?></td></tr>
</table>
</div>
</div></div>


<?php
// I'll put 2 editors here - as soon as 'http://wordpress.org/support/topic/179110?replies=2' bug is fixed.
?>


<p class="submit">
<?php wp_nonce_field('quizzin_create_edit_quiz'); ?>
<input type="hidden" name="action" value="<?php echo $action; ?>" />
<input type="hidden" name="quiz" value="<?php echo $_REQUEST['quiz']; ?>" />
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php e('Save') ?>" style="font-weight: bold;" tabindex="4" />
</p>

</div>
</form>

</div>
