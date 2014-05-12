<?php
require 'header.inc.php';

if (isset($_GET['qid'])) {
	$answer = urldecode($_GET['c']);

	$answers = explode("||", $answer);

	$a = getAnswers($_GET['qid'], true);

	$diff = array_diff($a, $answers);	

	if (empty($diff)) {
		// correct
		echo "<div id='answer'>correct</div>";
	} else {
		echo "<div id='answer'>incorrect</div>";
	}

	exit;
}

// get a random question
$q = getQuestion();

echo "<p>";

foreach ($q['categories'] as $cat) {
	echo "#{$cat['category']} ";
}

echo "</p>";
?>
<h1><?php echo $q['question']; ?></h1>
<form name='question_form' id='question_form' method='POST' action='<?php echo $_SERVER['SCRIPT_NAME']; ?>'>
<?php
switch ($q['question_type']) {
	case 'tf':
		?>
		<button type='button' name='true' id='true'>True</button>
		<button type='button' name='false' id='false'>False</button>
		<?php
		break;
	case 'fb':
		for ($x = 0; $x < count($q['answers']); $x++) {
			?>
			<input type='text' name='fb_answer[]' value='' />
			<?php
		}

		break;
	case 'mc':
		$cc = 0;
		foreach ($q['answers'] as $answer) {
			if ($answer['correct']) {
				$cc++;
			}
		}
	
		shuffle($q['answers']);
				
		$first = true;

		foreach ($q['answers'] as $answer) {
			if ($cc > 1) { ?>
			<input type='checkbox' name='mc_answer[]' id='mc_answer_<?php echo $answer['id']; ?>' value='<?php echo htmlentities($answer['answer']); ?>' />
			<label for='mc_answer_<?php echo $answer['id']; ?>'><?php echo $answer['answer']; ?></label><br/>
			<?php } else { ?>
			<input type='radio' name='mc_answer[]' id='mc_answer_<?php echo $answer['id']; ?>' value='<?php echo htmlentities($answer['answer']) . "'"; if ($first) echo "checked='checked'"; ?>>
			<label for='mc_answer_<?php echo $answer['id']; ?>'><?php echo $answer['answer']; ?></label><br/>
			<?php 
			}

			$first = false;
		}
		break;
	default:
}
?>
<script>
$("#true, #false").click(function () {
	sendAnswer($(this).html());
});

$(document).delegate('*', 'keypress', function(e) {
	if (e.which === 13) { // if is enter
		e.preventDefault(); // don't submit form

		var answers = [];

		// get the inputs to see if we have fill in the blanks or multiple choice
		var els = $("input[name^=mc_answer]");

		if (els.length) {
			els.each(function (i, el) {
				if ($(this).prop('checked')) {
					answers.push($(this).val());
				}
			});
		} else {
			$("input[name^=fb_answer]").each(function (i, el) {
				answers.push($(this).val());
			});
		}

		sendAnswer(answers.join("||"));
	}
});

function sendAnswer(correct) {
	var question_id = <?php echo $q['id']; ?>;
	
	$.ajax({
		url: "?qid=" + question_id + "&c=" + encodeURIComponent(correct)
	}).done(function (data) {
		alert($("#answer", $(data)).html());
		if ($("#answer", $(data)).html() == "correct") {
			$("body").css('background-color', 'green');
		} else {
			$("body").css('background-color', 'red');
		}

		setTimeout(function() {
			window.location.href = "q.php";
		}, 2000);
	});
}
</script>
<!--
<ul id='explanation'><?php
	foreach ($q['answers'] as $answer) {
		echo "<li>" . $answer['explanation'] . "</li>";
	}
?></ul>-->
<input type='hidden' name='question_id' value='<?php echo $question['id']; ?>' />
<!--<button type='submit' name='submit' value=''>GO</button> -->
</form>
