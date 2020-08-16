<body>
<?php
echo "<h1>$resultText</h1>";
?>
<h2><a href="./index.php">Click here or reload to play another game.</a></h2>

<h1>Match History</h1>
<?php
// pass.php was already included by our parent file.
$db = mysqli_connect("localhost", "leejo", $password, "leejo_War");
$res = $db->query("SELECT whenPlayed, who, p1Name, p2Name from History;");
?>

<table>
<tr>
  <th>Date</th>
  <th>Result</th>
</tr>
<?php
while($field = $res->fetch_array()) {
  echo "<tr>";
  echo "<td>" . $field[0] . "</td>";

  echo "<td>";
  switch($field[1]) {
    case 0: echo  $field[2] . " and " . $field[3] . " came to a draw "; break;
    case 1: echo  $field[2] . " beat " . $field[3]; break;
    case -1: echo $field[3] . " beat " . $field[2]; break;
  }
  echo "</td>";
  
  echo "</tr>";
}
?>
</table>

</body>
