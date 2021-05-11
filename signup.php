<?php
require('function.php');
require('searchSystem.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　新規登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
if(!empty($_POST)){
  $email=$_POST['email'];
  $username=$_POST['username'];
  $pass=$_POST['pass'];
  $pass_re=$_POST['pass_re'];

  validRequired($email, 'email');
  validRequired($username, 'username');
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');
  if(empty($err_msg)){
    //ユーザー名の最大文字数チェック
    validMaxLen($username, 'usename');
    //emailの形式チェック
    validEmail($email, 'email');
    //emailの最大文字数チェック
    validMaxLen($email, 'email');
    //email重複チェック
    validEmailDup($email);

    //パスワードの半角英数字チェック
    validHalf($pass, 'pass');
    //パスワードの最大文字数チェック
    validMaxLen($pass, 'pass');
    //パスワードの最小文字数チェック
    validMinLen($pass, 'pass');


    //パスワード（再入力）の最大文字数チェック
    validMaxLen($pass_re, 'pass_re');
    //パスワード（再入力）の最小文字数チェック
    validMinLen($pass_re, 'pass_re');
    if(empty($err_msg)){

      //パスワードとパスワード再入力が合っているかチェック
      validMatch($pass, $pass_re, 'pass_re');

      if(empty($err_msg)){
          try{
            $dbh = dbConnect();
            $sql = 'INSERT INTO users (username,email,password,login_time,create_date) VALUES(:username,:email,:pass,:login_time,:create_date)';
            $data = array(
              'username'=>$username,
              ':email'=>$email,
              ':pass'=>password_hash($pass, PASSWORD_DEFAULT),
              ':login_time'=>date('Y-m-d H:i:s'),
              ':create_date'=>date('Y-m-d H:i:s')
            );
            $stmt=queryPost($dbh, $sql, $data);
            if($stmt){
              //ログイン有効期限（デフォルトを１時間とする）
              $sesLimit = 60*60;
              //最終ログイン日時を現在日時に
              $_SESSION['login_date'] = time();
              $_SESSION['login_limit'] = $sesLimit;
              // ユーザーIDを格納
              $_SESSION['user_id'] = $dbh->lastInsertId();

              debug('セッション変数の中身：'.print_r($_SESSION,true));

              header("Location:index.php");
            }
          }catch(Exception $e){
          error_log('エラー発生:' . $e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
    }
  }
}

?>


<?php require('head.php');?>
<body>
  <?php require('header.php');?>
  <div class="background">
    <form action="" method="post" class="signup-form">
      <h2>新規登録</h2>
      <?php if(!empty($err_msg['common'])){ ?>
      <div class='area-msg'><?php echo $err_msg['common']; ?></div>
      <?php } ?>
      <?php if(!empty($err_msg['email'])){ ?>
      <div class='area-msg'><?php echo $err_msg['email']; ?></div>
      <?php } ?>
      <input type="text" name="email" placeholder="メールアドレス" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
      <?php if(!empty($err_msg['username'])){ ?>
      <div class='area-msg'><?php echo $err_msg['username']; ?></div>
      <?php } ?>
      <input type="text" name="username" placeholder="ユーザーネーム" value="<?php if(!empty($_POST['username'])) echo $_POST['username']; ?>">
      <?php if(!empty($err_msg['pass'])){ ?>
      <div class='area-msg'><?php echo $err_msg['pass']; ?></div>
      <?php } ?>
      <input type="password" name="pass" placeholder="パスワード(半角英数字の8文字以上)" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
      <?php if(!empty($err_msg['pass_re'])){ ?>
      <div class='area-msg'><?php echo $err_msg['pass_re']; ?></div>
      <?php } ?>
      <input type="password" name="pass_re" placeholder="パスワード確認" value="<?php if(!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>">
      <input type="submit" value="登録">
    </form>
  </div>
  <?php require('footer.php')?>
</body>
</html>
