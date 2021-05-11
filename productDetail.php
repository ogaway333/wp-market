<?php
require('function.php');
require('searchSystem.php');
debug('セッション変数の中身：'.print_r($_SESSION,true));

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　商品詳細表示ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
// GETデータを格納
$p_id=(!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
$product_data=get_ProductDetail($p_id);
$review_data=get_reviewList($p_id);

// パラメータ改ざんチェック
//================================
// GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい商品データが取れないのでマイページへ遷移させる
if(!empty($p_id) && empty($product_data)){
  debug('GETパラメータの商品IDが違います。ホームページへ遷移します。');
  header("Location:index.php"); //ホームページへ
}

?>



<?php require('head.php'); ?>
<body>
    <?php require('header.php');?>
    <h1 class="product_title"><?php if(!empty($product_data['name'])){echo sanitize($product_data['name']);}else{echo "データを取得できませんでした";} ?></h1>
    <div class="product_container">
      <ul class="slider01">
        <li><img alt="画像1" src="<?php showImg($product_data['pic1']); ?>" /></li>
        <li><img alt="画像2" src="<?php showImg($product_data['pic2']); ?>" /></li>
        <li><img alt="画像3" src="<?php showImg($product_data['pic3']); ?>" /></li>
      </ul>
      <div>
        <div class="selluser_container">
          <a href="userProfile.php?u_id=<?php echo sanitize($product_data['user_id']); ?>" class="user_image">
            <img src="<?php showImg($product_data['user_icon']); ?>" alt="">
          </a>
          <p class="username"><?php if(!empty($product_data['username'])){echo sanitize($product_data['username']);}else{echo "データを取得できませんでした";} ?></p>
        </div>
        <div class="link_container">
          <button class="download-button" onclick="location.href='<?php if(!empty($product_data['file_name'])) echo sanitize($product_data['file_name']); ?>'" download="unko.zip">テーマをダウンロード</button>
          <button class="demo-button" onclick="location.href='<?php if(!empty($product_data['demo_link'])) echo sanitize($product_data['demo_link']); ?>'">Demoを見る</button>
        </div>
        <h2 class="product_h2">タグ</h2>
        <ul class="product-tag">
        <?php if(!empty($product_data['tag1'])):?>
          <li><a href="searchProduct.php?q=<?php echo sanitize($product_data['tag1']); ?>"><?php if(!empty($product_data['tag1'])) echo sanitize($product_data['tag1']); ?></a></li>
        <?php endif; ?>
        <?php if(!empty($product_data['tag2'])):?>
          <li><a href="searchProduct.php?q=<?php echo sanitize($product_data['tag2']); ?>"><?php if(!empty($product_data['tag2'])) echo sanitize($product_data['tag2']); ?></a></li>
        <?php endif; ?>
        <?php if(!empty($product_data['tag3'])):?>
          <li><a href="searchProduct.php?q=<?php echo sanitize($product_data['tag3']); ?>"><?php if(!empty($product_data['tag3'])) echo sanitize($product_data['tag3']); ?></a></li>
        <?php endif; ?>
        <?php if(!empty($product_data['tag4'])):?>
          <li><a href="searchProduct.php?q=<?php echo sanitize($product_data['tag4']); ?>"><?php if(!empty($product_data['tag4'])) echo sanitize($product_data['tag4']); ?></a></li>
        <?php endif; ?>
        <?php if(!empty($product_data['tag5'])):?>
          <li><a href="searchProduct.php?q=<?php echo sanitize($product_data['tag5']); ?>"><?php if(!empty($product_data['tag5'])) echo sanitize($product_data['tag5']); ?></a></li>
        <?php endif; ?>
        </ul>
        <h2 class="product_h2">商品説明</h2>
        <div class="exp_text">
        <?php if(!empty($product_data['comment'])) echo nl2br(sanitize($product_data['comment'])); ?>
        </div>
        <h2 class="product_h2">カスタマーレビュー</h2>
        <div class="customer-container">
          <div class="average-evaluation">
            <p><?php echo $product_data['average_score'].'.0';  ?></p>
            <ul class="average-star">
              <?php showStar($p_id, $product_data['average_score'], 'fa-2x'); ?>
            </ul>
            <button class="review-button" onclick="location.href='create-review.php<?php echo '?p_id='.$p_id; ?>'">レビューを書く</button>
          </div>
          <div class="review-container">
          <?php if(!empty($review_data)): ?>
            <?php foreach($review_data as $key => $val): ?>
            <div class="review-box">
              <div class="userinfo-container">
                <div class="user_image">
                  <img src="<?php showImg($val['user_icon']); ?>" alt="">
                </div>
                <p class="username"><?php echo sanitize($val['username']); ?></p>
              </div>
              <ul class="review-star">
              <?php showStar($p_id, $val['score'], ''); ?>
              </ul>
              <p class="review-date"><?php echo sanitize($val['create_date']); ?></p>
              <div class="review_text"><?php echo nl2br(sanitize($val['comment'])); ?></div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

    <?php require('footer.php')?>

</body>
</html>
