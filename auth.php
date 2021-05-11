<?php
if(!empty($_SESSION['login_date'])){
  if(($_SESSION['login_date']+$_SESSION['login_limit']) < time()){
    debug('ログイン有効期限オーバーです。');

    // セッションを削除（ログアウトする）
    session_destroy();
    // ログインページへ
    header("Location:login.php");
  }else{
    debug('ログイン有効期限以内です。');
    //ログイン時間更新
    $_SESSION['login_date']=time();

    //現在実行中のスクリプトファイル名がlogin.phpの場合
    //$_SERVER['PHP_SELF']はドメインからのパスを返すため
    //さらにbasename関数を使うことでファイル名だけを取り出せる
    if(basename($_SERVER['PHP_SELF']) === 'login.php'){
      debug('マイページへ遷移します。');
      header("Location:index.php"); //ホームページへ
    }
  }
}else{
  debug('未ログインユーザーです。');
  if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
     header("Location:login.php"); //ログインページへ
  }
}


?>
