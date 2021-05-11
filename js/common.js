

$(function () {
  $('.slider01').slick({
    autoplay: true,
    autoplaySpeed: 1000,
    dots: true,
  });

  // レビュー星追加
  var $star = $('.set-star') || null;
  $star.on('click', function (e) {
    var scoreData = $(this).children('i').data('score') || null;
    console.log(scoreData);
    $(this).children('i').removeClass('far').addClass('fas');
    $(this).prevAll().children('i').removeClass('far').addClass('fas');
    $(this).nextAll().children('i').removeClass('fas').addClass('far');
    $.ajax({
      type: "POST",
      url: "ajaxScore.php",
      data: {
        score: scoreData[0],
        product_id: scoreData[1]

      }
    }).done(function (data) {
      console.log('Ajax Success');
    }).fail(function (msg) {
      console.log('Ajax Error');
    });

  });


  //コンテンツコンテナの高さ調整
  var $container = $('.content-container');
  $container.attr({ 'style': 'min-height:' + window.innerHeight + 'px;' });


  // フッターを最下部に固定
  var $ftr = $('#footer');
  if (window.innerHeight > $ftr.offset().top + $ftr.outerHeight()) {
    $ftr.attr({ 'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;' });
  }

  // 画像ライブプレビュー
  var $dropArea = $('.area-drop');
  var $fileInput = $('.input-file');
  $dropArea.on('dragover', function (e) {
    console.log('dragover');
    e.stopPropagation();
    e.preventDefault();
    $(this).css('opacity', '0.5');
  });
  $dropArea.on('dragleave', function (e) {
    console.log('leave');
    e.stopPropagation();
    e.preventDefault();
    $(this).css('opacity', '1.0');
  });
  $fileInput.on('change', function (e) {
    console.log('change');
    $dropArea.css('opacity', '1.0');
    var file = $(this).prop('files')[0],            // 2. files配列にファイルが入っています
      $img = $(this).siblings('.prev-img'), // 3. jQueryのsiblingsメソッドで兄弟のimgを取得
      fileReader = new FileReader();   // 4. ファイルを読み込むFileReaderオブジェクト

    // 5. 読み込みが完了した際のイベントハンドラ。imgのsrcにデータをセット
    fileReader.onload = function (event) {
      console.log(fileReader.onerror);
      // 読み込んだデータをimgに設定
      $img.attr('src', event.target.result).show();
    };

    // 6. 画像読み込み
    fileReader.readAsDataURL(file);

  });

  //ファイル名更新
  $('.js-upload-file').on('change', function () { //ファイルが選択されたら
    var file = $(this).prop('files')[0]; //ファイルの情報を代入(file.name=ファイル名/file.size=ファイルサイズ/file.type=ファイルタイプ)

    $('.js-upload-filename').text(file.name); //ファイル名を出力
  });
});
