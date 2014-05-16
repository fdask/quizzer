<?php
require 'quiz.inc.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['qid']) && !empty($data['answers'])) {
	$all_good = true;

	foreach ($data['answers'] as $answer) {
		if (!isAnswerCorrect($data['qid'], $answer)) {
			$all_good = false;
		}
	}

	// send the response
	header('Content-type: application/json');

	if ($all_good) {
		echo json_encode(array('status' => 'success'));
	} else {
		echo json_encode(array('status' => 'fail'));
	}

	// save the answer
}

exit;
