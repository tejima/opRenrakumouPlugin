<!DOCTYPE html>
<html lang="ja">
<head>
<?php include_http_metas() ?>
<?php include_metas() ?>
<?php include_title() ?>

<?php //todo: スタイルシートの読み込み ?>
<?php //use_stylesheet('/opRenrakumouPlugin/css/bootstrap.css')?>
<?php //use_stylesheet('/opRenrakumouPlugin/css/custom.css')?>
<?php //include_stylesheets() ?>
<link rel="stylesheet" type="text/css" href="/opRenrakumouPlugin/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="/opRenrakumouPlugin/css/custom.css">
<?php if (opConfig::get('enable_jsonapi') && opToolkit::isSecurePage()): ?>
<?php
use_helper('Javascript');
$jsonData = array(
  'apiKey' => $sf_user->getMemberApiKey(),
  'apiBase' => app_url_for('api', 'homepage'),
);
$json = defined('JSON_PRETTY_PRINT') ? json_encode($jsonData, JSON_PRETTY_PRINT) : json_encode($jsonData);
echo javascript_tag('
var openpne = '.$json.';
');
?>
<?php endif ?>
<?php //todo: javascriptの読み込み ?>
<?php //include_javascripts() ?>
<script src="/opRenrakumouPlugin/js/jquery.js"></script>
<script src="/opRenrakumouPlugin/js/jquery.tmpl.js"></script>
<script src="/opRenrakumouPlugin/js/bootstrap.js"></script>
<script src="/opRenrakumouPlugin/js/shortcut.js"></script>
<?php echo $op_config->get('pc_html_head') ?>
<?php include_partial('icons') ?>
</head>
<body>
<?php include_partial('pcallheader') ?>
  <div class="container">
    <div class="row">
      <div class="span5">
        <h3>発信コントロール</h3>
        <div>
          <a href="#testcall-modal" role="button" class="btn btn-block" data-toggle="modal">自分宛にテスト発信</a>
        </div>
        <div>
          <form>
            <fieldset>
              <label>連絡先</label>              
              <textarea id="direct-target-text" data-toggle="tab" class="tooltip-target input-block-level" data-placement="right" title="名字[半角スペース]電話番号[半角スペース]メールアドレス[改行]の形式で記入してください。エクセルで表を作成してから貼り付けるのをおすすめします。※電話番号はハイフン無し" rel="tooltip" rows="15"
               placeholder="山田 09012345678 yamada@example.com             田中 08012345678 tanaka@gmail.com                    佐藤 05012345678 sato@example.com"></textarea>
              <label>件名</label>
              <input id="call-title" class="input-block-level tooltip-target" type="text" placeholder="○月×日大雪休校連絡" rel="tooltip" title="後でわかりやすい日付を入力します。「◯月◯日 6年3組 大雪休みのお知らせ」などと、月日、クラス、内容を簡単に記載してください。" data-placement="right">
              <label>本文</label>
              <textarea id="call-body" data-placement="right" title="200文字以内で、伝えたい要件を記入してください。ここで書かれた文章は2回繰り返して発音されます。" class="input-block-level tooltip-target" rows="5" placeholder="○×小学校○年×組の連絡網です。大雪のため○月×日、○×小学校○年×組はお休みになりました。×日はお休みですが、翌△日は通常通り開校します。保護者のみなさま、ご対応、よろしくお願いたします。"></textarea>


              <!-- Button to trigger modal -->
              <a href="#docall-modal" role="button" class="btn btn-danger btn-block" data-toggle="modal">電話・メール発信</a>
              <a href="#docall-modal" role="button" class="btn btn-warning btn-block" data-toggle="modal">メール発信</a>
            </fieldset>
          </form>
        </div>
        <div></div>
      </div>
      <div class="span7">
        <div>
          <h3>送信状況確認 <a id="update-status" class="btn pull-right" href="#">更新</a> </h3>
          <div class="accordion" id="accordion2"></div>
        </div>
      </div>
    </div>
<?php include_partial('pcallfooter') ?>
  </div>
<!-- dialogs -->
  <div id="testcall-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h3>自分にテスト発信</h3>
    </div>
    <div class="modal-body">
      <form>
        <fieldset>
          <p>電話連絡：<span id="testcall-target-telnum">XX</span>人 メール連絡：<span id="testcall-target-mailnum">XX</span>人</p>

          <label>件名</label>
          <input id="testcall-title" class="input-block-level" type="text" disabled>

          <label>連絡本文</label>
          <textarea id="testcall-body" data-placement="right" title="200文字以内で、伝えたい要件を記入してください。ここで書かれた文章は2回繰り返して発音されます。" class="input-block-level tooltip-target" rows="5" disabled></textarea>

          <label>テスト発信先電話番号</label>
          <input id="testcall-tel" class="input-block-level tooltip-target" type="text" placeholder="09011112222" rel="tooltip" title="電話番号をハイフン無しで入力します" data-placement="top">
          <label>テスト発信先メールアドレス</label>
          <input id="testcall-mail" class="input-block-level tooltip-target" type="text" placeholder="test@academy.jp" rel="tooltip" title="メールアドレスを記入します" data-placement="top">

        </fieldset>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn pull-left" data-dismiss="modal" aria-hidden="true">キャンセル：編集をやり直す</button>
      <button id="testcall-button" class="btn btn-danger" type="button" data-loading-text="発信手続き中....">発信：実際に電話（メール）を発信する</button>
    </div>
  </div>

  <div id="docall-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h3>発信の最終確認</h3>
    </div>
    <div class="modal-body">
      <form>
        <fieldset>
          <p>電話連絡：<span id="docall-target-telnum">XX</span>人 メール連絡：<span id="docall-target-mailnum">XX</span>人</p>

          <label>件名</label>
          <input id="docall-title" class="input-block-level" type="text" disabled>

          <label>連絡本文</label>
          <textarea id="docall-body" data-placement="right" title="200文字以内で、伝えたい要件を記入してください。ここで書かれた文章は2回繰り返して発音されます。" class="input-block-level tooltip-target" rows="5" disabled></textarea>
        </fieldset>
      </form>
      <div class="alert alert-error">
        <strong>注意！</strong>発信ボタン押すと、実際に電話（メール）が発信されます。必ずテスト発信で確認をしてから。宛先、本文は十分確認の上、実行してください。
      </div>

    </div>
    <div class="modal-footer">
      <button class="btn pull-left" data-dismiss="modal" aria-hidden="true">キャンセル：編集をやり直す</button>
      <button id="docall-button" class="btn btn-danger">発信：実際に電話（メール）を発信する</button>
    </div>
  </div>
<!-- templates -->
  <script id="tmpl_list_target" type="text/x-jquery-tmpl">
                        <tr>
                        <td>${nickname}</td>
                        <td>${tel}</td>
                        <td>${mail}</td>
                      </tr>
  </script>
  <script id="tmpl_accordion" type="text/x-jquery-tmpl">
    {{each value}}
      <div class="accordion-group">
        <div class="accordion-heading">
          <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapse-${$index}">
            ${title}
            <span class="muted pull-right">${date}</span>
          </a>
        </div>
        <div id="collapse-${$index}" class="accordion-body collapse">
          <div class="accordion-inner">
            <p>${body}</p>
            <table class="table table-hover table-condensed">
              <thead>
                <tr>
                  <th>名前</th>
                  <th>電話番号</th>
                  <th>メールアドレス</th>
                  <th>電話連絡状況</th>
                  <th>メール連絡状況</th>
                </tr>
              </thead>
              <tbody>
              {{each status_list}}
                {{if 'CALLED'==telstat || 'PUSH1'==telstat || 'PUSH1'==mailstat }}
                <tr class="success">
                {{else 'CALLWAITING'==telstat || 'CALLPROCESSING'==telstat  }}
                <tr class="warning">
                {{else 'FAIL'==telstat || 'FAIL1'==telstat || 'FAIL2'==telstat || 'HUZAI'==telstat || 'HUZAI1'==telstat || 'HUZAI2'==telstat}}
                <tr class="error">
                {{else}}
                <tr>
                {{/if}}
                  <td>${nickname}</td>
                  <td>${tel}</td>
                  <td>${mail}</td>
                  {{if 'CALLWAITING'==telstat}}
                  <td>発信待機</td>
                  {{else 'CALLPROCESSING'==telstat}}
                  <td>発信作業中</td>
                  {{else 'FAIL'==telstat}}
                  <td>発信失敗</td>
                  {{else 'FAIL1'==telstat}}
                  <td>発信失敗1回目</td>
                  {{else 'FAIL2'==telstat}}
                  <td>発信失敗2回目</td>
                  {{else 'FAIL3'==telstat}}
                  <td>発信失敗3回目</td>
                  {{else 'HUZAI'==telstat}}
                  <td>不在着信</td>
                  {{else 'HUZAI1'==telstat}}
                  <td>不在着信1回目</td>
                  {{else 'HUZAI2'==telstat}}
                  <td>不在着信2回目</td>
                  {{else 'PUSH1'==telstat}}
                  <td>発信：了解有</td>
                  {{else 'CALLED'==telstat}}
                  <td>発信：了解無</td>
                  {{else}}
                  <td>ー</td>
                  {{/if}}
                  {{if 'CALLWAITING'==mailstat}}
                  <td>発信待機</td>
                  {{else 'CALLPROCESSING'==mailstat}}
                  <td>発信作業中</td>
                  {{else 'FAIL1'==mailstat}}
                  <td>発信失敗1回目</td>
                  {{else 'FAIL2'==mailstat}}
                  <td>発信失敗2回目</td>
                  {{else 'PUSH1'==mailstat}}
                  <td>発信：了解有</td>
                  {{else 'CALLED'==mailstat}}
                  <td>発信：了解無</td>
                  {{else}}
                  <td>ー</td>
                  {{/if}}
                </tr>
              {{/each}}
              </tbody>
            </table>
            <p>
              <a style="margin-bottom: 10px;" data-index="${$index}" class="duplicate-link btn btn-small pull-right">送信できなかった送信先宛にもう一度作成する</a>
              <a style="margin-bottom: 10px;" data-index="${$index}" class="duplicate-link btn btn-small pull-right">同じ内容でもう一度作成する</a>
            </p>
          </div>
        </div>
      </div>
    {{/each}}
  </script>

<!-- scripts -->
  <script>
  if (!('console' in window)) {
    window.console = {};
    window.console.log = function(str){
      return str;
    };
  }
  $(".tooltip-target").tooltip();
  function validate_target(){
    //各要素があるか？
    //メール：無効文字が使われていないか？
    //電話：無効文字が使われていないか？
    //FIXME

  }
  function update_call_status(){
    $.ajax({
      type: "GET",
      url: "/api.php/snsconfig/search.json",
      data:  {format: 'json',apiKey: openpne.apiKey,key: 'public_pcall_status'},
      async: false,
      dataType: "json",
      success: function(json){
        console.log("snsconfig/search.json");
        call_list = json.data.value;
        $('#accordion2 > *').remove();
        $("#tmpl_accordion").tmpl(json.data).appendTo('#accordion2');
        $("#collapse-0").collapse('show');
      }
    });
  }
  update_call_status();

/* events */
  $('#update-status').live('click',function(){
    update_call_status();
    $.ajax({
      type: "GET",
      url: "/api.php/call/cron.json",
      data:  {format: 'json',apiKey: openpne.apiKey,mode: 'all'},
      async: true,
      dataType: "json",
      success: function(json){
      }
    });
  });

  $('#docall-modal').on('show', function () {
    $('#docall-title').val($("#call-title").val());
    $('#docall-body').val($("#call-body").val());
  });
  $('#docall-modal').on('shown', function () {
    if(!$("#call-title").val() || !$("#call-body").val()){
      alert("件名と本文が揃っていません。記入の上、テストからやり直してください。");
      $('#docall-modal').modal('hide');
    }
  });
  $('#docall-modal').on('hide', function () {
    $('#docall-button').button('reset');
  });
  $("#docall-button").live('click',function(){
    $('#docall-button').button('loading');
    $.ajax({
      type: "POST",
      url: "/api.php/call/queue.json",
      data: { title: $("#call-title").val(),
              body: $("#call-body").val(),
              member_text: $("#direct-target-text").val()},
      async: true,
      cache: false,
      dataType: "json",
      //FIXME error: のカバー
      success: function(data){
        if(data['status'] == 'success'){
          alert("発信手続きが完了しました");
          $('#docall-modal').modal('hide');

          update_call_status();
          $('#update-status').click();
        }else{
          alert("発信手続きができませんでした:" + data['message']);
        }
        $('#docall-button').button('reset');
      }
    });
  });

  $('#testcall-modal').on('show', function () {
    $('#testcall-title').val($("#call-title").val() ? $("#call-title").val() : $("#call-title").attr("placeholder"));
    $('#testcall-body').val($("#call-body").val() ? $("#call-body").val() : $("#call-body").attr("placeholder"));      
  });

  $('#testcall-modal').on('hide', function () {
    $('#testcall-button').button('reset');
  });

  $("#testcall-button").live('click',function(){
    $('#testcall-button').button('loading');
    $.ajax({
      type: "POST",
      url: "/api.php/call/demo.json",
      data: { tel: $("#testcall-tel").val(),
              body: $("#testcall-body").val()},
      async: true,
      cache: false,
      dataType: "json",
      //FIXME error: のカバー
      success: function(data){
        if(data['status'] == 'success'){
          alert("発信手続きが完了しました");
          $('#testcall-modal').modal('hide');
        }else{
          alert("発信手続きができませんでした:" + data['message']);
        }
        $('#testcall-button').button('reset');
      },
      error: function(data){
      }
    });
  });

  $('.duplicate-link').live('click',function(){
    var index = $(this).attr("data-index");
    var status_list = call_list[index].status_list;
    var str = "";
    for (var i = 0; i < status_list.length; i++) {
      str += status_list[i]["nickname"] + " " + status_list[i]["tel"] + " " +status_list[i]["mail"] + "\n";
    };
    $('#direct-target-text').val(str);
    $('#call-title').val(call_list[index].title);
    $('#call-body').val(call_list[index].body);
  });

  $('#direct-target-text').live('change',function(){
    //console.log("CHANGE");
  });


  </script>
</body>
</html>
