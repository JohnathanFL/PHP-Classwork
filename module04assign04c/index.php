<?php
extract($_REQUEST);
include("../pass.php");
$conn = mysqli_connect("localhost", "leejo", $password, "leejo_TestyDB");
// Learned about mysql prepared statements in PHP
$deleter = $conn->prepare("DELETE FROM Songs WHERE title = ?");
$adder = $conn->prepare("INSERT INTO Songs (title, album, artist, year, genre) VALUES (?, ?, ?, ?, ?)");

#print_r($_REQUEST);

function sanitize_reload() {
  // Learned: About the Post/Redirect/GET cycle
  // https://en.wikipedia.org/wiki/Post/Redirect/Get
  // Makes debugging a wee bit trickier, but it's annoying to delete over and over
  header("HTTP/1.1 303 See Other");
  header("Location: http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
}

if($_REQUEST) {
  if(isset($_REQUEST["delete"])) {
    $deleter->bind_param("s", $delete);
    $deleter->execute();
    sanitize_reload();
  } elseif (isset($_REQUEST["add"])) {
    #echo "$title $album $artist $year $genre";
    $adder->bind_param("sssss", $title, $album, $artist, $year, $genre);
    $adder->execute();
    sanitize_reload();
  }

}

$deleter->close();
$adder->close();

function song_row($title, $album, $artist, $year, $genre) {
  echo <<<HERE
    <tr>
      <td>
        <form action="index.php" method="POST">
          <button type=submit name="delete" value="$title">X</button>
        </form>
      </td>
      <td>$title</td>
      <td>$album</td>
      <td>$artist</td>
      <td>$year</td>
      <td>$genre</td>
    </tr>
HERE;
}

function print_songs() {
  global $conn, $filter, $filterBy;

  $ORDER_BY = "ORDER BY artist, album, title, year, genre";
  
  if(isset($_REQUEST["filter"])) {
    $res = $conn->query("SELECT title, album, artist, year, genre FROM Songs WHERE $filterBy like '%$filter%' $ORDER_BY;");
  } else
    $res = $conn->query("SELECT title, album, artist, year, genre FROM Songs $ORDER_BY;");
  while($field = mysqli_fetch_array($res))
    song_row($field[0], $field[1], $field[2], $field[3], $field[4]);
}

?>

<html>
  <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <h1>Playlist</h1>
    <h4><a href="../">Back</a></h4>
    <form action="index.php" method=POST>
      <input type=text name="title" required=true placeholder="title"/>
      <input type=text name="album" required=true placeholder="album"/>
      <input type=text name="artist" required=true placeholder="artist"/>
      <input type=year name="year" required=true />
      <select name="genre">
        <option>metal</option>
        <option>imetal</option>
        <option>pmetal</option>
      </select>
      <br>
      <button id="addBtn" type="submit" name="add">Add Song</button>
    </form>
    <hr>
    <form action="index.php" method=GET>
      <?php echo "<input type=text placeholder='filter' name='filter' value='$filter'/>"; ?>
      <select name='filterBy' selected='$filterBy'>
       <?php
        $filters = array("Title", "Album", "Artist", "Year", "Genre");
        if(isset($filterBy)) {
          echo "FILTERING FOR $filterBy";
          // Sort such that the filtered by thing "bubbles" to the top
          usort($filters, function($lhs, $rhs) {
            global $filterBy;
            if($lhs == $filterBy and $rhs != $filterBy) return -1;
            elseif ($rhs == $filterBy and $lhs != $filterBy) return 1;
            else return 0;
          });
        }
        foreach($filters as $f)
          echo "<option name='" . strtolower($f) . "'>$f</option>\n";
        ?>
      </select>
      <button type=submit>Filter</button>
    </form>
    <table>
      <tbody>
        <tr>
          <th></th>
          <th>Title</th>
          <th>Album</th>
          <th>Artist</th>
          <th>Year</th>
          <th>Genre</th>
        </tr>
        <?php print_songs(); ?>
      </tbody>
    </table>
    <hr>
    <?php
      ini_set("highlight.default", '"class="hDef');
      ini_set("highlight.keyword", '"class="hKey');
      ini_set("highlight.string", '"class="hStr');
      ini_set("highlight.html", '"class="hHtml');
      ini_set("highlight.comment", '"class="hComm');
      highlight_file("index.php");
    ?>
    
  </body>

</html>


<?php
mysqli_close($conn);
?>
