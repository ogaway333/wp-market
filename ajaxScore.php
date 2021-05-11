<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　Ajax　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
if(isset($_POST['score']) && isset($_POST['product_id']) && isset($_SESSION['user_id']) && isLogin()){
  $score=$_POST['score'];
  $product_id=$_POST['product_id'];
  $user_id=$_SESSION['user_id'];
  $review_data=get_review($user_id, $product_id);

  if(empty($review_data)){
    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'INSERT INTO review (user_id,product_id,score,create_date) VALUES(:user_id,:product_id,:score,:create_date)';
      $data = array(
        ':user_id'=>$user_id,
        ':product_id'=>$product_id,
        ':score'=>$score,
        ':create_date'=>date('Y-m-d H:i:s')
      );
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      updateProductScore($product_id, $user_id);

    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
    }
  }else{
    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql='UPDATE review SET score = :score WHERE user_id = :user_id AND product_id = :product_id';
      $data = array(
        ':user_id'=>$user_id,
        ':product_id'=>$product_id,
        ':score'=>$score
      );
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      $indec=$score-$review_data['score'];

      updateProductScore($product_id, $user_id, $indec);


    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
    }
  }
}



?>
