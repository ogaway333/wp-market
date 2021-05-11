<?php
require('function.php');
require('searchSystem.php');
debug('セッション変数の中身：'.print_r($_SESSION,true));
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード再発行認証キー入力ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//SESSIONに認証キーがあるか確認、なければリダイレクト
if(empty($_SESSION['auth_key'])){
   header("Location:passRemindSend.php"); //認証キー送信ページへ
}

if(!empty($_POST)){
  $auth_key=$_POST['token'];
  validRequired($token, 'token');
  if(empty($err_msg)){
    debug('未入力チェックOK。');

    //半角チェック
    validHalf($auth_key, 'token');
    if(empty($err_msg)){
      debug('バリデーションOK。');
      if($auth_key !== $_SESSION['auth_key']){
        $err_msg['common'] = MSG13;
      }
      if(time() > $_SESSION['auth_key_limit']){
        $err_msg['common'] = MSG14;
      }
      if(empty($err_msg)){
        debug('認証OK');
        $pass = makeRandKey(); //パスワード生成
        try {
          // DBへ接続
          $dbh = dbConnect();
          // SQL文作成
          $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
          $data = array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass, PASSWORD_DEFAULT));
          // クエリ実行
          $stmt = queryPost($dbh, $sql, $data);
          // クエリ成功の場合
          if($stmt){
            debug('クエリ成功。');

            //メールを送信
            $from = 'info@gmail.com';
            $to = $_SESSION['auth_email'];
            $subject = '【パスワード再発行完了】｜WP-MARKET';

            $comment = <<<EOT
本メールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力頂き、ログインください。

ログインページ：http://localhost:8888/wp-market/login.php
再発行パスワード：{$pass}
※ログイン後、パスワードのご変更をお願い致します
EOT;
            sendMail($from, $to, $subject, $comment);

            //セッション削除
            session_unset();
            debug('セッション変数の中身：'.print_r($_SESSION,true));

            header("Location:login.php"); //ログインページへ

          }else{
            debug('クエリに失敗しました。');
            $err_msg['common'] = MSG07;
          }

        } catch (Exception $e) {
          error_log('エラー発生:' . $e->getMessage());
          $err_msg['common'] = MSG07;
        }



      }

    }

  }
}



?>



<?php require('head.php'); ?>
<body>
  <?php require('header.php');?>
  <section class="container-width passRemind-container">
  <h2>パスワードを忘れた場合</h2>
  <p class="remind-text">ご指定のメールアドレスお送りした【パスワード再発行認証】メール内にある「認証キー」をご入力ください。</p>
   <form class="passRemind-form" action="" method="post">
      <?php if(!empty($err_msg['common'])){ ?>
      <div class='area-msg'><?php echo $err_msg['common']; ?></div>
      <?php } ?>
      <?php if(!empty($err_msg['email'])){ ?>
      <div class='area-msg'><?php echo $err_msg['email']; ?></div>
      <?php } ?>
      <label for="" class="">
        認証キー
        <input type="text" name='token'>
      </label>
      <input type="submit" value="再発行する">
   </form>
  </section>

  <?php require('footer.php')?>
</body>
