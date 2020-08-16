<html>
  <head>
    <meta charset="UTF-8"/>
  </head>
  <body>
    <h1>Module 03 - Assign 03b</h1>
    <h2>The Writinator 9000</h2>
    <hr>
    <?php
      $op = $_REQUEST["op"][0];
      if ($op == 'w' || $op == 'a') {
        echo "'$op'";
        $data = $_REQUEST["data"];
        $file = fopen("filey.txt", "$op");
        fwrite($file, $data);
        fclose($file);
      } elseif ($op == 'r') {
        readfile("filey.txt");
      }
    ?>
  </body>
</html>
