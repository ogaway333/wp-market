<?php
require('function.php');
require('searchSystem.php');
debug('セッション変数の中身：'.print_r($_SESSION,true));
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード変更ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
//ログイン認証
require('auth.php');
$userData=get_userdata($_SESSION['user_id']);

if(!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST,true));
  $old_pass=$_POST['old_pass'];
  $new_pass=$_POST['new_pass'];
  $renew_pass=$_POST['renew_pass'];

  //未入力チェック
  validRequired($old_pass, 'old_pass');
  validRequired($new_pass, 'new_pass');
  validRequired($renew_pass, 'renew_pass');

  if(empty($err_msg)){
    debug('未入力チェック完了');
    //古いパスワードのチェック
    validPass($old_pass, 'old_pass');
    //古いパスワードのチェック
    validPass($new_pass, 'new_pass');

    if(!password_verify($old_pass, $userData['password'])){
      $err_msg['old_pass'] = MSG10;
    }

    //新しいパスワードと古いパスワードが同じかチェック
    if($old_pass === $new_pass){
      $err_msg['new_pass'] = MSG11;
    }
    //パスワードとパスワード再入力が合っているかチェック（ログイン画面では最大、最小チェックもしていたがパスワードの方でチェックしているので実は必要ない）
    validMatch($new_pass, $renew_pass, 'renew_pass');

    var_dump($err_msg);

    if(empty($err_msg)){
      debug('バリデーション完了');
      //例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'UPDATE users SET password = :pass WHERE id = :id';
        $data = array(':id' => $_SESSION['user_id'], ':pass' => password_hash($new_pass, PASSWORD_DEFAULT));
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
          debug('パスワード変更成功!');
          header("Location:mypage.php"); //マイページへ

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
  <section class="container-width regist-container">
    <h1>パスワード変更</h1><span class="area-msg"><?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?></span>
    <form action="" method="post" class="regist-form">
      <label for="" class="">
        古いパスワード<span class="label-require">[必須/半角英数字の8文字以上]</span><span class="area-msg"><?php if(!empty($err_msg['old_pass'])) echo $err_msg['old_pass']; ?></span>
        <input type="password" name='old_pass' value="<?php if(!empty(getFormData('old_pass'))) echo getFormData('old_pass'); ?>">
      </label>
      <label for="" class="">
        新しいパスワード<span class="label-require">[必須/半角英数字の8文字以上]</span><span class="area-msg"><?php if(!empty($err_msg['new_pass'])) echo $err_msg['new_pass']; ?></span>
        <input type="password" name='new_pass' value="<?php if(!empty(getFormData('new_pass'))) echo getFormData('new_pass'); ?>">
      </label>
      <label for="" class="">
        新しいパスワード(再入力)<span class="label-require">[必須/半角英数字の8文字以上]</span><span class="area-msg"><?php if(!empty($err_msg['renew_pass'])) echo $err_msg['renew_pass']; ?></span>
        <input type="password" name='renew_pass' value="<?php if(!empty(getFormData('renew_pass'))) echo getFormData('renew_pass'); ?>">
      </label>

      <input type="submit" value="変更">

    </form>

  </section>
  <?php require('footer.php')?>
</body>
