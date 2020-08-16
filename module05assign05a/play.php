<body>
  <h1><?php echo $resultText; ?></h1>
  <form action="index.php" method="POST">
    <div id="playerContainer">
      <div class="playerDisplay" id="p1">
        <h1><?php echo $p1Name; ?></h1>
        <?php echo "<p class='score'>Score: $p1Score</p>"; ?>
        <div id="p1DeckDisplay">
          <?php displayDeck($p1Deck, "p1Selection"); ?>
        </div>
        <!-- Learned about type=button -->
        <button type=button onclick="hideDeck(1);">Toggle Deck Hidden</button>
      </div>
      <div class="playerDisplay" id="p2">
        <h1><?php echo $p2Name; ?></h1>
        <?php echo "<p class='score'>Score: $p2Score</p>"; ?>
        <div id="p2DeckDisplay">
          <?php displayDeck($p2Deck, "p2Selection"); ?>
        </div>
        <button type=button onclick="hideDeck(2);">Toggle Deck Hidden</button>
      </div>
    </div>
    <button type=submit name="endTurn">End Turn</button>
  </form>
  <form action="index.php" method="POST">
    <button type=submit name="reset">Reset Game</button>
  </form>

  <hr>
  <h1>Rules</h1>
  <div id="rules">
  The rules:
  <ol>
  <li> Each player receives half of a deck of standard
   cards.</li>
  <li> Each player is able to see their entire deck, but
   but should not allow their opponent to see it.
    <ul>
      <li><b>Players should use the "Toggle Deck Hidden" button to keep their deck invisible when their opponent is picking.</b></li>
    </ul>
   </li>
  <li> Each round:
    <ol>
        <li>Each player chooses a card to put into play.</li>
        <li>The player with the higher value card wins that round.
          <ul>
            <li>The suit of a card doesn't matter.</li>
            <li>Aces are highest</li>
            <li>Kings beat Queens, Queens beat Jacks.</li>
            <li>Kings, Queens, and Jesters rule all numbers.</li>
            <li>Numbered cards only beat cards with numbers below them.</li>
          </ul>
        </li> 
      </ol>
    </li>
    <li> After all cards have been played, the number of wins for each player is tallied</li>
     and the player with the most wins wins.
  </ol>
  </div>
</body>
