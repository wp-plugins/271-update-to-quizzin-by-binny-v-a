<?php
require_once('wpframe.php');
global $wpdb;
$GLOBALS['wpframe_plugin_name'] = basename(dirname(__FILE__));
$GLOBALS['wpframe_plugin_folder'] = $GLOBALS['wpframe_home'] . '/wp-content/plugins/' . $GLOBALS['wpframe_plugin_name'];

$all_question = $wpdb->get_results($wpdb->prepare("SELECT ID,question,explanation FROM wp_quiz_question WHERE quiz_id=%d ORDER BY ID", $quiz_id));
if($all_question) {
	if(!isset($GLOBALS['quizzin_client_includes_loaded'])) {
?>
<link type="text/css" rel="stylesheet" href="<?php echo $GLOBALS['wpframe_plugin_folder']?>/style.css" />
<script type="text/javascript" src="<?php echo $GLOBALS['wpframe_home']?>/wp-includes/js/jquery/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['wpframe_plugin_folder']?>/script.js"></script>
<?php
	$GLOBALS['quizzin_client_includes_loaded'] = true; // Make sure that this code is not loaded more than once.
}


if(isset($_REQUEST['action']) and $_REQUEST['action']) { // Quiz Reuslts.
	$score = 0;
	$total = 0;
	
	$result = '';
	$result .= "<p>" . t('All the questions in the quiz along with their answers are shown below. Your answers are bolded. The correct answers have a green background while the incorrect ones have a red background.') . "</p>";
	
	foreach ($all_question as $ques) {
		$result .= "<div class='show-question'>";
		$result .= "<div class='show-question-content'>". stripslashes($ques->question) . "</div>\n";
		$all_answers = $wpdb->get_results("SELECT ID,answer,correct FROM wp_quiz_answer WHERE question_id={$ques->ID} ORDER BY sort_order");
		
		$correct = false;
		$result .= "<ul>";
		foreach ($all_answers as $ans) {
			$class = 'answer';
			if($ans->ID == $_REQUEST["answer-" . $ques->ID]) $class .= ' user-answer';
			if($ans->correct == 1) $class .= ' correct-answer';
			if($ans->ID == $_REQUEST["answer-" . $ques->ID] and $ans->correct == 1) {$correct = true; $score++;}
			
			$result .= "<li class='$class'>" . stripslashes($ans->answer) . "</li>\n";
		}
		$result .= "</ul>";
		if(!$_REQUEST["answer-" . $ques->ID]) $result .= "<p class='unanswered'>" . t('Question was not answered') . "</p>";
		$result .= "<p class='explanation'>" . stripslashes($ques->explanation) . "</p>";
		
		$result .= "</div>";
		$total++;
	}
	
	//Find scoring details of this guy.
	$percent = number_format($score / $total * 100, 2);
						//0-9			10-19%,	 	20-29%, 	30-39%			40-49%						
	$all_rating = array(t('Failed'), t('Failed'), t('Failed'), t('Failed'), t('Just Passed'), 
						//																			100%			More than 100%?!
					t('Satisfactory'), t('Competent'), t('Good'), t('Very Good'),t('Excellent'), t('Unbeatable'), t('Cheater'));
	$grade = intval($percent / 10);
	if($percent == 100) $grade = 9;
	if($score == $total) $grade = 10;
	$rating = $all_rating[$grade];
	
	$quiz_details = $wpdb->get_row($wpdb->prepare("SELECT name,final_screen, description FROM wp_quiz_quiz WHERE ID=%d", $quiz_id));
	
	$replace_these	= array('%%SCORE%%', '%%TOTAL%%', '%%PERCENTAGE%%', '%%GRADE%%', '%%RATING%%', '%%CORRECT_ANSWERS%%', '%%WRONG_ANSWERS%%', '%%QUIZ_NAME%%',	  '%%DESCRIPTION%%');
	$with_these		= array($score,		 $total,	  $percent,			$grade,		 $rating,		$score,					$total-$score,	   stripslashes($quiz_details->name), stripslashes($quiz_details->description));
	
	// Show the results
	
	print str_replace($replace_these, $with_these, stripslashes($quiz_details->final_screen));
	if(get_option('quizzin_show_answers')) print '<hr />' . $result;

} else { // Show The Quiz.
	$single_page = get_option('quizzin_single_page');

?>

<div class="quiz-area <?php if($single_page) echo 'single-page-quiz'; ?>">
<form action="" method="post" class="quiz-form" id="quiz-<?php echo $quiz_id?>">
<?php
$question_count = 1;

foreach ($all_question as $ques) {
	echo "<div class='question' id='question-$question_count'>";
	echo "<div class='question-content'>". stripslashes($ques->question) . "</div><br />";
	echo "<input type='hidden' name='question_id[]' value='{$ques->ID}' />";
	$dans = $wpdb->get_results("SELECT ID,answer FROM wp_quiz_answer WHERE question_id={$ques->ID} ORDER BY sort_order");
	foreach ($dans as $ans) {
		echo "<input type='radio' name='answer-{$ques->ID}' id='answer-id-{$ans->ID}' class='answer' value='{$ans->ID}' />";
		echo "<label for='answer-id-{$ans->ID}'>" . stripslashes($ans->answer) . "</label><br />";
	}
	
	echo "</div>";
	$question_count++;
}

?><br />
<input type="button" id="next-question" value="<?php e("Next") ?> &gt;"  /><br />

<input type="submit" name="action" id="action-button" value="<?php e("Show Results") ?>"  />
<input type="hidden" name="quiz_id" value="<?php echo  $quiz_id ?>" />
</form>
</div>

<?php }
}
?>