<?php
//================================
// ログ
//================================
//ログを取るか
ini_set('log_errors', 'off');
//ログの出力ファイルを指定
ini_set('error_log','php.log');

//================================
// デバッグ
//================================
//デバッグフラグ
$debug_flg = true;
//デバッグログ関数
function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバッグ：'.$str);
  }
}

//================================
// セッション準備・セッション有効期限を延ばす
//================================
//セッションファイルの置き場を変更する（/var/tmp/以下に置くと30日は削除されない）
session_save_path("var/tmp/");
//ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime ', 60*60*24*30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//================================
// 画面表示処理開始ログ吐き出し関数
//================================
function debugLogStart(){
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID：'.session_id());
  debug('セッション変数の中身：'.print_r($_SESSION,true));
  debug('現在日時タイムスタンプ：'.time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug( 'ログイン期限日時タイムスタンプ：'.( $_SESSION['login_date'] + $_SESSION['login_limit'] ) );
  }
}

//================================
// 定数
//================================
//エラーメッセージを定数に設定
define('MSG01','入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03','パスワード（再入力）が合っていません');
define('MSG04','半角英数字のみご利用いただけます');
define('MSG05','8文字以上で入力してください');
define('MSG06','文字数が規定を超えています');
define('MSG07','エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08', 'そのEmailは既に登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '古いパスワードが違います');
define('MSG11', '古いパスワードと同じです');
define('MSG12', '文字で入力してください');
define('MSG13', '正しくありません');
define('MSG14', '有効期限が切れています');
define('MSG15', '半角数字のみご利用いただけます');
define('MSG16', '未評価です');
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '登録しました');

//================================
// グローバル変数
//================================
//エラーメッセージ格納用の配列
$err_msg = array();

//================================
// バリデーション関数
//================================
//バリデーション関数（未入力チェック）
function validRequired($str, $key){
  if($str === ''){
    global $err_msg;
    $err_msg[$key]=MSG01;
  }
}

