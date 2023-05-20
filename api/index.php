
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
  <p>It is possible to get file-based routing working on the community <a href="https://github.com/vercel-community/php">Vercel Functions PHP runtime</a>.</p>
  <p>You simply need the following configuration:</p>
  <code>vercel.json</code>
  <pre>
{
  "functions": {
    "api/*.php": {
      "runtime": "vercel-php@0.6.0"
    }
  },
  "routes": [{ "src": "/(.*)", "dest": "/api/$1" }]
}
  </pre>
  <p>And, make sure to place all your PHP files under <code>/api</code>.</p>
  <nav>
    <a href="index.php">Go to Index</a>
    <a href="page-two.php">Go to Page 2</a>
    <a href="php-info.php">View PHP Info</a>
    <a href="dir/nested.php">Go to Nested Page</a>
  </nav>
</body>
</html>