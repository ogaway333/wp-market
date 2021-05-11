<?php
require('function.php');
require('searchSystem.php');
debug('セッション変数の中身：'.print_r($_SESSION,true));
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　マイページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
//ログイン認証
require('auth.php');
$user_id=$_SESSION['user_id'];
$user_data=get_userdata($user_id);
$product_data=get_Products($user_id);



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
              <a class="registprofile-link" href="edit-profile.php">プロフィールを編集する</a>
              <a class="registprofile-link" href="passEdit.php">パスワードの変更</a>
              <div class="self-introduction">
                <?php if(!empty($user_data['self_msg'])){echo nl2br($user_data['self_msg']);}?>
              </div>
              <h2>作品を編集<a class="registproduct-link" href="registproduct.php">テーマを登録する</a></h2>
              <ul class="mycontent-cover">
                <?php if(!empty($product_data)){
                  foreach($product_data as $key => $val){ ?>
                    <li class="mycontent-box">
                        <a  href="registProduct.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="mycontent">
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
