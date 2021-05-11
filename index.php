<?php
require('function.php');
require('searchSystem.php');
debug('セッション変数の中身：'.print_r($_SESSION,true));
//新着テーマを取得
$product_data=get_NewProducts_Limit();

//上位５位のタグを取得
$tag_data=get_tagsList();


?>



<?php require('head.php'); ?>
<body>
    <?php require('header.php');?>
    <div class="top-image">
    <div class="opacity-cover"></div>
    <div class=top-text>
        誰でもWordPressテーマを投稿、<br>
        ダウンロード、<br>
        ニーズの追求が可能
    </div>

    </div>

    <div class="pickup-tag">
        <h2>人気のタグ</h2>
        <ul class="taglist">
        <?php foreach ($tag_data as $key => $val):
           echo '<li><a href="searchProduct.php?q='.$val['tag_name'].'">'.$val['tag_name'].'</a></li>';
         endforeach; ?>
        </ul>
    </div>
    <div class="content-container">
        <h2>新着のテーマ一覧</h2>
        <ul class="content-cover">
            <?php if(!empty($product_data)){
                  foreach($product_data as $key => $val){ ?>
            <li class="content-box">
                <a  href="productDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="content">
                    <p class="eyecatching-image"><img src="<?php showImg($val['pic1']); ?>" alt=""></p>
                    <p class="eyecatching-name"><?php echo sanitize($val['name']); ?></p>
                </a>
            </li>
            <?php }
            }?>
        </ul>
        <button class="more-button" onclick="location.href='searchProduct.php'">more</button>
    </div>
    <?php require('footer.php')?>

</body>
</html>
