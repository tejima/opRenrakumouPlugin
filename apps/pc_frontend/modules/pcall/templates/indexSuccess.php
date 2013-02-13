<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta charset="UTF-8">
  <link href="opChatTaskPlugin/css/bootstrap.css" rel="stylesheet">
  <link href="opChatTaskPlugin/css/custom.css" rel="stylesheet">

  <title></title>
  <!-- libs -->
  <script src="opChatTaskPlugin/js/jquery.js"></script>
  <script src="opChatTaskPlugin/js/jquery.tmpl.js"></script>
  <script src="opChatTaskPlugin/js/bootstrap.js"></script>
  <script src="opChatTaskPlugin/js/shortcut.js"></script>

  <!-- Le fav and touch icons -->
  <link rel="shortcut icon" href="assets/ico/favicon.ico">
  <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
  <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
  <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
  <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">
  <style></style>
</head>
<body>
  <div class="navbar navbar-fixed-top navbar-inverse">
    <div class="navbar-inner">
      <div class="container">
        <a class="brand" href="/pcall/index">緊急連絡サービス pCall</a>
        <ul class="nav pull-right">
          <li>
            <a href="/">SNSへ戻る</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="hero-unit" style="margin-top: 80px; padding-top: 30px; padding-bottom: 30px;">
      <div>
        <h2>緊急連絡サービス pCall</h2>
        <p>
          <a href="#voice_demo" role="button" data-toggle="modal" class="btn btn-warning">音声発信DEMO</a>
        </p>
      </div>
    </div>

    <!-- Modal -->
    <div id="voice_demo" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">DEMO発信</h3>
      </div>
      <div class="modal-body">

        <form>
          <fieldset>
            <label>発信先電話番号</label>
            <input id="demo-tel" class="input-block-level tooltip-target" type="text" placeholder="09011112222" rel="tooltip" title="電話番号をハイフン無しで入力します" data-placement="right">
            <label>連絡本文</label>
            <textarea id="demo-body" data-placement="right" title="200文字以内で、伝えたい要件を記入してください。ここで書かれた文章は2回繰り返して発音されます。" class="input-block-level tooltip-target" rows="5" placeholder="舟渡小学校6年1組の連絡網です。大雪のため2月9日、舟渡小学校6年1組はお休みになりました。9日はお休みですが、翌10日は通常通り開校します。保護者のみなさま、ご対応、よろしくお願いたします。"></textarea>
          </fieldset>
        </form>

        <p>
          赤いボタンを押すと、実際に電話がかかります。
          <br>１．番号に間違いはありませんか</p>
        <p>確認の上、赤い発信ボタンを押してください。キャンセルの場合は白いキャンセルボタンを押してください。</p>

      </div>
      <div class="modal-footer">
        <button class="btn pull-left" data-dismiss="modal" aria-hidden="true">キャンセル：編集をやり直す</button>
        <button id="demo-call-button" class="btn btn-danger">発信する</button>
        <div id="post-progress" class="hide">
          <p style="text-align: center; margin-top: 40px;">進行状況</p>
          <div class="progress progress-striped active">
            <div class="bar" style="width: 40%;"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="span5" style="
  background-color: #efefef;
  -webkit-border-radius: 3px;
     -moz-border-radius: 3px;
          border-radius: 3px;
  min-height: 40px;
  line-height: 40px;">
        <h3 style="padding-left: 20px;">発信コントロール</h3>
        <div style="padding: 20px;">
          <form>
            <fieldset>
              <label>件名</label>
              <input class="input-block-level tooltip-target" type="text" placeholder="◯月◯日◯◯連絡" rel="tooltip" title="後でわかりやすい日付を入力します。「◯月◯日 6年3組 大雪休みのお知らせ」などと、月日、クラス、内容を簡単に記載してください。" data-placement="right">
              <label>連絡本文</label>
              <textarea data-placement="right" title="200文字以内で、伝えたい要件を記入してください。ここで書かれた文章は2回繰り返して発音されます。" class="input-block-level tooltip-target" rows="5" placeholder="舟渡小学校6年1組の連絡網です。大雪のため2月9日、舟渡小学校6年1組はお休みになりました。9日はお休みですが、翌10日は通常通り開校します。保護者のみなさま、ご対応、よろしくお願いたします。"></textarea>

              <label>連絡先選択</label>
              <ul class="nav nav-pills" id="myTab">
                <li class="active">
                  <a href="#group" data-toggle="tab" class="tooltip-target" data-placement="right" title="事前に作ったコミュニティに対して送付します" rel="tooltip">グループ連絡モード</a>
                </li>
                <li>
                  <a href="#direct" data-toggle="tab">ダイレクト電話モード</a>
                </li>
              </ul>

              <div class="tab-content">
                <div class="tab-pane active" id="group">
                  <h4>6年1組グループ</h4>
                  <table class="table table-hover table-condensed" style="background: #fff;">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>名前</th>
                        <th>電話連絡</th>
                        <th>メール連絡</th>
                      </tr>
                    </thead>
                    <tbody id="list-target"></tbody>
                  </table>

                </div>
                <div class="tab-pane" id="direct">

                  <textarea class="input-block-level" rows="20" placeholder="田中 08040600334
                                                                山田 08040600334
                                                                大島 08040600334"></textarea>
                  <span class="help-block">名字[半角スペース]電話番号[改行]の形式で記入してください。※ハイフン無し</span>

                </div>
              </div>

              <!-- Button to trigger modal -->
              <a href="#myModal" role="button" class="btn btn-block" data-toggle="modal">テスト送信</a>
              <a href="#myModal" role="button" class="btn btn-danger btn-block" data-toggle="modal">* 本送信 *</a>
            </fieldset>
          </form>
        </div>
        <div></div>
      </div>
      <div class="span7">
        <div>
          <h3>送信状況確認</h3>
          <div class="accordion" id="accordion2"></div>
        </div>
      </div>
    </div>
    <hr>
    <div>© Tejimaya, Inc. 2002〜</div>
  </div>

  <!-- Modal -->
  <div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h3 id="myModalLabel">発信の最終確認</h3>
    </div>
    <div class="modal-body">
      <p>
        赤いボタンを押すと、実際に電話がかかります。
        <br>
        １．自分の電話にテスト確認しましたか？
        <br>２．文章に間違いはありませんいか？</p>
      <p>確認の上、赤い発信ボタンを押してください。キャンセルの場合は白いキャンセルボタンを押してください。</p>

    </div>
    <div class="modal-footer">
      <button class="btn pull-left" data-dismiss="modal" aria-hidden="true">キャンセル：編集をやり直す</button>
      <button id="call-button" class="btn btn-danger">発信する</button>
      <div id="post-progress" class="hide">
        <p style="text-align: center; margin-top: 40px;">進行状況</p>
        <div class="progress progress-striped active">
          <div class="bar" style="width: 40%;"></div>
        </div>
      </div>
    </div>
  </div>

  <style></style>
  <script id="tmpl_list_target" type="text/x-jquery-tmpl">
                        <tr>
                        <td>${id}</td>
                        <td>${nickname}</td>
                        <td>${telstat}</td>
                        <td>${mailstat}</td>
                      </tr>
  </script>
  <script id="tmpl_info" type="text/x-jquery-tmpl">
