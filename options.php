<?php
include('wpframe.php');

if(isset($_REQUEST['submit']) and $_REQUEST['submit']) {
	$options = array('show_answers', 'single_page');
	foreach($options as $opt) {
		if(isset($_POST[$opt])) update_option('quizzin_' . $opt, 1);
		else update_option('quizzin_' . $opt, 0);
	}
	showMessage("Options updated");
}
?>
<div class="wrap">
<h2>Quizzin Settings</h2>

<form action="" method="post">

<?php showOption('single_page', 'Show all questions in a <strong>single page</strong>'); ?>
<?php showOption('show_answers', '<strong>Show correct answers</strong> at the end of the quiz.'); ?>

<p class="submit">
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php e('Save Options') ?>" style="font-weight: bold;" />
</p>

</form>

</div>

<?php
function showOption($option, $title) {
?>
<input type="checkbox" name="<?php echo $option; ?>" value="1" id="<?php echo $option?>" <?php if(get_option('quizzin_'.$option)) print " checked='checked'"; ?> />
<label for="<?php echo $option?>"><?php e($title) ?></label><br />

<?php
}
