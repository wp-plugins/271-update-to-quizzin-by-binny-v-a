<?php
require('wpframe.php');
stopDirectCall(__FILE__);

if($_REQUEST['message'] == 'updated') showMessage('Quiz Updated');

if($_REQUEST['action'] == 'delete') {
	$wpdb->get_results("DELETE FROM wp_quiz_quiz WHERE ID='$_REQUEST[quiz]'");
	$wpdb->get_results("DELETE FROM wp_quiz_answer WHERE question_id=(SELECT ID FROM wp_quiz_question WHERE quiz_id='$_REQUEST[quiz]')");
	$wpdb->get_results("DELETE FROM wp_quiz_question WHERE quiz_id='$_REQUEST[quiz]'");
	showMessage("Quiz Deleted");
}
?>

<div class="wrap">
<h2><?php e("Manage Quiz"); ?></h2>

<?php
wp_enqueue_script( 'listman' );
wp_print_scripts();
?>

<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><div style="text-align: center;"><?php e('ID') ?></div></th>
		<th scope="col"><?php e('Title') ?></th>
		<th scope="col"><?php e('Number Of Questions') ?></th>
		<th scope="col"><?php e('Created on') ?></th>
		<th scope="col" colspan="3"><?php e('Action') ?></th>
	</tr>
	</thead>

	<tbody id="the-list">
<?php
// Retrieve the quizes
$all_quiz = $wpdb->get_results("SELECT ID,name,added_on FROM `wp_quiz_quiz` ");

if (count($all_quiz)) {
	foreach($all_quiz as $quiz) {
		$class = ('alternate' == $class) ? '' : 'alternate';
		
		print "<tr id='quiz-{$quiz->ID}' class='$class'>\n";
		?>
		<th scope="row" style="text-align: center;"><?php echo $quiz->ID ?></th>
		<td><?php echo stripslashes($quiz->name)?></td>
		<td><?php echo $quiz->question_count ?></td>
		<td><?php echo date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($quiz->added_on)) ?></td>
		<td><a href='edit.php?page=quizzin/question.php&amp;quiz=<?php echo $quiz->ID?>' class='edit'><?php e('Manage Questions')?></a></td>
		<td><a href='edit.php?page=quizzin/quiz_form.php&amp;quiz=<?php echo $quiz->ID?>&amp;action=edit' class='edit'><?php e('Edit'); ?></a></td>
		<td><a href='edit.php?page=quizzin/quiz.php&amp;action=delete&amp;quiz=<?php echo $quiz->ID?>' class='delete' onclick="return confirm('<?php echo  addslashes(t("You are about to delete this quiz? This will delete all the questions and answers within this quiz. Press 'OK' to delete and 'Cancel' to stop."))?>');"><?php e('Delete')?></a></td>
		</tr>
<?php
		}
	} else {
?>
	<tr>
		<td colspan="5"><?php e('No Quizes found.') ?></td>
	</tr>
<?php
}
?>
	</tbody>
</table>

<a href="edit.php?page=quizzin/quiz_form.php&amp;action=new"><?php e("Create New Quiz")?></a>
</div>
