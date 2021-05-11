<?php
require('function.php');
require('searchSystem.php');
debug('セッション変数の中身：'.print_r($_SESSION,true));

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　レビューページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');


// GETデータを格納
$p_id=(!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
$product_data=(!empty($_GET['p_id'])) ? get_ProductDetail($p_id) : '';
$review_data=get_review($_SESSION['user_id'], $p_id);

// パラメータ改ざんチェック
//================================
// GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい商品データが取れないのでマイページへ遷移させる
if(!empty($p_id) && empty($product_data)){
  debug('GETパラメータの商品IDが違います。マイページへ遷移します。');
  header("Location:index.php"); //ホームページへ
}

if($_SESSION['user_id'] === $product_data['user_id']){
  debug('GETパラメータの商品IDが違います。マイページへ遷移します。');
  header("Location:productDetail.php?p_id=$p_id"); //ホームページへ
}



if(!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST,true));
  $comment=$_POST['comment'];
  debug('バリデーション開始');
  if(empty($review_data)){
    $err_msg['score']=MSG16;
  }
  //未入力チェック
  validRequired($comment, 'comment');
  //最大文字数チェック
  validMaxLen($comment, 'comment', 1000);

  if(empty($err_msg)){
    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql='UPDATE review SET comment = :comment WHERE user_id = :user_id AND product_id = :product_id';
      $data = array(
        ':comment'=>$comment,
        ':user_id'=>$_SESSION['user_id'],
        ':product_id'=>$p_id
      );
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      if($stmt){
        header("Location:productDetail.php"."?p_id=".$p_id);
      }

    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
    }
  }

}

?>



<?php require('head.php'); ?>
<body>
  <?php require('header.php');?>
  <div class="container-width create-review-container">
    <h2>総合評価</h2>
    <span class="area-msg"><?php if(!empty($err_msg['score'])) echo $err_msg['score']; ?></span>
    <ul class="review-star">
    <?php showStar($p_id, $review_data['score'], 'fa-2x', 1); ?>

    </ul>
    <h2>レビューを追加</h2>
    <form action="" method="post" class="create-reviewform">
      <span class="area-msg"><?php if(!empty($err_msg['comment'])) echo $err_msg['comment']; ?></span>
      <textarea class="review-textarea" name="comment" id="" rows="10"><?php if(!empty($review_data['comment'])) echo sanitize($review_data['comment']); ?></textarea>
      <input type="submit" value="投稿">
    </from>
  </div>
  <?php require('footer.php')?>
</body>
