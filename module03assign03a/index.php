<?php
session_start();
if(isset($_REQUEST['reset'])) {
  session_unset();
}


$won = false;
if(!isset($_SESSION['target'])) {
  $_SESSION['target'] = rand(1, 1000);

  // Assume that if we have no target, we have no guess counts either.
  $_SESSION['numGuesses'] = 0;
  $_SESSION['guesses'] = array();
} else {
  if(isset($_REQUEST['guess'])) {
    $_SESSION['guesses'][$_SESSION['numGuesses']] = $_REQUEST['guess'];
    $_SESSION['numGuesses']++;

    $won = $_REQUEST['guess'] == $_SESSION['target'];
  }
}
?>

<html>
  <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
  <?php if(!$won): ?>
    <h1>Pick a number, any number!</h1>
    <a href="../">Back to Index</a> <hr>
    <form action="index.php" method="post">
      <table>
        <tr>
          <td>Guess:</td>
          <td>
            <input type="number" min=1 max=1000 name="guess" value="1">
          </td>
        </tr>
        <tr>
          <td>Number of guesses: </td>
          <td>
            <?php echo $_SESSION['numGuesses']; ?>
          </td>
        </tr>
      </table>
      <input type="submit"/>
    </form>
    <form action="index.php" method="post">
      <!-- Learned how to use value to change button text -->
      <input type="submit" name="reset" value="Reset"></input>
      <input type="hidden" name="reset"></input>
    </form>
    <table id="history">
      <tr>
        <th>Guess</th>
        <th>Hint</th>
      </tr>
      <?php
        foreach ($_SESSION['guesses'] as $guess) {
          echo "<tr>";
          
          echo "<td>$guess</td> ";

          echo "<td>";
          if ($guess > $_SESSION['target']) {
            echo "Too high.";
          } elseif ($guess < $_SESSION['target']) {
            echo "Too low.";
          } else {
            echo "Juuuuust right.";
          }
          echo "</td>";
          
          echo "</tr>";
        }
      ?>
    </table>
    <!-- Learned about alternative control structure syntax -->
    <?php else: ?>
    <h1>You won!</h1>
    <a href="../">Back to Index</a> <hr>
    Refresh the page to try again!
    <?php
      session_unset();
      // Per https://stackoverflow.com/questions/6472123/why-is-php-session-destroy-not-working
      // session_destroy doesn't actually destroy the session for some reason.
      $_SESSION = [];
    ?>
    <?php endif; ?>
    <hr>
    <?php highlight_file("index.php"); ?>
  </body>
</html>
