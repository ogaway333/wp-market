<?php
//共通変数・関数ファイルを読込み
require('function.php');
require('searchSystem.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　商品出品登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// GETデータを格納
$p_id=(!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
$dbFormData=(!empty($_GET['p_id'])) ? getProduct($_SESSION['user_id'], $p_id) : '';
$edit_flg = (empty($dbFormData)) ? false : true;
debug('商品ID：'.$p_id);
debug('フォーム用DBデータ：'.print_r($dbFormData,true));


// パラメータ改ざんチェック
//================================
// GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい商品データが取れないのでマイページへ遷移させる
if(!empty($p_id) && empty($dbFormData)){
  debug('GETパラメータの商品IDが違います。マイページへ遷移します。');
  header("Location:mypage.php"); //マイページへ
}

if(!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST,true));
  if(empty($_FILES)){
    debug('FILEがからです');
  }
  debug('FILE情報：'.print_r($_FILES,true));
  $name=$_POST['name'];
  $comment=$_POST['comment'];
  $demo_link=$_POST['demo_link'];
  $tag1=$_POST['tag1'];
  $tag2=$_POST['tag2'];
  $tag3=$_POST['tag3'];
  $tag4=$_POST['tag4'];
  $tag5=$_POST['tag5'];
  //zipファイルをアップロードし、パスを格納
  $file_name = ( !empty($_FILES['file_name']['name']) ) ? uploadZip($_FILES['file_name'],'file_name') : '';
  // zipファイルをPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
  $file_name = ( empty($file_name) && !empty($dbFormData['file_name']) ) ? $dbFormData['file_name'] : $file_name;
  //画像をアップロードし、パスを格納
  $pic1 = ( !empty($_FILES['pic1']['name']) ) ? uploadImg($_FILES['pic1'],'pic1') : '';
  // 画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
  $pic1 = ( empty($pic1) && !empty($dbFormData['pic1']) ) ? $dbFormData['pic1'] : $pic1;
  $pic2 = ( !empty($_FILES['pic2']['name']) ) ? uploadImg($_FILES['pic2'],'pic2') : '';
  $pic2 = ( empty($pic2) && !empty($dbFormData['pic2']) ) ? $dbFormData['pic2'] : $pic2;
  $pic3 = ( !empty($_FILES['pic3']['name']) ) ? uploadImg($_FILES['pic3'],'pic3') : '';
  $pic3 = ( empty($pic3) && !empty($dbFormData['pic3']) ) ? $dbFormData['pic3'] : $pic3;
  // 更新の場合はDBの情報と入力情報が異なる場合にバリデーションを行う
  if(empty($dbFormData)){
    //未入力チェック
    validRequired($name, 'name');
    //最大文字数チェック
    validMaxLen($name, 'name');
    //未入力チェック
    validRequired($demo_link, 'demo_link');
    //最大文字数チェック
    validMaxLen($demo_link, 'demo_link');
    //最大文字数チェック
    validMaxLen($tag1, 'tag1', 10);
    //最大文字数チェック
    validMaxLen($tag1, 'tag1', 10);
    //最大文字数チェック
    validMaxLen($tag2, 'tag2', 10);
    //最大文字数チェック
    validMaxLen($tag3, 'tag3', 10);
    //最大文字数チェック
    validMaxLen($tag4, 'tag4', 10);
    //最大文字数チェック
    validMaxLen($tag5, 'tag5', 10);
    //未入力チェック
    validRequired($comment, 'comment');
    //最大文字数チェック
    validMaxLen($comment, 'comment', 1000);
    //未入力チェック
    validRequired($file_name, 'file_name');
  }else{
    if($name !== $dbFormData['name']){
      //未入力チェック
      validRequired($name, 'name');
      //最大文字数チェック
      validMaxLen($name, 'name');
    }
    if($demo_link !== $dbFormData['demo_link']){
      //未入力チェック
      validRequired($name, 'demo_link');
      //最大文字数チェック
      validMaxLen($name, 'demo_link');
    }
    if($tag1 !== $dbFormData['tag1']){
      //最大文字数チェック
      validMaxLen($tag1, 'tag1', 10);
    }
    if($tag2 !== $dbFormData['tag2']){
      //最大文字数チェック
      validMaxLen($tag2, 'tag2', 10);
    }
    if($tag3 !== $dbFormData['tag3']){
      //最大文字数チェック
      validMaxLen($tag3, 'tag3', 10);
    }
    if($tag4 !== $dbFormData['tag4']){
      //最大文字数チェック
      validMaxLen($tag4, 'tag4', 10);
    }
    if($tag5 !== $dbFormData['tag5']){
      //最大文字数チェック
      validMaxLen($tag5, 'tag5', 10);
    }
    if($comment !== $dbFormData['comment']){
      //最大文字数チェック
      validMaxLen($comment, 'comment', 1000);
    }
    if($file_name !== $dbFormData['file_name']){
      //未入力チェック
      validRequired($file_name, 'file_name');
    }
  }


  if(empty($err_msg)){
    debug('バリデーションOKです。');
    try {
      $dbh=dbConnect();
      if($edit_flg){
        debug('db更新を開始');
        $sql='UPDATE product SET name = :name, comment = :comment, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3, tag1 = :tag1, tag2 = :tag2, tag3 = :tag3, tag4 = :tag4, tag5 = :tag5, file_name = :file_name, demo_link = :demo_link WHERE user_id = :u_id AND id = :p_id';

        $data = array(
          ':name' => $name,
          ':comment' => $comment,
          ':pic1' => $pic1,
          ':pic2' => $pic2,
          ':pic3' => $pic3,
          ':tag1' => $tag1,
          ':tag2' => $tag2,
          ':tag3' => $tag3,
          ':tag4' => $tag4,
          ':tag5' => $tag5,
          ':file_name' => $file_name,
          ':demo_link' => $demo_link,
          ':u_id' => $_SESSION['user_id'],
          ':p_id' => $p_id
        );
        debug('SQL：'.$sql);
        debug('流し込みデータ：'.print_r($data,true));
        $stmt=queryPost($dbh, $sql, $data);
        // クエリ成功の場合
        if($stmt){
          if($tag1 !== $dbFormData['tag1']){
            updateTags($tag1);
          }
          if($tag2 !== $dbFormData['tag2']){
            updateTags($tag2);
          }
          if($tag3 !== $dbFormData['tag3']){
            updateTags($tag3);
          }
          if($tag4 !== $dbFormData['tag4']){
            updateTags($tag4);
          }
          if($tag5 !== $dbFormData['tag5']){
            updateTags($tag5);
          }
          debug('マイページへ遷移します。');
          header("Location:mypage.php"); //マイページへ
        }
      }else{
        debug('DB新規登録です。');
        $sql = 'insert into product (name, comment, pic1, pic2, pic3, user_id, tag1, tag2, tag3, tag4, tag5, file_name, demo_link, create_date) values (:name, :comment, :pic1, :pic2, :pic3, :u_id, :tag1, :tag2, :tag3, :tag4, :tag5, :file_name, :demo_link, :date)';
        $data = array(
          ':name' => $name,
          ':comment' => $comment,
          ':pic1' => $pic1,
          ':pic2' => $pic2,
          ':pic3' => $pic3,
          ':u_id' => $_SESSION['user_id'],
          ':tag1' => $tag1,
          ':tag2' => $tag2,
          ':tag3' => $tag3,
          ':tag4' => $tag4,
          ':tag5' => $tag5,
          ':file_name' => $file_name,
          ':demo_link' => $demo_link,
          ':date' => date('Y-m-d H:i:s')
        );
        debug('SQL：'.$sql);
        debug('流し込みデータ：'.print_r($data,true));
        $stmt=queryPost($dbh, $sql, $data);
        // クエリ成功の場合
        if($stmt){
          updateTags($tag1);
          updateTags($tag2);
          updateTags($tag3);
          updateTags($tag4);
          updateTags($tag5);
          debug('マイページへ遷移します。');
          header("Location:mypage.php"); //マイページへ
        }
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
    <?php if($edit_flg){ ?>
      <h1>WordPressテーマの編集</h1>
    <?php }else{ ?>
      <h1>WordPressテーマの登録</h1>
    <?php } ?>
     <form action="" method="post" enctype="multipart/form-data" class="regist-form">
      <div class="area-msg">
        <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
      </div>
      <label for="" class="">
        作品名<span class="label-require">[必須]</span><span class="area-msg"><?php if(!empty($err_msg['name'])) echo $err_msg['name']; ?></span>
        <input type="text" name='name' placeholder="" value="<?php echo getFormData('name'); ?>">
      </label>
      <label for="" class="">
        デモサイトのリンク<span class="label-require">[必須]</span><span class="area-msg"><?php if(!empty($err_msg['demo_link'])) echo $err_msg['demo_link']; ?></span>
        <input type="text" name='demo_link' placeholder="" value="<?php echo getFormData('demo_link'); ?>">
      </label>
      <label for="" class="">
        紹介文<span class="label-require">[必須/1000文字以内]</span><span class="area-msg"><?php if(!empty($err_msg['comment'])) echo $err_msg['comment']; ?></span>
        <textarea name="comment" id="" rows="30" class="regist-textarea"><?php echo getFormData('comment'); ?></textarea>
      </label>
      <label for="" class="">
        タグ<span class="label-require">[各10文字以内]</span>
        <span class="area-msg"><?php if(!empty($err_msg['tag1'])) echo $err_msg['tag1']; ?></span>
        <input type="text" name='tag1' placeholder="" value="<?php echo getFormData('tag1'); ?>">
        <span class="area-msg"><?php if(!empty($err_msg['tag2'])) echo $err_msg['tag2']; ?></span>
        <input type="text" name='tag2' placeholder="" value="<?php echo getFormData('tag2'); ?>">
        <span class="area-msg"><?php if(!empty($err_msg['tag3'])) echo $err_msg['tag3']; ?></span>
        <input type="text" name='tag3' placeholder="" value="<?php echo getFormData('tag3'); ?>">
        <span class="area-msg"><?php if(!empty($err_msg['tag4'])) echo $err_msg['tag4']; ?></span>
        <input type="text" name='tag4' placeholder="" value="<?php echo getFormData('tag4'); ?>">
        <span class="area-msg"><?php if(!empty($err_msg['tag5'])) echo $err_msg['tag5']; ?></span>
        <input type="text" name='tag5' placeholder="" value="<?php echo getFormData('tag5'); ?>">
      </label>
      商品画像
      <div style="overflow:hidden;">
        <div class="imgDrop-container">
          <label class="area-drop">
            <span class="upload-button-text">ドラッグ＆<br>ドロップ</span>
            <input type="file" name="pic1" class="input-file">
            <img src="<?php echo getFormData('pic1'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic1'))) echo 'display:none;' ?>">
          </label>
          <span class="area-msg"><?php if(!empty($err_msg['pic1'])) echo $err_msg['pic1']; ?></span>
        </div>
        <div class="imgDrop-container">
          <label class="area-drop">
            <span class="upload-button-text">ドラッグ＆<br>ドロップ</span>
            <input type="file" name="pic2" class="input-file">
            <img src="<?php echo getFormData('pic2'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic2'))) echo 'display:none;' ?>">
          </label>
           <span class="area-msg"><?php if(!empty($err_msg['pic2'])) echo $err_msg['pic2']; ?></span>
        </div>
        <div class="imgDrop-container">
          <label class="area-drop">
            <span class="upload-button-text">ドラッグ＆<br>ドロップ</span>
            <input type="file" name="pic3" class="input-file">
            <img src="<?php echo getFormData('pic3'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic3'))) echo 'display:none;' ?>">
          </label>
          <span class="area-msg"><?php if(!empty($err_msg['pic3'])) echo $err_msg['pic3']; ?></span>
        </div>
      </div>
      アップロードファイル<span class="label-require">[必須/zipファイルのみ]</span><span class="area-msg"><?php if(!empty($err_msg['file_name'])) echo $err_msg['file_name']; ?></span>
      <label for="" class='file-upload-button'>
        <span class="upload-button-text">ファイル保存</span>
        <input type="file" name="file_name" class="input-file js-upload-file">
        <div class="js-upload-filename">ファイルが未選択です</div>
      </label>
      <input type="submit" value="公開">

    </form>

  </section>
  <?php require('footer.php')?>
</body>
