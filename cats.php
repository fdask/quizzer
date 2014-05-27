<?php 
require 'header.inc.php'; 
?>
<style>
table, td {
	border: 1px solid black;
}
</style>
<table>
	<tr>
		<th>Category</th>
		<th>Count</th>
		<th>List</th>
		<th>Quiz</th>
	</tr>
<?php
$cats = getCategories();

foreach ($cats as $cat) {
	echo "<tr>";
	echo "<td>{$cat['category']}</td>";
	echo "<td>{$cat['count']}</td>";
	echo "<td><a href='list.php?c={$cat['id']}'>List</a></td>";
	echo "<td><a href='q.php?c={$cat['id']}'>Quiz</a></td>";
	echo "</tr>";
}

echo "</table>";
?>
