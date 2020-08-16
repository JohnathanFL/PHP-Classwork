<?php
// The rules are a simple extension of the normal War cardgame,
// where each player takes turns drawing from an unknown deck of
// cards to see who gets the higher card the most number of times.
// "Strategy war" is just the extension I came up with when I was younger
// to take some of the randomness out.


// Basic flow of my code:
  // Extract the session
  // Perform logic: (Not neccesarily in order)
    // If we have no session stuff set, we just default to names.php
    // If there is session stuff set, that will auto-set fileToShow
    // If we just received a name submission, we go to the main game
    // If we just received an endTurn, score the round and update state
      // If players are out of cards,  score the game, hit MySQL, unset session, and show win.php
  // Display the page (based on $fileToShow from the logic)
session_start();
//print_r($_REQUEST);

// Default to names.php for entering player name
$fileToShow = "names.php";
// This will override the above if we're in a game
extract($_SESSION);
include("../pass.php");
$db = mysqli_connect("localhost", "leejo", $password, "leejo_War");
$adder = $db->prepare("INSERT INTO History (whenPlayed, who, p1Name, p2Name) VALUES (CAST(? AS DATE), ?, ?, ?)");

function redistribute_decks() {
  global $p1Deck, $p2Deck;
  $deck = array(
    "C1", "C2", "C3", "C4", "C5", "C6", "C7", "C8", "C9", "C10", "C11", "C12", "C13",
    "D1", "D2", "D3", "D4", "D5", "D6", "D7", "D8", "D9", "D10", "D11", "D12", "D13", 
    "H1", "H2", "H3", "H4", "H5", "H6", "H7", "H8", "H9", "H10", "H11", "H12", "H13", 
    "S1", "S2", "S3", "S4", "S5", "S6", "S7", "S8", "S9", "S10", "S11", "S12", "S13", 
  );

  // First randomize, then deal
  // I don't know why I took the time to sort that array by hand
  // when I always shuffle it immediately

  // Well ain't that an aptly named function
  // PHP warns it ain't cryptographically secure, so I guess
  // don't make a major betting website over top this?
  shuffle($deck);

  // As long as there're cards to deal, deal em'
  while(count($deck) > 0) {
    array_push($p1Deck, array_pop($deck));
    array_push($p2Deck, array_pop($deck));
  }
}

// Returns 1, 0, or -1 for left wins, draw, or right wins
function compare_cards($lhs, $rhs) {
  // Ignore the suit
  $lhs = substr($lhs, 1);
  $rhs = substr($rhs, 1);

  // Elevate the Aces
  if($lhs == 1) $lhs = 1000;
  if($rhs == 1) $rhs = 1000;

  // Spaceships!
  // Pew pew pew
  // and you said you'd never found a use for them...
  return $lhs <=> $rhs;
}

// We just got off the login page
if(isset($_REQUEST['login'])) {
  $fileToShow = "play.php";
  $p1Name = $_REQUEST['p1Name'];
  $p2Name = $_REQUEST['p2Name'];
  $p1Deck = array();
  $p2Deck = array();
  redistribute_decks();
  $p1Score = 0;
  $p2Score = 0;

  // Sanitize since they just entered their names.
  // Otherwise they'll implicitly reset the entire thing
  header("HTTP/1.1 303 See Other");
  header("Location: http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
} elseif(
    isset($_REQUEST['endTurn'])
    and
    // Learned: About the difficulties of the POST/GET redirect cycle
    // Make sure we aren't resubmitting a previous request
    // Have to do it like this because a POST/GET redirect will
    // wipe out the ability to see the end screen
    // In short: Make sure they aren't resubmitting a past move by making sure
    // P1 actually has the card they're playing
    count(array_intersect($p1Deck, [$_REQUEST['p1Selection']])) > 0) {
  $p1Dealt = $_REQUEST['p1Selection'];
  $p2Dealt = $_REQUEST['p2Selection'];

  // Learned: About array_diff and [] array syntax
  // Literally just takes out what's in the second array
  // Nifty, that one
  $p1Deck = array_diff($p1Deck, [$p1Dealt]);
  $p2Deck = array_diff($p2Deck, [$p2Dealt]);
  
  switch(compare_cards($p1Dealt, $p2Dealt)) {
    case 0:
      $resultText = "$p1Name and $p2Name came to a draw.";
      break;
    case 1:
      $p1Score++;
      $resultText = "$p1Name  wins this round.";
      break;
    case -1:
      $p2Score++;
      $resultText = "$p2Name  wins this round.";
      break;
  }

  // Game's over.
  if(count($p1Deck) == 0) {
    // Pew pew pew!
    // I'm seriously starting to like this op
    switch($p1Score <=> $p2Score) {
      case 0: $resultText = "The game ends in a draw.";
        break;
      case 1: $resultText = "The game ends with a win for $p1Name";
        break;
      case -1: $resultText = "The game ends with a win for $p2Name";
        break;
    }
    $fileToShow = "win.php";
    // Learned about adding dates to PHP from MySQL
    // You'd think prepared statements would have a date param string
    $curDate = date("Y-m-d");
    $who = $p1Score <=> $p2Score;
    $adder->bind_param("siss", $curDate, $who, $p1Name, $p2Name);
    $adder->execute();

    // Learned about error logging in PHP without having access to admin functions
    // This'd be useful to teach directly.
    error_log("Added $curDate $who", 3, "/home/leejo/public_html/private/module05assign05a/log");

    session_destroy();
  }

} elseif(isset($_REQUEST['reset'])) {
  session_destroy();
  // Gotta sanitize again so we aren't stuck in another loop
  header("HTTP/1.1 303 See Other");
  header("Location: http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
}

// Where deck is either $p1Deck or $p2Deck
function displayDeck($deck, $id) {
  echo "<select name='$id' class='deckPicker'>\n";
  foreach($deck as $card) {
    echo "<option data-img-src='gamepieces/$card.jpg' data-img-class='card' value='$card'/>";
  }
  echo "</select>\n";
}


// Save the current state
$_SESSION['p1Deck'] = $p1Deck;
$_SESSION['p2Deck'] = $p2Deck;
$_SESSION['p1Score'] = $p1Score;
$_SESSION['p2Score'] = $p2Score;
$_SESSION['p1Name'] = $p1Name;
$_SESSION['p2Name'] = $p2Name;

// Since we do a POST/GET redirect, we lose $resultText, so we have
// to store it somehow.
$_SESSION['resultText'] = $resultText;

// Store the current page in session
$_SESSION['fileToShow'] = $fileToShow;

?>

<html>
  <head>
    <meta charset="UTF-8"/>
    <link rel="stylesheet" href="style.css"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="image-picker.min.js"></script>
    <link rel="stylesheet" href="image-picker.css"/>
    <script src="main.js"></script>
  </head>
  <h1>Strategy War</h1>
  <?php include($fileToShow) ?>

  <hr>
  <?php /* print_r($_SESSION);*/ ?>
  <hr>
  <?php
    ini_set("highlight.default", '"class="hDef');
    ini_set("highlight.keyword", '"class="hKey');
    ini_set("highlight.string", '"class="hStr');
    ini_set("highlight.html", '"class="hHtml');
    ini_set("highlight.comment", '"class="hComm');
    ?>

    <h1>index.php</h1>
    <?php highlight_file("./index.php"); ?>
    
    <h1>play.php</h1>
    <?php highlight_file("./play.php"); ?>
    <h1>win.php</h1>
    <?php highlight_file("./win.php"); ?>
  ?>
</html>