<tr class="${status}">
                        <td>${id}</td>
                        <td>${nickname}</td>
                        <td>${telstat}</td>
                        <td>${mailstat}</td>
                      </tr>
</script>
  <script id="tmpl_accordion" type="text/x-jquery-tmpl">
            <div class="accordion-group">
              <div class="accordion-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapse-${index}">
                  ${title}
                  <span class="muted pull-right">${date}</span>
                </a>
              </div>
              <div id="collapse-${index}" class="accordion-body collapse">
                <div class="accordion-inner">
                  <p>${body}</p>

                  <table class="table table-hover  table-condensed">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>名前</th>
                        <th>電話連絡状況</th>
                        <th>メール連絡状況</th>
                      </tr>
                    </thead>
                    <tbody id="list-${index}"></tbody>
                  </table>
                </div>
              </div>
            </div>

</script>

  <script>$('.tooltip-target').tooltip();
  $("#call-button").click(function(){
    $("#post-progress").show();
  });

  $.ajax({
    type: "GET",
    url: "/api.php/call/search.json",
    data: {id: "0"},
    async: false,
    cache: false,
    dataType: "json",
    success: function(data){
      console.log("loaded");
      console.log(data);
      $("#tmpl_accordion").tmpl(data).appendTo('#accordion2');
    }
  });

  $.get("/api.php/call/search.json",{id: "4"},function(data){
    console.log("loaded-4");
    console.log(data);
    $("#tmpl_list_target").tmpl(data).appendTo('#list-target');
  },"json");

  $.get("/api.php/call/search.json",{id: "1"},function(data){
    console.log("loaded-1");
    console.log(data);
    $("#tmpl_info").tmpl(data).appendTo('#list-1');
  },"json");

  $.get("/api.php/call/search.json",{id: "2"},function(data){
    console.log("loaded-2");
    console.log(data);
    $("#tmpl_info").tmpl(data).appendTo('#list-2');
  },"json");

  $("#demo-call-button").click(function(){
    $.ajax({
    type: "POST",
    url: "/api.php/call/demo.json",
    data: { tel: $("#demo-tel").val(),
            body: $("#demo-body").val()},
    async: false,
    cache: false,
    dataType: "json",
    success: function(data){
      console.log("loaded");
      console.log(data);
      }
    });
  });
  </script>

</body>
</html>