<?php
require('function.php');
require('searchSystem.php');
debug('セッション変数の中身：'.print_r($_SESSION,true));
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　プロフィール編集ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
//ログイン認証
require('auth.php');

$dbFormData=get_userdata($_SESSION['user_id']);

if(!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST,true));
  debug('FILE情報：'.print_r($_FILES,true));
  $username=$_POST['username'];
  $email=$_POST['email'];
  $self_msg=$_POST['self_msg'];

  //画像をアップロードし、パスを格納
  $user_icon = ( !empty($_FILES['user_icon']['name']) ) ? uploadImg($_FILES['user_icon'],'user_icon') : '';
  // 画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
  $user_icon= ( empty($user_icon) && !empty($dbFormData['user_icon']) ) ? $dbFormData['user_icon'] : $user_icon;
  if($username !== $dbFormData['username']){
      //未入力チェック
      validRequired($username, 'username');
      //最大文字数チェック
      validMaxLen($username, 'username');
  }

  if($email !== $dbFormData['email']){
      //未入力チェック
      validRequired($email, 'email');
      //最大文字数チェック
      validMaxLen($email, 'email');
      //バリデーション関数（Email形式チェック）
      validEmail($email, 'email');
      //重複チェック
      validEmailDup($email);

  }

  if($self_msg !== $dbFormData['self_msg']){
      //最大文字数チェック
      validMaxLen($self_msg, 'self_msg');
  }


  if(empty($err_msg)){
    debug('バリデーションOKです。');
    try {
      $dbh=dbConnect();
      $sql='UPDATE users SET username = :username, email = :email, user_icon = :user_icon, self_msg = :self_msg WHERE id = :u_id';
      $data = array(
          ':username' => $username,
          ':email' => $email,
          ':user_icon' => $user_icon,
          ':self_msg' => $self_msg,
          ':u_id' => $_SESSION['user_id']
      );
      debug('SQL：'.$sql);
      debug('流し込みデータ：'.print_r($data,true));
      $stmt=queryPost($dbh, $sql, $data);
      // クエリ成功の場合
      if($stmt){
          debug('マイページへ遷移します。');
          header("Location:mypage.php"); //マイページへ
      }
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }


  }




}
?>


<?php require('head.php'); ?>
<body>
  <?php require('header.php');?>
  <section class="container-width regist-container">
    <h1>プロフィール編集</h1>
    <form action="" method="post" class="regist-form" enctype="multipart/form-data">
    <div class="icon-cover" style='margin-bottom:30px;'>
      <span>ユーザーアイコン</span><span class="area-msg"><?php if(!empty($err_msg['user_icon'])) echo $err_msg['user_icon']; ?></span>
      <label class="area-drop">
        <span class="upload-button-text">ドラッグ＆<br>ドロップ</span>
        <input type="file" name="user_icon" class="input-file">
          <img src="<?php if(!empty(getFormData('user_icon'))) echo getFormData('user_icon'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('user_icon'))) echo 'display:none;' ?>">
      </label>
      <span class="area-msg"><?php if(!empty($err_msg['user_icon'])) echo $err_msg['user_icon']; ?></span>
    </div>
      <label for="" class="">
        ユーザー名<span class="label-require">[必須]</span> <span class="area-msg"><?php if(!empty($err_msg['username'])) echo $err_msg['username']; ?></span>
        <input type="text" name='username' value="<?php if(!empty(getFormData('username'))) echo getFormData('username'); ?>">
      </label>
      <label for="" class="">
        メールアドレス<span class="label-require">[必須]</span> <span class="area-msg"><?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?></span>
        <input type="text" name='email' value="<?php if(!empty(getFormData('email'))) echo getFormData('email'); ?>">
      </label>
      <label for="" class="">
        公開プロフィール<span class="label-require">[1000文字以内]</span> <span class="area-msg"><?php if(!empty($err_msg['comment'])) echo $err_msg['comment']; ?></span>
        <textarea name="self_msg" id="" rows="30" class="regist-textarea"><?php if(!empty(getFormData('self_msg'))) echo getFormData('self_msg'); ?></textarea>
      </label>

      <input type="submit" value="公開">

    </form>

  </section>
  <?php require('footer.php')?>
</body>
