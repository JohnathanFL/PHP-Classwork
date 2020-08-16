<?php


$weIn=false;
#print_r($_REQUEST);
extract($_REQUEST);

# Takes the id (name=) an an associative map of value -> what should be displayed
# def is the default selected (based on the key, not the value)
function select_of($id, $values, $def) {
  echo "<select name='$id'>\n";
  foreach($values as $val => $desc) {
    if ($def == $val) {
      echo "<option value='$val' selected='selected'>$desc</option>\n";
    } else {
      echo "<option value='$val'>$desc</option>\n";
    }
  }
  echo "</select>\n";
}

function date_picker($which) {
  $curYear = date("Y"); # Thank god for auto string -> int conversions in PHP
  # Or the devil, I suppose that's more appropriate with PHP.
  $curMon = date("n");
  $curDay = date("j");
  $curHour = date("G");
  $curMin = date("i");

  $tmp = array();
  for($i = $curYear - 5; $i <= $curYear + 5; $i++)
    $tmp[$i] = $i;
  echo "Year: ";
  select_of($which . "_year", $tmp, $curYear);
  
  $months = array(
    1 => "Jan",
    2 => "Feb",
    3 => "Mar",
    4 => "Apr",
    5 => "May",
    6 => "Jun",
    7 => "Jul",
    8 => "Aug",
    9 => "Sep",
    10 => "Oct",
    11 => "Nov",
    12 => "Dec" 
  );
  echo "Month: ";
  select_of($which . "_month", $months, $curMon);

  # Learned that PHP's real prickly about int-like strings
  $tmp = array();
  for($i = 1; $i <= 31; $i++)
    $tmp[$i] = $i;
  echo "Day: ";
  select_of($which . "_day", $tmp, $curDay);


  $tmp = array();
  for($i = 0; $i <= 23; $i++)
    $tmp[$i] = $i;
  echo "Hour: ";
  select_of($which . "_hour", $tmp, $curHour);
  
  $tmp = array();
  for($i = 0; $i <= 59; $i++)
    $tmp[$i] = $i;
  echo "Minute: ";
  select_of($which . "_minute", $tmp, $curMin);
}

// For testing from command line for getting errors
/*$weIn = true;
$t1_year = 2020;
$t1_month = 1;
$t1_day = 1;
$t1_hour = 10;
$t1_minute = 0;

$t2_year = 2020;
$t2_month = 1;
$t2_day = 10;
$t2_hour = 10;
$t2_minute = 0;
*/

# Check if the date is valid
$dateValid = true;
$dateFrom = new DateTime();
$dateTo = new DateTime();
if ($weIn) {
  if(!checkdate($t1_month, $t1_day, $t1_year)) $dateValid = false;
  if(!checkdate($t2_month, $t2_day, $t2_year)) $dateValid = false;

  $curYear = date("Y");
  if ($t1_year < $curYear - 5 || $t1_year > $curYear + 5) $dateValid = false;
  if ($t2_year < $curYear - 5 || $t2_year > $curYear + 5) $dateValid = false;
  
  # Formatting them from YYYY-m-d H:M:S format

  # Weird that these setDate functions actually wrap days around months
  # Stupid, even
  if ($dateFrom->setDate($t1_year, $t1_month, $t1_day) == false) $dateValid = false;
  if ($dateFrom->setTime($t1_hour, $t1_minute) == false) $dateValid == false;

  if ($dateTo->setDate($t2_year, $t2_month, $t2_day) == false) $dateValid = false;
  if ($dateTo->setTime($t2_hour, $t2_minute) == false) $dateValid == false;
}

# Thus we only good if we're in and the dates were valid
$weGood = $dateValid;

function clamp($val, $max) {
  if($val > $max) {
    return $max;
  } else {
    return $val;
  }
}

