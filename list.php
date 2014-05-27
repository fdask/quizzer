<?php 
if (isset($_GET['did'])) {
	require 'quiz.inc.php';

	$question_id = intval($_GET['did']);
	
	if (deleteQuestion($question_id)) {
		echo "success";
	} else {
		echo "fail";
	}

	exit;
}

include 'header.inc.php'; 
?>
<script>
$("button").click(function () {
	alert("testing");
});
</script>
<style>
table {
	border: 1px solid black;
}

table td {
	border: 1px solid black;
}
</style>
<section>
<?php
if (isset($_GET['c'])) {
	$category = getCategory($_GET['c']);
	$questions = getQuestions($_GET['c']);

	echo "<h2>#{$category['category']}</h2>";
} else {
	$questions = getQuestions();
}

if (!empty($questions)) {
	echo "<table>";
	echo "<tr><th>ID</th><th>Question</th><th>Type</th><th></th></tr>";

	foreach ($questions as $q) {
		?>
		<tr>
			<td><?php echo $q['id']; ?></td>
			<td><a href='questions.php?qid=<?php echo $q['id']; ?>'><?php echo $q['question']; ?></a></td>
			<td><?php echo $q['question_type']; ?></td>
			<td><button type='button' name='delete[]' data-qid='<?php echo $q['id']; ?>'>X</button></td>
		</tr>
		<?php
	}

	echo "</table>";
}
?>
</section>
<?php include 'footer.inc.php'; ?>
