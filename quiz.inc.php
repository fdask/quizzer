<?php
error_reporting(E_ALL);
ini_set("display_errors", "on");

function removeCategories($question_id) {
	$query = "
	DELETE FROM questioncategories
	WHERE
		question_id = $question_id";

	return mysql_query($query);
}

function removeAnswers($question_id) {
	$query = "
	DELETE FROM answers
	WHERE
		question_id = $question_id";

	return mysql_query($query);
}

function saveAnswer($question_id, $answer, $explanation, $correct, $position = null) {
	$cor = $correct ? 1 : 0;

	$query = "
	INSERT INTO answers (
		question_id,
		answer,
		explanation,
		correct,
		position
	) VALUES (
		$question_id,
		'" . mysql_real_escape_string($answer) . "',
		'" . mysql_real_escape_string($explanation) . "',
		$cor,
		" . ((!$position) ? 'NULL' : $position) . "
	)";		

	echo $query . "<br/>";
	return mysql_query($query);
}

function getQuestion($question_id = false) {
	$ret = array();

	if (!$question_id) {
		$query = "
		SELECT
			id,
			question,
			question_type,
			time_limit
		FROM
			questions
		ORDER BY RAND()
		LIMIT 1";
	} else {
		$query = "
		SELECT
			id,
			question,
			question_type,
			time_limit
		FROM
			questions
		WHERE
			id = $question_id";
	}

	$res = mysql_query($query) or die($query . "<br/>" . mysql_error());

	if (mysql_num_rows($res) === 1) {
		// we've got the question
		$row = mysql_fetch_assoc($res);

		$question_id = $row['id'];

		// now get the answers
		$answers = array();
		$answers['answers'] = getAnswers($question_id);

		$categories = array();
		$categories['categories'] = getCategories($question_id);

		$ret = array_merge($row, $answers, $categories);
	}

	return $ret;
}

function getQuestions() {
	$query = "
	SELECT
		id,
		question,
		question_type,
		time_limit
	FROM
		questions";

	$res = mysql_query($query);

	if ($res) {
		$ret = array();

		while ($row = mysql_fetch_assoc($res)) {
			$ret[] = $row;
		}

		return $ret;
	}

	return false;
}

function saveQuestion($question, $type, $limit) {
	$query = "
	INSERT INTO questions (
		question,
		question_type,
		time_limit
	) VALUES (
		'" . mysql_real_escape_string($question) . "',
		'" . mysql_real_escape_string($type) . "',
		" . (intval($limit) ? intval($limit) : 0) . "
	)";

	$res = mysql_query($query) or die($query . "\n" . mysql_error());

	if ($res) {
		return mysql_insert_id();
	}

	return false;
}

function updateQuestion($question_id, $question, $type, $limit) {
	$query = "
	UPDATE questions SET
		question = '" . mysql_real_escape_string($question) . "',
		question_type = '" . mysql_real_escape_string($type) . "',
		time_limit = " . (intval($limit) ? intval($limit) : 0) . " 
	WHERE
		id = $question_id";

	return mysql_query($query);
}

function deleteQuestion($question_id) {
	if (removeCategories($question_id) && removeAnswers($question_id)) {
		$query = "DELETE FROM questions WHERE id = $question_id";

		return mysql_query($query);
	}

	return false;
}

function getAnswers($question_id, $correct = false) {
	$ret = array();

	$query = "
	SELECT
		id,
		question_id,
		answer,
		explanation,
		correct,
		position
	FROM
		answers
	WHERE
		question_id = $question_id";

	if ($correct) {
		$query .= " AND correct = 1";
	}

	$res = mysql_query($query) or die($query . "<br/>" . mysql_error());

	if (mysql_num_rows($res) > 0) {
		while ($row = mysql_fetch_assoc($res)) {
			if ($correct) {
				$ret[] = $row['answer'];
			} else {
				$ret[] = $row;
			}
		}
	}

	return $ret;
}

