<header>
        <p class="header-logo">
            <a href="index.php">
                <img src="img/logo.png" alt="wp-market">
            </a>
        </p>
        <nav>
            <div class="header-search-container">
                <form action="" method='post'>
                    <input type="text" placeholder="テーマを検索" name='search_key'>
                </form>
            </div>
            <ul class="header-subnav">
              <?php
              if(empty($_SESSION['user_id'])){
              ?>
                <li>
                    <a href="login.php">ログイン</a>
                </li>
                <li>
                    <a href="signup.php">新規登録</a>
                </li>
                <?php
              }else{
                ?>
                <li>
                    <a href="mypage.php">マイページ</a>
                </li>
                <li>
                    <a href="logout.php">ログアウト</a>
                </li>
                <?php
              }
                ?>
            </ul>
        </nav>
</header>
