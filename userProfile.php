<?php
require('function.php');
require('searchSystem.php');
debug('セッション変数の中身：'.print_r($_SESSION,true));
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　マイページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
// GETデータを格納
$u_id=(!empty($_GET['u_id'])) ? $_GET['u_id'] : '';
$user_data=get_userdata($u_id);
$product_data=get_Products($u_id);



?>



<?php require('head.php'); ?>
<body>
  <?php require('header.php');?>
  <section class="container-width mypage-container">
              <div class="userinfo-container">
                <div class="myuser_image">
                  <img src=<?php showImg($user_data['user_icon']);?> alt="">
                </div>
                <p class="myusername"><?php if(!empty($user_data['username'])){echo $user_data['username'];}else{echo 'データを取得できませんでした';}  ?></p>
              </div>
              <div class="self-introduction">
                <?php if(!empty($user_data['self_msg'])){echo nl2br($user_data['self_msg']);}?>
              </div>
              <h2>作品を閲覧</h2>
              <ul class="mycontent-cover">
                <?php if(!empty($product_data)){
                  foreach($product_data as $key => $val){ ?>
                    <li class="mycontent-box">
                        <a  href="productDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="mycontent">
                          <div class="float-container">
                            <p class="myeyecatching-image"><img src="<?php showImg($val['pic1']); ?>" alt=""></p>
                            <p class="myeyecatching-name"><?php echo sanitize($val['name']); ?></p>
                          </div>
                        </a>
                    </li>
                  <?php }
                    }?>
              </ul>

  </section>
  <?php require('footer.php')?>
</body>
