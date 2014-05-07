<?php include 'header.inc.php'; ?>
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
$questions = getQuestions();

if (!empty($questions)) {
	echo "<table>";
	echo "<tr><th>ID</th><th>Question</th><th>Type</th><th></th></tr>";

	foreach ($questions as $q) {
		?>
		<tr>
			<td><?php echo $q['id']; ?></td>
			<td><a href='questions.php?qid=<?php echo $q['id']; ?>'><?php echo $q['question']; ?></a></td>
			<td><?php echo $q['question_type']; ?></td>
			<td><button type='button' name='delete[]'>X</button></td>
		</tr>
		<?php
	}

	echo "</table>";
}

echo "<pre>";
print_r($questions);
echo "</pre>";
?>
</section>
<?php include 'footer.inc.php'; ?>