//バリデーション関数（Email形式チェック）
function validEmail($str, $key){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\?\*\[|\]%'=~^\{\}\/\+!#&\$\._-])*@([a-zA-Z0-9_-])+\.([a-zA-Z0-9\._-]+)+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}

//バリデーション関数（Email重複チェック）
function validEmailDup($email){
  global $err_msg;
  try{
    $dbh=dbConnect();
    $sql='SELECT count(*) FROM users WHERE :email = email AND delete_flg = 0';
    $data = array(':email' => $email);
    $stmt=queryPost($dbh,$sql,$data);
    // クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //array_shift関数は配列の先頭を取り出す関数です。クエリ結果は配列形式で入っているので、array_shiftで1つ目だけ取り出して判定します
    if(!empty(array_shift($result))){
      debug('email重複');
      $err_msg['email'] = MSG08;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//バリデーション関数（同値チェック）
function validMatch($str1, $str2, $key){
  if($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}

//バリデーション関数（半角チェック）
function validHalf($str, $key){
  if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}

//バリデーション関数（最小文字数チェック）
function validMinLen($str, $key, $min = 8){
  if(mb_strlen($str) < $min){
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}
//バリデーション関数（最大文字数チェック）
function validMaxLen($str, $key, $max = 255){
  if(mb_strlen($str) > $max){
    global $err_msg;
    $err_msg[$key] = MSG06;
  }
}

//固定長チェック
function validLength($str, $key, $len = 8){
  if( mb_strlen($str) !== $len ){
    global $err_msg;
    $err_msg[$key] = $len . MSG14;
  }
}

//パスワードチェック
function validPass($str, $key){
  //半角英数字チェック
  validHalf($str, $key);
  //最大文字数チェック
  validMaxLen($str, $key);
  //最小文字数チェック
  validMinLen($str, $key);
}




//================================
// ログイン認証
//================================
function isLogin(){
  // ログインしている場合
  if( !empty($_SESSION['login_date']) ){
    debug('ログイン済みユーザーです。');

    // 現在日時が最終ログイン日時＋有効期限を超えていた場合
    if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
      debug('ログイン有効期限オーバーです。');

      // セッションを削除（ログアウトする）
      session_destroy();
      return false;
    }else{
      debug('ログイン有効期限以内です。');
      return true;
    }

  }else{
    debug('未ログインユーザーです。');
    return false;
  }
}


//================================
// データベース
//================================
//DB接続関数
function dbConnect(){
  //DBへの接続準備
  $dsn = 'mysql:dbname=wp-market;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    //PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成（DBへ接続）
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}

function queryPost($dbh, $sql, $data){
  //クエリー作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行
  if(!$stmt->execute($data)){
    debug('クエリに失敗しました。');
    debug('失敗したSQL：'.print_r($stmt,true));
    $err_msg['common'] = MSG07;
    return 0;
  }
  debug('クエリ成功');
  return $stmt;
}

//================================
// メール送信
//================================
function sendMail($from, $to, $subject, $comment){
    if(!empty($to) && !empty($subject) && !empty($comment)){
        //文字化けしないように設定（お決まりパターン）
        mb_language("Japanese"); //現在使っている言語を設定する
        mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング（機械が分かる言葉へ変換）するかを設定

        //メールを送信（送信結果はtrueかfalseで返ってくる）
        $result = mb_send_mail($to, $subject, $comment, "From: ".$from);
        //送信結果を判定
        if ($result) {
          debug('メールを送信しました。');
        } else {
          debug('【エラー発生】メールの送信に失敗しました。');
        }
    }
}


//================================
// 更新
//================================


function updateTags($tag){
  debug('タグ情報の更新開始');
  $tag_data=get_tags($tag);
  if(!empty($tag)){
    if(empty($tag_data)){
      try{
        $dbh = dbConnect();
        $sql = 'INSERT INTO tags (tag_name, reg_num, create_date) VALUES(:tag_name, 1, :create_date)';
        $data = array(
          'tag_name'=>$tag,
          ':create_date'=>date('Y-m-d H:i:s')
        );
        $stmt=queryPost($dbh, $sql, $data);

        }catch(Exception $e){
          error_log('エラー発生:' . $e->getMessage());
          $err_msg['common'] = MSG07;
        }
    }else{
      try{
        $dbh = dbConnect();
        $sql='UPDATE tags SET reg_num = reg_num + 1 WHERE tag_name = :tag_name';
        $data = array(
          ':tag_name'=>$tag
        );
        $stmt=queryPost($dbh, $sql, $data);

        }catch(Exception $e){
          error_log('エラー発生:' . $e->getMessage());
          $err_msg['common'] = MSG07;
        }
    }
  }
}

//商品データのスコア更新
//indecは元のスコアと更新後のスコアの増減値
//$rater_countは足す評価人数
function updateProductScore($product_id, $user_id, $indec=0){
  debug('商品情報のスコア更新を開始');
  $reviewData=get_review($user_id, $product_id);
  if($indec === 0){
    debug('スコアの増減なし');
    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql='UPDATE product SET overall_score = overall_score + :score, rater_num = rater_num + 1 WHERE id = :product_id';
      $data = array(
        ':score'=>$reviewData['score'],
        ':product_id'=>$product_id
      );
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      if($stmt){
        debug('１段回目の更新成功');
      }

      // SQL文作成
      $sql='UPDATE product SET average_score = overall_score / rater_num WHERE id = :product_id';
      $data = array(
        ':product_id'=>$product_id
      );
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      if($stmt){
        debug('２段回目の更新成功');
      }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
  }else{
    debug('スコアの増減あり'.$indec);
    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql='UPDATE product SET overall_score = overall_score + '.$indec.' WHERE id = :product_id';
      $data = array(
        ':product_id'=>$product_id
      );
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      if($stmt){
        debug('１段回目の更新成功');
      }

      // SQL文作成
      $sql='UPDATE product SET average_score = overall_score / rater_num WHERE id = :product_id';
      $data = array(
        ':product_id'=>$product_id
      );
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      if($stmt){
        debug('２段回目の更新成功');
      }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
  }
}



//================================
// get関数
//================================
//エラーメッセージ表示
function getErrMsg($key){
  global $err_msg;
  if(!empty($err_msg[$key])){
    return $err_msg[$key];
  }
}


//タグ名からタグデータの取得
function get_tags($tag_name){
  try {
    debug('登録タグの取得を開始');
    $dbh=dbConnect();
    $sql="SELECT * FROM tags WHERE tag_name = :tag_name AND delete_flg=0";
    $data=array(
      ':tag_name' => $tag_name
    );
    $stmt=queryPost($dbh,$sql,$data);
    $result=$stmt->fetch(PDO::FETCH_ASSOC);;
    debug('タグの中身：'.print_r($result,true));
    return $result;
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//上位５位のタグリストの取得
function get_tagsList(){
  try {
    debug('登録タグの取得を開始');
    $dbh=dbConnect();
    $sql="SELECT * FROM tags ORDER BY reg_num DESC LIMIT 5";
    $data=array();
    $stmt=queryPost($dbh,$sql,$data);
    $result=$stmt->fetchAll();
    debug('上位５位のタグデータ：'.print_r($result,true));
    return $result;
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//ユーザーidと商品idからレビューデータの取得
function get_review($user_id, $product_id){
  try {
    debug('レビューの取得を開始');
    $dbh=dbConnect();
    $sql="SELECT * FROM review WHERE user_id=:user_id AND product_id=:product_id AND delete_flg=0";
    $data=array(
      ':user_id' => $user_id,
      ':product_id' => $product_id
    );
    $stmt=queryPost($dbh,$sql,$data);
    $result=$stmt->fetch(PDO::FETCH_ASSOC);
    debug('レビューの中身：'.print_r($result,true));
    return $result;
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//商品idからレビューデータの取得
function get_reviewList($product_id){
  try {
    debug('レビューの取得を開始');
    $dbh=dbConnect();
    $sql="SELECT * FROM review AS r, users AS u WHERE r.product_id=:product_id AND r.user_id=u.id AND r.delete_flg=0 AND u.delete_flg=0";
    $data=array(
      ':product_id' => $product_id
    );
    $stmt=queryPost($dbh,$sql,$data);
    $result=$stmt->fetchAll();
    debug('レビューの中身：'.print_r($result,true));
    return $result;
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//ユーザーidから個人情報を取得
function get_userdata($user_id){
  try {
    debug(print_r($user_id,true).'の個人情報の取得を開始');
    $dbh=dbConnect();
    $sql="SELECT * FROM users WHERE id=:id AND delete_flg=0";
    $data=array(':id' => $user_id);
    $htmt=queryPost($dbh,$sql,$data);
    $result=$htmt->fetch(PDO::FETCH_ASSOC);
    debug('個人情報の中身：'.print_r($result,true));
    return $result;
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//ユーザーidと商品idから商品情報取得
function getProduct($u_id, $p_id){
  debug('商品情報を取得します。');
  debug('ユーザーID：'.$u_id);
  debug('商品ID：'.$p_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM product WHERE user_id = :u_id AND id = :p_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを１レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//6件まで商品を表示
function get_NewProducts_Limit(){
  try {
    debug('新規商品情報の取得を開始');
    $dbh=dbConnect();
    $sql="SELECT * FROM product WHERE delete_flg=0 ORDER BY id DESC LIMIT 6";
    $data=array();
    $stmt=queryPost($dbh,$sql,$data);
    $result=$stmt->fetchAll();
    debug('新規商品情報の中身：'.print_r($result,true));
    return $result;
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//全ての新規商品を取得
function get_NewProducts(){
  try {
    debug('新規商品情報の取得を開始');
    $dbh=dbConnect();
    $sql="SELECT * FROM product WHERE delete_flg=0";
    $data=array();
    $stmt=queryPost($dbh,$sql,$data);
    return $stmt;
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//新規商品順に表示
function getProductList($currentMinNum = 1, $q, $span = 8){
  debug('商品情報を取得します。');
  //例外処理
  try {
    $dbh=dbConnect();

    if(!empty($q)){
      $stmt=get_SearchProducts($q);
      $rst['total'] = $stmt->rowCount(); //総レコード数
      $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
      debug('総合'.$rst['total']);
      if(!$stmt){
        return false;
      }

      // ページング用のSQL文作成
      $search='%'.$q.'%';
      $sql='SELECT *  FROM product WHERE name LIKE :search OR comment LIKE :search OR tag1 LIKE :search OR tag2 LIKE :search OR tag3 LIKE :search OR tag4 LIKE :search OR tag5 LIKE :search ORDER BY id DESC LIMIT '.$span.' OFFSET '.$currentMinNum;
      $data=array(':search' => $search);
      debug('SQL：'.$sql);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      if($stmt){
        // クエリ結果のデータを全レコードを格納
        $rst['data'] = $stmt->fetchAll();
        return $rst;
      }else{
        return false;
      }

    }else{
      $stmt=get_NewProducts();
      $rst['total'] = $stmt->rowCount(); //総レコード数
      $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
      debug('総合レコード数'.$rst['total']);
      debug('総合ページ数'.$rst['total_page']);

      if(!$stmt){
        return false;
      }

      // ページング用のSQL文作成
      $sql = 'SELECT * FROM product ORDER BY id DESC LIMIT '.$span.' OFFSET '.$currentMinNum;
      debug('SQL：'.$sql);
      $data=array();
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      if($stmt){
        $rst['data'] = $stmt->fetchAll();
       // クエリ結果のデータを全レコードを格納
        return $rst;
      }else{
        return false;
      }
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//自分のidから全ての商品を引き出す
function get_Products($user_id){
  try {
    debug(print_r($user_id,true).'の商品情報の取得を開始');
    $dbh=dbConnect();
    $sql="SELECT * FROM product WHERE user_id=:id AND delete_flg=0";
    $data=array(':id' => $user_id);
    $stmt=queryPost($dbh,$sql,$data);
    $result=$stmt->fetchAll();
    debug('商品情報の中身：'.print_r($result,true));
    return $result;
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//商品idから一つだけの商品を取り出す
function get_ProductDetail($p_id){
  try {
    debug('商品id:'.print_r($p_id,true).'の商品情報の取得を開始');
    $dbh=dbConnect();
    $sql="SELECT *  FROM product AS p, users AS u WHERE p.id = :id AND p.user_id = u.id AND p.delete_flg=0 AND u.delete_flg=0";
    $data=array(':id' => $p_id);
    $stmt=queryPost($dbh,$sql,$data);
    if($stmt){
      // クエリ結果のデータを１レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//検索
function get_SearchProducts($q){
  try {
    debug(print_r($q,true).'の検索結果の取得を開始');
    $search='%'.$q.'%';
    $dbh=dbConnect();
    $sql="SELECT *  FROM product WHERE name LIKE :search OR comment LIKE :search OR tag1 LIKE :search OR tag2 LIKE :search OR tag3 LIKE :search OR tag4 LIKE :search OR tag5 LIKE :search";
    $data=array(':search' => $search);
    $htmt=queryPost($dbh,$sql,$data);
    return $htmt;
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//データ数を数える
function get_ProductCount($product_data){
  $count=0;
  foreach($product_data as $key => $val){
    $count++;
  }
  return $count;

}

//sessionを１回だけ取得できる
function getSessionFlash($key){
  if(!empty($_SESSION[$key])){
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}

// フォーム入力保持
function getFormData($str, $flg = false){
  if($flg){
    $method = $_GET;
  }else{
    $method = $_POST;
  }
  global $dbFormData;
  global $err_msg;
  // ユーザーデータがある場合
  if(!empty($dbFormData)){
    //フォームのエラーがある場合
    if(!empty($err_msg[$str])){
      //POSTにデータがある場合
      if(isset($method[$str])){
        return sanitize($method[$str]);
      }else{
        //ない場合（基本ありえない）はDBの情報を表示
        return sanitize($dbFormData[$str]);
      }
    }else{
      //POSTにデータがあり、DBの情報と違う場合
      if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
        return sanitize($method[$str]);
      }else{
        return sanitize($dbFormData[$str]);
      }
    }
  }else{
    if(isset($method[$str])){
      return sanitize($method[$str]);
    }
  }
}

//================================
// その他
//================================
// サニタイズ
function sanitize($str){
  return htmlspecialchars($str,ENT_QUOTES);
}

//認証キー生成
function makeRandKey($length = 8) {
    static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i = 0; $i < $length; ++$i) {
        $str .= $chars[mt_rand(0, 61)];
    }
    return $str;
}

// 画像処理
function uploadImg($file, $key){
  debug('画像アップロード処理開始');
  debug('FILE情報：'.print_r($file,true));

  if (isset($file['error']) && is_int($file['error'])) {
    try {
      // バリデーション
      // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
      switch ($file['error']) {
          case UPLOAD_ERR_OK: // OK
              break;
          case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
              throw new RuntimeException('ファイルが選択されていません');
          case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過した場合
          case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
              throw new RuntimeException('ファイルサイズが大きすぎます');
          default: // その他の場合
              throw new RuntimeException('その他のエラーが発生しました');
      }

      // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
          throw new RuntimeException('画像形式が未対応です');
      }

      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
      if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
          throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：'.$path);
      return $path;

    } catch (RuntimeException $e) {

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();

    }
  }
}


// zipファイル処理
function uploadZip($file, $key){
  debug('zipファイルのアップロード処理開始');
  debug('FILE情報：'.print_r($file,true));

  if (isset($file['error']) && is_int($file['error'])) {
    try {
      // バリデーション
      // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
      switch ($file['error']) {
          case UPLOAD_ERR_OK: // OK
              break;
          case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
              throw new RuntimeException('ファイルが選択されていません');
          case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過した場合
          case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
              throw new RuntimeException('ファイルサイズが大きすぎます');
          default: // その他の場合
              throw new RuntimeException('その他のエラーが発生しました');
      }

      $type = mime_content_type($file['tmp_name']);
      if ($type !== 'application/zip') { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
          throw new RuntimeException('ファイル形式が未対応です');
      }

      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'uploads/'.sha1_file($file['tmp_name']).'.zip';
      if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
          throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：'.$path);
      return $path;

    } catch (RuntimeException $e) {

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();

    }
  }
}

//GETパラメータ付与
// $del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array()){
  if(!empty($_GET)){
    $str = '?';
    foreach($_GET as $key => $val){
      if(!in_array($key,$arr_del_key,true)){ //取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}

//ページング
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination( $currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
  // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
  if( $currentPageNum == $totalPageNum && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
  // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
  }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
  }elseif( $currentPageNum == 2 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
  // 現ページが1の場合は左に何も出さない。右に５個出す。
  }elseif( $currentPageNum == 1 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
  // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  }elseif($totalPageNum < $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  // それ以外は左に２個出す。
  }else{
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }

  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
      if($currentPageNum != 1){
        echo '<li class="list-item"><a href="?p=1&q='.$link.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="list-item ';
        if($currentPageNum == $i ){ echo 'active'; }
        echo '"><a href="?p='.$i.'&q='.$link.'">'.$i.'</a></li>';
      }
      if($currentPageNum != $maxPageNum && $maxPageNum > 1){
        echo '<li class="list-item"><a href="?p='.$maxPageNum.'&q='.$link.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
}

//レビューの星の表示
function showStar($p_id, $score, $fa_size, $set_flg=0){
  if($set_flg === 1) {
    $set = "set-star";
  }else{
    $set = "";
  }
  if(empty($score) || $score === 0){
    echo '<li  class="'.$set.'"><i data-score="[1 , '.$p_id.']"  class="far fa-star '.$fa_size.'"></i></li>';
    echo '<li  class="'.$set.'"><i data-score="[2 , '.$p_id.']"  class="far fa-star '.$fa_size.'"></i></li>';
    echo '<li  class="'.$set.'"><i data-score="[3 , '.$p_id.']"  class="far fa-star '.$fa_size.'"></i></li>';
    echo '<li  class="'.$set.'"><i data-score="[4 , '.$p_id.']"  class="far fa-star '.$fa_size.'"></i></li>';
    echo '<li  class="'.$set.'"><i data-score="[5 , '.$p_id.']"  class="far fa-star '.$fa_size.'"></i></li>';
  }

  switch ($score) {
    case 1:
        echo '<li  class="'.$set.'"><i data-score="[1 , '.$p_id.']"  class="fas fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[2 , '.$p_id.']"  class="far fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[3 , '.$p_id.']"  class="far fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[4 , '.$p_id.']"  class="far fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[5 , '.$p_id.']"  class="far fa-star '.$fa_size.'"></i></li>';
        break;
    case 2:
        echo '<li  class="'.$set.'"><i data-score="[1 , '.$p_id.']"  class="fas fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[2 , '.$p_id.']"  class="fas fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[3 , '.$p_id.']"  class="far fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[4 , '.$p_id.']"  class="far fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[5 , '.$p_id.']"  class="far fa-star '.$fa_size.'"></i></li>';
        break;
    case 3:
        echo '<li  class="'.$set.'"><i data-score="[1 , '.$p_id.']"  class="fas fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[2 , '.$p_id.']"  class="fas fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[3 , '.$p_id.']"  class="fas fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[4 , '.$p_id.']"  class="far fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[5 , '.$p_id.']"  class="far fa-star '.$fa_size.'"></i></li>';
        break;
    case 4:
        echo '<li  class="'.$set.'"><i data-score="[1 , '.$p_id.']"  class="fas fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[2 , '.$p_id.']"  class="fas fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[3 , '.$p_id.']"  class="fas fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[4 , '.$p_id.']"  class="fas fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[5 , '.$p_id.']"  class="far fa-star '.$fa_size.'"></i></li>';
        break;
    case 5:
        echo '<li  class="'.$set.'"><i data-score="[1 , '.$p_id.']"  class="fas fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[2 , '.$p_id.']"  class="fas fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[3 , '.$p_id.']"  class="fas fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[4 , '.$p_id.']"  class="fas fa-star '.$fa_size.'"></i></li>';
        echo '<li  class="'.$set.'"><i data-score="[5 , '.$p_id.']"  class="fas fa-star '.$fa_size.'"></i></li>';
        break;
  }
}

function showImg($path){
  if(!empty($path)){
    echo sanitize($path);
  }else{
    echo 'img/no-image.png';
  }

}





?>
