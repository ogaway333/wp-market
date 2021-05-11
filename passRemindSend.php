<?php
require('function.php');
require('searchSystem.php');
debug('セッション変数の中身：'.print_r($_SESSION,true));
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード再設定メール送信ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
if(!empty($_POST)){
  $email=$_POST['email'];
  validRequired($email, 'email');
  if(empty($err_msg)){
    validEmail($email, 'email');
    validMaxLen($email, 'email');
    if(empty($err_msg)){
      debug('バリデーションOK。');
      try {
        $dbh=dbConnect();
        $sql="SELECT count(*) FROM users WHERE email=:email AND delete_flg=0";
        $data = array('email' => $email);
        $stmt = queryPost($dbh, $sql, $data);
        // クエリ結果の値を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if($stmt && array_shift($result)){
          $auth_key = makeRandKey(); //認証キー生成
          //メールを送信
          $from = 'info@gmail.com';
          $to = $email;
          $subject = '【パスワード再発行認証】｜WP-MARKET';
          //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
          //EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
          $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：http://localhost:8888/wp-market/passRemindRecieve.php
認証キー：{$auth_key}
※認証キーの有効期限は30分となります

認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
http://localhost:8888/wp-market/passRemindSend.php
EOT;

          sendMail($from, $to, $subject, $comment);

          //認証に必要な情報をセッションへ保存
          $_SESSION['auth_key'] = $auth_key;
          $_SESSION['auth_email'] = $email;
          $_SESSION['auth_key_limit'] = time()+(60*30); //現在時刻より30分後のUNIXタイムスタンプを入れる
          debug('セッション変数の中身：'.print_r($_SESSION,true));

          header("Location:passRemindRecieve.php"); //認証キー入力ページへ



        }else{
          debug('クエリに失敗したかDBに登録のないEmailが入力されました。');
          $err_msg['common'] = MSG07;
        }
      } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = MSG07;
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
  <p class="remind-text">登録済みアカウントのメールアドレスを入力してください。ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送り致します。</p>
   <form class="passRemind-form" action="" method="post">
      <?php if(!empty($err_msg['common'])){ ?>
      <div class='area-msg'><?php echo $err_msg['common']; ?></div>
      <?php } ?>
      <?php if(!empty($err_msg['email'])){ ?>
      <div class='area-msg'><?php echo $err_msg['email']; ?></div>
      <?php } ?>
      <label for="" class="">
        メールアドレス
        <input type="text" name='email' placeholder="example@example.com">
      </label>
      <input type="submit" value="送信する">
   </form>
  </section>

  <?php require('footer.php')?>
</body>
