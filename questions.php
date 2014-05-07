<?php
require 'header.inc.php'; 

if (isset($_POST['submit'])) {
	if (isset($_POST['q_id'])) {
		$question_id = intval($_POST['q_id']);

		// edit instead of new
		if (updateQuestion($question_id, $_POST['question'], $_POST['question_type'], $_POST['question_timelimit'])) {
			// nuke all the previous answers
			removeAnswers($question_id);

			// save current answers
			saveAnswers($question_id, $_POST['question_type']);

			// nuke all the previous categories
			removeCategories($question_id);

			// save current categories
			if (isset($_POST['tag'])) {
				saveCategories($question_id, "tag");
			}
		}
	} else {
		$question_id = saveQuestion($_POST['question'], $_POST['question_type'], $_POST['question_timelimit']);

		if ($question_id) {
			$count = saveAnswers($question_id, $_POST['question_type']);

			echo "Saved a total of $count answers<br/>";

			// store the categories
			if (isset($_POST['tag'])) {
				saveCategories($question_id, "tag");
			}
		}
	}
} else if (isset($_GET['qid'])) {
	// load the question
	$q = getQuestion($_GET['qid']);

	echo "<pre>";
	print_r($q);
	echo "</pre>";
}
?>
		<section>
			<form name='quiz' id='quiz' method='POST' action='<?php echo $_SERVER['SCRIPT_NAME']; ?>'>
				<label for='question'>Question</label>
				<textarea name='question' id='question' /><?php if (isset($q['question'])) { echo $q['question']; } ?></textarea><br/>

				<?php
				if (isset($q) && !empty($q)) {
					foreach (array('mc', 'tf', 'fb') as $type) {
						$qts[$type] = ($q['question_type'] == $type) ? "checked='checked'" : "";	
					}
				}
				?>
				<p>Question Type</p>
				<ul>
					<li>
						<input type='radio' name='question_type' id='question_type_mc' value='mc' <?php if (!empty($q)) { echo $qts['mc']; } else { echo "checked='checked'"; } ?> />
						<label for='question_type_mc'>Multiple Choice</label>
					</li>
					<li>
						<input type='radio' name='question_type' id='question_type_tf' value='tf' <?php if (!empty($q)) { echo $qts['tf']; } ?> />
						<label for='question_type_tf'>True or False</label>
					</li>
					<li>
						<input type='radio' name='question_type' id='question_type_fb' value='fb' <?php if (!empty($q)) { echo $qts['fb']; } ?> />
						<label for='question_type_fb'>Fill in the Blanks</label>
					</li>
				</ul>
	
				<label for='question_cats'>Question Categories</label>
				<div id='question_cats'><?php
					if (isset($q['categories'])) {
						$cats = array();

						foreach ($q['categories'] as $category) {
							$cats[] = $category['category'];
						}

						echo implode(", ", $cats);
					}
				?></div>

				<p>Answers</p>
				<!-- this answer block only applies to a true false question -->
				<span id='answer_tf' <?php if ((isset($q) && $q['question_type'] != "tf") || !isset($q)) { echo "style='display: none;'"; } ?>>
					<?php
					if (!empty($q)) {
						$correct = "";

						foreach ($q['answers'] as $answer) {
							if ($answer['correct']) {
								$correct = $answer['answer'];
							}						
						}
					}
					?>
					<label for='answer_true'>True</label>
					<input type='radio' name='answer_tf' id='answer_true' value='true' <?php if ($correct == "True" || !isset($q)) { echo "checked='checked'"; } ?> /><br/>
					<label for='answer_false'>False</label>
					<input type='radio' name='answer_tf' id='answer_false' value='false' <?php if ($correct == "False") { echo "checked='checked'"; } ?>/><br/>
				</span>

				<span id='answer_mc' <?php if (isset($q) && $q['question_type'] != "mc") { echo "style='display: none;"; } ?>>
					<button type='button' name='add_answer' id='add_answer'>Add Answer</button><br/>

					<?php
					if (!empty($q)) {
						foreach ($q['answers'] as $answer) {
							?>
					<fieldset class='answer_mc'>
						<label for='answer'>Answer</label>
						<input type='text' name='answer[]' value="<?php echo $answer['answer']; ?>" /><br/>

						<label for='answer_correct'>Correct Answer?</label>
						<input type='checkbox' name='answer_correct[]' <?php if ($answer['correct']) echo "checked='checked'"; ?> /> Correct<br/>
				
						<label for='answer_explanation'>Explanation</label>
						<input type='text' name='answer_explanation[]' value='<?php echo str_replace('"', '\"', $answer['explanation']); ?>' /><br/>

						<button type='button' name='delete[]'>X</button>
					</fieldset>
							<?php
						}
					} else {
						?>
					<fieldset class='answer_mc'>
						<label for='answer'>Answer</label>
						<input type='text' name='answer[]' /><br/>

						<label for='answer_correct'>Correct Answer?</label>
						<input type='checkbox' name='answer_correct[]' /> Correct<br/>
				
						<label for='answer_explanation'>Explanation</label>
						<input type='text' name='answer_explanation[]' /><br/>

						<button type='button' name='delete[]'>X</button>
					</fieldset>
						<?php
					}
					?>
				</span>

				<span id='answer_fb' <?php if ((isset($q) && $q['question_type'] != "fb") || !isset($q)) { echo "style='display: none;'"; } ?>>
					<?php 
					if (!empty($q)) {
						if ($q['question_type'] == "fb") {
							foreach ($q['answers'] as $answer) {
								echo "<input type='text' name='answer_fb[]' value='" . str_replace("'", "\'", $answer['answer']) . "' />";
							}
						}

						echo "<br/>";
					}
					?>
				</span>
				<br />

				<label for='question_timelimit'>Time Limit (seconds)</label>
				<input type='text' name='question_timelimit' id='question_timelimit' <?php if (isset($q)) echo "value='" . $q['time_limit'] . "'"; ?> /><br/>
				<input type='hidden' name='correct_answer' id='correct_answer' value='' />
				
				<?php 
				if (!empty($q)) {
					?>
					<input type='hidden' name='q_id' value='<?php echo $q['id']; ?>' />
					<button type='submit' name='submit' id='submitButton'>Update Question</button>
					<?php
				} else {
					?>
					<button type='submit' name='submit' id='submitButton'>Save Question</button>
					<?php
				}
				?>
			</form>
		</section>
<?php require 'footer.inc.php'; ?>
