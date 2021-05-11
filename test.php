<?php
var_dump($_FILES);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
 <form action="test.php" method="post" enctype="multipart/form-data">
 <div>
 <label for="">
  <input type="text">
  <p>unko</p>
  <input type="file" name="fname">
  <input type="submit" value="アップロード">
 </label>
 </div>
</form>
</body>
</html>
