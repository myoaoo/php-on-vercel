<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PHP on Vercel</title>
</head>
<body>
  <h1>
    <?php
      echo 'Hello from Vercel PHP!';
    ?>
  </h1>
  <p>This is a nested page.</p>
  <nav>
    <a href="index.php">Go to Index</a>
    <a href="page-two.php">Go to Page 2</a>
    <a href="dir/nested.php">Go to Nested Page</a>
    <a href="php-info.php">View PHP Info</a>
  </nav>
</body>
</html>