function saveAnswers($question_id, $question_type) {
	$ret = 0;

	if ($question_type == "mc") {
		for ($x = 0; $x < count($_POST['answer']); $x++) {
			$correct = explode(",", $_POST['correct_answer']);

			// insert the answers
			if (saveAnswer($question_id, $_POST['answer'][$x], $_POST['answer_explanation'][$x], (in_array($x, $correct)))) {
				$ret++;
			}
		}
	} else if ($question_type == "tf") {
		if ($_POST['answer_tf'] == "true") {
			if (saveAnswer($question_id, 'True', $_POST['answer_tf_ex'], 1)) {
				$ret++;
			}

			if (saveAnswer($question_id, 'False', '', 0)) {
				$ret++;
			}
		} else {
			if (saveAnswer($question_id, 'True', '', 0)) {
				$ret++;
			}

			if (saveAnswer($question_id, 'False', $_POST['answer_tf_ex'], 1)) {
				$ret++;
			}
		}
	} else if ($question_type == "fb") {
		for ($x = 0; $x < count($_POST['answer_fb']); $x++) {
			if (saveAnswer($question_id, $_POST['answer_fb'][$x], '', 1, $x)) {
				$ret++;
			}
		}
	} else if ($question_type == "cm") {
		foreach ($_POST['answer_cm'] as $row) {
			if (saveAnswer($question_id, $row[0], $row[1], 1)) {
				$ret++;
			}
		}
	}

	return $ret;
}

function saveCategories($question_id, $tag) {
	$cats = array();

	for ($c = 0; $c < count($_POST[$tag]); $c++) {
		// check to see if the category already exists
		$cres = mysql_query("
		SELECT
			id,
			category
		FROM
			categories
		WHERE
			category = '" . mysql_real_escape_string($_POST[$tag][$c]) . "'");

		if (mysql_num_rows($cres) > 0) {
			while ($row = mysql_fetch_assoc($cres)) {
				$cats[] = $row['id'];
			}

		} else {
			$cres = mysql_query("
			INSERT INTO categories (
				category
			) VALUES (
				'" . mysql_real_escape_string($_POST[$tag][$c]) . "'
			)");

			$cats[] = mysql_insert_id();
		}
	}

	foreach ($cats as $catid) {
		mysql_query("INSERT INTO questioncategories (
			question_id,
			category_id
		) VALUES (
			$question_id,
			$catid
		)");
	}
}	

function getCategories($question_id = false) {
	$ret = array();

	if (!$question_id) {
		$query = "
		SELECT 
			COUNT(*) AS count, 
			category_id, 
			c.category 
		FROM 
			questioncategories 
		INNER JOIN categories c ON (questioncategories.category_id = c.id) 
		GROUP BY category_id
		ORDER BY count DESC";
	} else {
		$query = "
		SELECT
			c.id,
			c.category
		FROM
			categories c
		INNER JOIN questioncategories qc ON (qc.category_id = c.id)
		WHERE
			qc.question_id = $question_id";
	}

	$res = mysql_query($query) or die($query . "<br/>" . mysql_error());

	if (mysql_num_rows($res) > 0) {
		while ($row = mysql_fetch_assoc($res)) {
			$ret[] = $row;
		}
	}

	return $ret;
}

function isAnswerCorrect($question_id, $answer) {
	$ret = false;

	$query = "
	SELECT
		correct
	FROM
		answers
	WHERE
		question_id = $question_id";

	if (ctype_alpha($answer)) {
		$query .= " AND answer = '" . mysql_real_escape_string($answer) . "'";
	} else {
		$query .= " AND id = $answer";
	}

	$res = mysql_query($query);

	if ($res && mysql_num_rows($res)) {
		$row = mysql_fetch_assoc($res);

		return $row['correct'] ? true : false;
	}

	return false;
}

mysql_connect("localhost", "quiz", "quiz");
mysql_select_db("quiz");
