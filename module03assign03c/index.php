<?php
  function serializer($arrays) {
    //echo "Got serializer with: <br>";
    //print_r($arrays);
    echo '<input type="hidden" name="submitting"/>';
    foreach ($arrays as $name => $arr) {
      //echo gettype($arr);
      //echo "<br>name was $name<br>";
      foreach ($arr as $i => $el) {
        // Learned you gotta be reaaaal careful about string interpolation
        // Example: Some idiot might try something like `name='$name[$i]'`, not realizing that
        // it will interpret that as an array access.
        // An idiot like that would probably get really frustrated with PHP and not find his
        // mistake for a long time.
        // I'm oooooobviously not that kind of idiot.
        // Definitely not.
        echo "<input type='hidden' name='$name" . "[$i]' value='$el'/>";
      }
    }
  }
?>

<html>
<head>
  <meta charset="UTF-8"/>
  <link rel="stylesheet" href="./style.css"/>
</head>
<body>
  <?php
    $miles = array();
    $gallons = array();
    extract($_REQUEST);
    if($op == "Add") {
      array_push($miles, $newMiles);
      array_push($gallons, $newGallons);
    }
    elseif ($op == "Delete") {
      unset($miles[$delete]);
      unset($gallons[$delete]);
    }
  ?>

  <?php
    $sum = 0.0;
    foreach($miles as $i => $el) {
      $sum += $el / $gallons[$i];
    }
    $sum /= count($miles);
    if(!is_nan($sum))
      echo "<h1>Your average MPG: $sum</h1>";
    else
      echo "<h1>Log a trip to get started!</h1>";
  ?>

  <hr>
  <!-- Form to enter new data -->
  <form class="boxed" action="index.php" method="POST">
    <h4>Add a Trip</h4>
    <?php
      serializer(array("miles" => $miles, "gallons" => $gallons));
    ?>
    Miles: <input type="number" min="1" name="newMiles"/><br>
    Gallons: <input type="number" min="1" name="newGallons"/>
    <input type="submit" name="op" value="Add"/>
    
  </form>

  <!-- Form to delete existing data -->
  <form class="boxed" action="index.php" method="POST">
    <h4>Delete a Trip</h4>
    <?php
      serializer(array("miles" => $miles, "gallons" => $gallons));
    ?>
    <select name="delete">
      <?php
        foreach($miles as $i => $mile) {
          $gallon = $gallons[$i]; // Need to keep the arrays parallel
          echo "<option value='$i'>Trip $i ($mile miles, $gallon gallons)</option>";
        }
      ?>
    </select>
    <input type="submit" name="op" value="Delete"/>
  </form>
</body>
</html>
