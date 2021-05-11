<?php


    if(isset($_POST['search_key'])){
        $q=$_POST['search_key'];
        debug('検索開始'.$q);
        header("Location:searchProduct.php"."?q=".$q);
        exit();

    }


?>
