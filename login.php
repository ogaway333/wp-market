<?php
//共通変数・関数ファイルを読込み
require('function.php');
require('searchSystem.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログインページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

if(!empty($_POST)){
  $email=$_POST['email'];
  $pass=$_POST['pass'];
  $login_save=$_POST['login_save'];
  //emailの形式チェック
  validEmail($email, 'email');
  //emailの最大文字数チェック
  validMaxLen($email, 'email');

  //パスワードの半角英数字チェック
  validHalf($pass, 'pass');
  //パスワードの最大文字数チェック
  validMaxLen($pass, 'pass');
  //パスワードの最小文字数チェック
  validMinLen($pass, 'pass');

  if(empty($err_msg)){
    debug('バリデーション成功');
    try {
      $dbh = dbConnect();
      $sql = 'SELECT password,id FROM users WHERE :email=email AND delete_flg=0';
      $data = array(':email' => $email);
      debug('クエリ結果の中身：');
      $stmt = queryPost($dbh, $sql, $data);
      $result= $stmt->fetch(PDO::FETCH_ASSOC);
      debug('クエリ結果の中身：'.print_r($result,true));
      // パスワード照合
      if(!empty($result) && password_verify($pass, array_shift($result))){
        debug('パスワードがマッチしました。');

        if($login_save === 'on'){
          debug('ログイン期限は30日です');
          //ログイン有効期限（デフォルトを１時間とする）
          $sesLimit = 60*60;
          $_SESSION['login_date']=time();
          $_SESSION['login_limit']=$sesLimit * 24 * 30;
          $_SESSION['user_id']=$result['id'];
          debug('セッション変数の中身：'.print_r($_SESSION,true));
          debug('ホームページへ遷移します。');
          header("Location:index.php");
        }else{
          debug('ログイン期限は１時間です');
          //ログイン有効期限（デフォルトを１時間とする）
          $sesLimit = 60*60;
          $_SESSION['login_date']=time();
          $_SESSION['login_limit']=$sesLimit;
          $_SESSION['user_id']=$result['id'];
          debug('セッション変数の中身：'.print_r($_SESSION,true));
          debug('ホームページへ遷移します。');
          header("Location:index.php");
        }

      }else {
        debug('パスワードがアンマッチです。');
        $err_msg['common'] = MSG09;
      }
    } catch (Exception $e) {
          error_log('エラー発生:' . $e->getMessage());
          $err_msg['common'] = MSG07;
    }
  }

}

?>


<?php require('head.php');?>
<body>
  <?php require('header.php');?>
  <div class="background">
    <form action="" class="login-form" method="post">
      <h2>ログイン</h2>
      <a class="passRemind-link"href="passRemindSend.php">パスワードを忘れた場合</a>
      <?php if(!empty($err_msg['common'])){ ?>
      <div class='area-msg'><?php echo $err_msg['common']; ?></div>
      <?php } ?>
      <input type="text" name="email" placeholder="メールアドレス" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
      <input type="password" name="pass" placeholder="パスワード" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
      <label for="check_login_save" class="checkbox01">
        <input type="checkbox" id="check_login_save" name="login_save" value='on'>
        <span class="checkbox-parts">ログインを保持</span>
      </label>
      <input type="submit" value="ログイン">
    </form>
  </div>
  <?php require('footer.php')?>
</body>
</html>
