<?php
require 'header.inc.php';

// get a random question
if (isset($_GET['qid'])) {
	$q = getQuestion($_GET['qid']);
} else if ($_GET['c']) {
	$q = getQuestionByCategory($_GET['c']);
}

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
	case 'fb':
		// fill in the blanks
		for ($x = 0; $x < count($q['answers']); $x++) {
			?>
			<input type='text' name='fb_answer[]' value='' />
			<?php
		}

		break;
	case 'mc':
	case 'tf':
		// multiple choice
		$cc = 0;
		foreach ($q['answers'] as $answer) {
			if ($answer['correct']) {
				$cc++;
			}
		}
	
		if ($q['question_type'] == "mc") {
			shuffle($q['answers']);
		}
				
		$first = true;

		foreach ($q['answers'] as $answer) {
			if ($cc > 1) { 
				// checkbox - multiple choice with more than one correct answer
				?>	
			<input type='checkbox' name='mc_answer[]' id='mc_answer_<?php echo $answer['id']; ?>' data-qid='<?php echo $answer['id']; ?>' value='<?php echo htmlentities($answer['answer']); ?>' />
			<label for='mc_answer_<?php echo $answer['id']; ?>'><?php echo htmlentities($answer['answer']); ?></label><br/>
			<?php } else { 
				// radio - only one right answer 
			?>
			<input type='radio' name='mc_answer[]' id='mc_answer_<?php echo $answer['id']; ?>' value='<?php echo $answer['id'] . "'"; if ($first) echo "checked='checked'"; ?>>
			<label for='mc_answer_<?php echo $answer['id']; ?>'><?php echo htmlentities($answer['answer']); ?></label><br/>
			<?php 
			}

			$first = false;
		}
		break;
	case 'cm':
		// column match
		$left = array();
		$right = array();

		foreach ($q['answers'] as $answer) {
			$left[] = "<li data-txt='" . str_replace("'", "\'", $answer['answer']) . "'>" . htmlentities($answer['answer']) . "</li>";
			$right[] = "<li data-txt='" . str_replace("'", "\'", $answer['explanation']) . "'>" . htmlentities($answer['explanation']) . "</li>";
		}

		shuffle($left);
		shuffle($right);

		echo "<ul id='column-left'>";

		for ($x = 0; $x < count($left); $x++) {
			echo $left[$x];
		}

		echo "</ul>";
		echo "<ul id='column-right'>";

		for ($x = 0; $x < count($right); $x++) {
			echo $right[$x];
		}

		echo "</ul>";

		break;
	default:
}
?>
<!--
<ul id='explanation'><?php
	foreach ($q['answers'] as $answer) {
		echo "<li>" . $answer['explanation'] . "</li>";
	}
?></ul>-->
<input type='hidden' name='question_id' value='<?php if (isset($q)) echo $q['id']; ?>' />
<button type='button' name='answerq' id='answerq' value=''>GO</button>

</form>

<script>
$("#answerq").click(function () {
	var answers = getAnswers();

	sendAnswer(answers);
});

function getAnswers() {
	var answers = [];

	// get the inputs to see if we have fill in the blanks or multiple choice
	var els = $("input[name^=mc_answer]");

	if (els.length) {
		els.each(function (i, el) {
			if ($(this).prop('checked')) {
				answers.push($(this).val());
			}
		});
	} else if ($("#column-left").length) {
		var left = $("#column-left").sortable('toArray', {attribute: 'data-txt'});
		var right = $("#column-right").sortable('toArray', {attribute: 'data-txt'});

		for (var x = 0; x < left.length; x++) {
			answers.push([left[x], right[x]]);
		}
	} else {
		$("input[name^=fb_answer]").each(function (i, el) {
			answers.push($(this).val());
		});
	}

	return answers;
}

function sendAnswer(answers) {
	var sendme = {
		qid: <?php echo $q['id']; ?>,
		answers: answers,
	};

	$.ajax({
		url: "ajax.php",
		type: "POST",
		data: JSON.stringify(sendme),
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		async: true,
		success: function (data) {
			if (data.status == "success") {
				$("body").css('background-color', 'green');
			} else {
				$("body").css('background-color', 'red');
			}

			setTimeout(function() {
				window.location.href = "q.php" + window.location.search;
			}, 2000);
		},
		error: function(httpRequest, textStatus, errorThrown) { 
			console.log("status=" + textStatus + ",error=" + errorThrown);
		}
	});
}
</script>
</body>
</html>