# We're doing this one iteratively based on the 30 min increments. Performance? What's that?
# I'm certain there's an easier way, but I can't figure it out at the moment, and this
# lets me roll all 3 into one function.
function calc_rate($maxDay, $maxWeek) {
  global $dateFrom, $dateTo;
  $diff = $dateFrom->diff($dateTo);
  # The 0 as an arg was for sugar. I doubt anyone will ever hit PHP_INT_MAX per week
  # Although some airports would like to try...
  if($maxWeek == 0) $maxWeek = PHP_INT_MAX;

  $fmt = "Y-m-d@H:i";
  $cur = clone $dateFrom;

  $plus30 = new DateInterval("PT30M");

  $total = 0; # Total fare
  $curDay = 0; # How much fare for current day
  $curWeek = 0; # How much fare for current week

  # While we aren't past the target and aren't on it
  while($cur->diff($dateTo)->invert != 1 && $cur->format($fmt) != $dateTo->format($fmt)) { 
    $day = $cur->format("j");
    $weekDay = $cur->format("N");
    
    $cur->add($plus30);
    $curDay += 1;
    
    # If we just moved a day, add the current daily amount to the weekly amount
    if($day != $cur->format("j")) {
      $curWeek += clamp($curDay, $maxDay);
      $curDay = 0;
    }

    # If we just moved to the next week, add our current weekly amount to the total
    if ($weekDay > $cur->format("N")) {
      $total += clamp($curWeek, $maxWeek);
      $curWeek = 0;
    }
    
  }

  # Roll the leftovers in
  $curWeek += clamp($curDay, $maxDay);
  $total += clamp($curWeek, $maxWeek);

  echo "<h1>For " . $dateFrom->format($fmt) . " - " . $dateTo->format($fmt) . "<br>\n";
  echo "You owe $" . $total . " for your stay</h1>\n";
}

function echo_results() {
  global $weIn, $weGood, $dateFrom, $dateTo, $rate;
  if($weIn && $weGood) {
    if($rate == "short") {
      calc_rate(18, 0);
    } elseif ($rate == "long") {
      calc_rate(8, 48);
    } elseif ($rate == "economy") {
      calc_rate(6, 36);
    }
  } elseif ($weIn){
    echo "<h1>Invalid date(s) entered. Unable to calculate.</h1>";
  }
}

?>

<html>
  <head>
    <meta charset="UTF-8"/>
    <link rel="stylesheet" href="style.css"/>
  </head>
  <body>
    <?php echo_results(); ?>
		<a href="../index.html">Back to Index</a>
  
    <form action="index.php" method="POST">
      Start time <br>
      <?php date_picker("t1"); ?>
      <br>
      <br>
      End Time <br>
      <?php date_picker("t2"); ?>
      <br>

      <br>
      Parking rate: <br>
      <!-- Learned that the "for" in a label refers to the id attribute, although the id attribute
      isn't required for that radio button to function. -->
      <input type="radio" name="rate" id="short-rate" value="short" required=true>
      <label for="short-rate">Short Term Parking</label><br>
      <input type="radio" name="rate" id="long-rate" value="long">
      <label for="long-rate">Long Term Parking</label><br>
      <input type="radio" name="rate" id="economy-rate" value="economy">
      <label for="economy-rate">Economy Parking</label><br>
      <!-- Instead of finding a variable to check for isset, why not just do this? -->
      <input type="hidden" name="weIn" value="true"> 
      <input type="submit">
      
    </form>
    <hr>
    Note to the professor: I assume that "each 30 minutes" includes fractional 30 mins as rounded up. (i.e you pay for each 30 minute block since, with the first block starting exactly on the start time)

    <hr>
    <?php
      # Changing highlighting stuff so you can read the PHP file when it's printed
      # Learned about mucking with ini settings
      ini_set("highlight.default", '"class="hDef');
      ini_set("highlight.keyword", '"class="hKey');
      ini_set("highlight.string", '"class="hStr');
      ini_set("highlight.html", '"class="hHtml');
      ini_set("highlight.comment", '"class="hComm');
      highlight_file("index.php");
    ?>
  </body>
</html>
