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
        <a class="brand" href="#">緊急連絡サービス pCall</a>
        <ul class="nav pull-right">
          <li>
            <a href="#">SNSへ戻る</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="hero-unit" style="margin-top: 80px; padding-top: 30px; padding-bottom: 30px;">
      <div>
        <h2>緊急連絡サービス pCall</h2>
        <p>本サービスは、ネット画面から、文章を作成し、音声合成、一斉に方、、、緊急連絡を電話発信するシステムです。</p>
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
              <textarea data-placement="right" title="200文字以内で、伝えたい要件を記入してください。ここで書かれた文章は2回繰り返して発音されます。" class="input-block-level tooltip-target" rows="8" placeholder="舟渡小学校6年1組の連絡網です。大雪のため2月9日、舟渡小学校6年1組はお休みになりました。9日はお休みですが、翌10日は通常通り開校します。保護者のみなさま、ご対応、よろしくお願いたします。"></textarea>

              <label>連絡先選択</label>
              <ul class="nav nav-pills" id="myTab">
                <li class="active">
                  <a href="#group" data-toggle="tab">グループ連絡モード</a>
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
                        <th>連絡方法</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>1</td>
                        <td>田中</td>
                        <td>電話</td>
                      </tr>
                      <tr>
                        <td>2</td>
                        <td>山田</td>
                        <td>メール</td>
                      </tr>
                      <tr>
                        <td>3</td>
                        <td>山本</td>
                        <td>電話</td>
                      </tr>
                      <tr>
                        <td>3</td>
                        <td>山本</td>
                        <td>電話</td>
                      </tr>
                      <tr>
                        <td>4</td>
                        <td>山本</td>
                        <td>電話</td>
                      </tr>
                      <tr>
                        <td>5</td>
                        <td>山本</td>
                        <td>メール</td>
                      </tr>
                      <tr>
                        <td>6</td>
                        <td>山本</td>
                        <td>電話</td>
                      </tr>
                      <tr>
                        <td>7</td>
                        <td>山本</td>
                        <td>電話</td>
                      </tr>
                      <tr>
                        <td>8</td>
                        <td>山本</td>
                        <td>電話</td>
                      </tr>
                      <tr>
                        <td>9</td>
                        <td>山本</td>
                        <td>電話</td>
                      </tr>
                    </tbody>
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
          <div class="accordion" id="accordion2">
            <div class="accordion-group">
              <div class="accordion-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">2月9日 大雪休校連絡<span class="muted pull-right">2013/02/06</span></a>
              </div>
              <div id="collapseOne" class="accordion-body collapse in">
                <div class="accordion-inner">
                  <p>
                    舟渡小学校6年1組の連絡網です。大雪のため2月9日、舟渡小学校6年1組はお休みになりました。9日はお休みですが、翌10日は通常通り開校します。保護者のみなさま、ご対応、よろしくお願いたします。
                  </p>

                  <table class="table table-hover table-condensed">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>名前</th>
                        <th>連絡方法</th>
                        <th>状況</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr class="success">
                        <td>1</td>
                        <td>田中</td>
                        <td>電話</td>
                        <td>受信完了 了解済み</td>
                      </tr>
                      <tr class="success">
                        <td>2</td>
                        <td>山田</td>
                        <td>メール</td>
                        <td>受信完了 了解済み</td>
                      </tr>
                      <tr class="error">
                        <td>3</td>
                        <td>山本</td>
                        <td>電話</td>
                        <td>不通3回 ストップ</td>
                      </tr>
                      <tr>
                        <td>3</td>
                        <td>山本</td>
                        <td>電話</td>
                        <td>連絡前</td>
                      </tr>
                      <tr class="success">
                        <td>4</td>
                        <td>山本</td>
                        <td>電話</td>
                        <td>受信完了 了解反応なし</td>
                      </tr>
                      <tr class="error">
                        <td>5</td>
                        <td>山本</td>
                        <td>メール</td>
                        <td>メール不達 メールアドレス違い</td>
                      </tr>
                      <tr class="error">
                        <td>6</td>
                        <td>山本</td>
                        <td>電話</td>
                        <td>不通3回 ストップ</td>
                      </tr>
                      <tr>
                        <td>7</td>
                        <td>山本</td>
                        <td>電話</td>
                        <td>連絡前</td>
                      </tr>
                      <tr>
                        <td>8</td>
                        <td>山本</td>
                        <td>電話</td>
                        <td>連絡前</td>
                      </tr>
                      <tr>
                        <td>9</td>
                        <td>山本</td>
                        <td>電話</td>
                        <td>連絡前</td>
                      </tr>

                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="accordion-group">
              <div class="accordion-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">8月9日 台風休校連絡<span class="muted pull-right">2012/08/08</span></a>
              </div>
              <div id="collapseTwo" class="accordion-body collapse">
                <div class="accordion-inner">
                  <p>
                    舟渡小学校6年1組の連絡網です。大雪のため2月9日、舟渡小学校6年1組はお休みになりました。9日はお休みですが、翌10日は通常通り開校します。保護者のみなさま、ご対応、よろしくお願いたします。
                  </p>

                  <table class="table table-hover  table-condensed">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>名前</th>
                        <th>連絡方法</th>
                        <th>状況</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr class="success">
                        <td>1</td>
                        <td>田中</td>
                        <td>電話</td>
                        <td>受信完了 了解済み</td>
                      </tr>
                      <tr class="success">
                        <td>2</td>
                        <td>山田</td>
                        <td>メール</td>
                        <td>受信完了 了解済み</td>
                      </tr>
                      <tr class="error">
                        <td>3</td>
                        <td>山本</td>
                        <td>電話</td>
                        <td>不通3回 ストップ</td>
                      </tr>
                      <tr>
                        <td>3</td>
                        <td>山本</td>
                        <td>電話</td>
                        <td>連絡前</td>
                      </tr>
                      <tr class="success">
                        <td>4</td>
                        <td>山本</td>
                        <td>電話</td>
                        <td>受信完了 了解反応なし</td>
                      </tr>
                      <tr class="error">
                        <td>5</td>
                        <td>山本</td>
                        <td>メール</td>
                        <td>メール不達 メールアドレス違い</td>
                      </tr>
                      <tr class="error">
                        <td>6</td>
                        <td>山本</td>
                        <td>電話</td>
                        <td>不通3回 ストップ</td>
                      </tr>
                      <tr>
                        <td>7</td>
                        <td>山本</td>
                        <td>電話</td>
                        <td>連絡前</td>
                      </tr>
                      <tr>
                        <td>8</td>
                        <td>山本</td>
                        <td>電話</td>
                        <td>連絡前</td>
                      </tr>
                      <tr>
                        <td>9</td>
                        <td>山本</td>
                        <td>電話</td>
                        <td>連絡前</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
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
      <p>確認画面、本送信時は特に入念にアラートする。テストしましたか？文章に間違いはありませんいか？など。</p>
      <p>確認画面としての役割、本文、送信リストを表示する</p>

    </div>
    <div class="modal-footer">
      <button class="btn pull-left" data-dismiss="modal" aria-hidden="true">キャンセル：編集をやり直す</button>
      <button id="call-button" class="btn btn-danger">この条件で、(テスト)発信する</button>
      <div id="post-progress" class="hide">
        <p style="text-align: center; margin-top: 40px;">進行状況</p>
        <div class="progress progress-striped active">
          <div class="bar" style="width: 40%;"></div>
        </div>
      </div>
    </div>
  </div>

  <style></style>
  <script>$('.tooltip-target').tooltip();
  $("#call-button").click(function(){
    $("#post-progress").show();
  });
  </script>

</body>
</html>