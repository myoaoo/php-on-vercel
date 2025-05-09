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
  <p>This is page two.</p>
<!-- 推荐这3种写法 -->
<img src="/source/images/12.png" alt="正确路径1">
<img src="source/images/12.png" alt="正确路径2"> 
<img src="/api/source/images/12.png" alt="正确路径3">
  <img src="https://www.oaoo.top/assets/images/post/3-1.png" alt="Example Image 5">

  <nav>
    <a href="index.php">Go to Index</a>
    <a href="page-two.php">Go to Page 2</a>
    <a href="php-info.php">View PHP Info</a>
    <a href="dir/nested.php">Go to Nested Page</a>
  </nav>
</body>
</html>    