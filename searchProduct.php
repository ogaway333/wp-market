<?php
require('function.php');
require('searchSystem.php');
debug('セッション変数の中身：'.print_r($_SESSION,true));
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　商品一覧ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// GETデータを格納
// カレントページ
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは１ページ

if(!preg_match("/^[0-9]+$/",$currentPageNum)){
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php"); //トップページへ
}

// 表示件数
$listSpan = 8;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan); //1ページ目なら(1-1)*20 = 0 、 ２ページ目なら(2-1)*20 = 20
$q=(!empty($_GET['q'])) ? $_GET['q'] : '';
debug('現在のページ数:'.$currentPageNum);
debug('検索キーワード:'.$q);


$ProductData = getProductList($currentMinNum, $q);

debug('DBデータ：'.print_r($ProductData,true));




?>



<?php require('head.php'); ?>
<body>
  <?php require('header.php');?>
    <div class="content-container">
    <?php if(!empty($q)){?>
      <h2><?php echo $q.'の検索結果:'.$ProductData['total'].'件' ?></h2>
    <?php }else{ ?>
      <h2>新着テーマ一覧</h2>
    <?php } ?>
    <ul class="content-cover">
    <?php foreach($ProductData['data'] as $key => $val): ?>
      <li class="content-box">
        <a  href="productDetail.php<?php echo '?p_id='.$val['id']; ?>" class="content">
          <p class="eyecatching-image"><img src="<?php echo sanitize($val['pic1']); ?>" alt=""></p>
          <p class="eyecatching-name"><?php echo sanitize($val['name']); ?></p>
        </a>
      </li>
    <?php endforeach; ?>
    </ul>
    <?php pagination($currentPageNum, $ProductData['total_page'], $q); ?>
    </div>
  <?php require('footer.php')?>
</body>
