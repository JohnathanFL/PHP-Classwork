<html>
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Result</h1>
You managed to get a
<?php

  $dist = $_REQUEST["distance"];
  $fuel = $_REQUEST["fuel"];
  
  $kpl = $dist/$fuel;

  if ($kpl < 10.0) {
    $adjective = "terrible";
  } elseif ($kpl < 20.0) {
    $adjective = "normal";
  } elseif ($kpl < 30.0) {
    $adjective = "nice";
  } else {
    $adjective = "sweet";
  }
  
  echo "$adjective $kpl km/L.";
  
?>
</body>
</html>
