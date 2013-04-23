<!DOCTYPE html>
<html lang="ja">
<head>
<?php include_http_metas() ?>
<?php include_metas() ?>
<title>緊急連絡サービス pCall</title>
<?php use_helper('opRenrakumou') ?>
<?php remove_assets() ?>
<?php use_stylesheet('/opRenrakumouPlugin/css/bootstrap.css') ?>
<?php use_stylesheet('/opRenrakumouPlugin/css/custom.css')?>
<?php include_stylesheets() ?>
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
<?php use_javascript('/opRenrakumouPlugin/js/jquery.js') ?>
<?php use_javascript('/opRenrakumouPlugin/js/jquery.tmpl.js') ?>
<?php use_javascript('/opRenrakumouPlugin/js/jquery.timers.js') ?>
<?php use_javascript('/opRenrakumouPlugin/js/bootstrap.js') ?>
<?php use_javascript('/opRenrakumouPlugin/js/shortcut.js') ?>
<?php use_javascript('/opRenrakumouPlugin/js/pcallutil.js') ?>
<?php use_javascript('/opRenrakumouPlugin/js/pcallvalidator.js') ?>
<?php use_javascript('/opRenrakumouPlugin/js/pcall.js') ?>
<?php include_javascripts() ?>
<?php echo $op_config->get('pc_html_head') ?>
</head>
<body>
<?php include_partial('pcallheader') ?>
  <div class="container">
    <div class="row">
      <div class="span5">
        <h3>発信コントロール</h3>
        <div>
          <ul>
            <li>現在の電話発信数: <span id="telCount"></span></li>
            <li>現在のメール発信数: <span id="mailCount"></span></li>
          </ul>
        </div>
        <div>
          <a id="demoModalButton" href="#demoCallModal" role="button" class="btn btn-block" data-toggle="modal">自分宛にテスト発信</a>
        </div>
        <div>
          <label>連絡先</label>
          <textarea id="directTarget" data-toggle="tab" class="tooltip-target input-block-level" data-placement="right" title="名字[半角スペース]電話番号[半角スペース]メールアドレス[改行]の形式で記入してください。エクセルで表を作成してから貼り付けるのをおすすめします。※電話番号はハイフン無し" rel="tooltip" rows="5" placeholder="山田 09012345678 yamada@example.com"></textarea>
          <label>件名</label>
          <input id="callTitle" class="input-block-level tooltip-target" type="text" placeholder="○月×日大雪休校連絡" rel="tooltip" title="後でわかりやすい日付を入力します。「◯月◯日 6年3組 大雪休みのお知らせ」などと、月日、クラス、内容を簡単に記載してください。" data-placement="right">
          <label>本文</label>
          <textarea id="callBody" data-placement="right" title="200文字以内で、伝えたい要件を記入してください。" class="input-block-level tooltip-target" rows="5" placeholder="○×小学校○年×組の連絡網です。大雪のため○月×日、○×小学校○年×組はお休みになりました。×日はお休みですが、翌△日は通常通り開校します。保護者のみなさま、ご対応、よろしくお願いたします。"></textarea>
          <!-- Button to trigger modal -->
          <a id="doTelModalButton" href="#doCallModal" role="button" class="btn btn-danger btn-block" data-toggle="modal">電話・メール発信</a>
          <a id="doMailModalButton" href="#doCallModal" role="button" class="btn btn-warning btn-block" data-toggle="modal">メール発信</a>
        </div>
      </div>
      <div class="span7">
        <div>
          <h3>送信状況確認 <a id="updateStatus" class="btn pull-right" href="#">更新</a> </h3>
          <div class="accordion" id="updateStatusBody"></div>
        </div>
      </div>
    </div>
<?php include_partial('pcallfooter') ?>
  </div>
  <!-- dialogs -->
  <div id="demoCallModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h3>自分宛にテスト発信</h3>
    </div>
    <div class="modal-body">
      <p>電話連絡：1人 メール連絡：1人</p>
      <label>件名</label>
      <input id="demoCallTitle" class="input-block-level" type="text" disabled>
      <label>連絡本文</label>
      <textarea id="demoCallBody" data-placement="right" title="200文字以内で、伝えたい要件を記入してください。" class="input-block-level tooltip-target" rows="5" disabled></textarea>
      <label>テスト発信先電話番号</label>
      <input id="demoCallTel" class="input-block-level tooltip-target" type="text" placeholder="09012345678" rel="tooltip" title="電話番号をハイフン無しで入力します" data-placement="top">
      <label>テスト発信先メールアドレス</label>
      <input id="demoCallMail" class="input-block-level tooltip-target" type="text" placeholder="demo@example.com" rel="tooltip" title="メールアドレスを記入します" data-placement="top">
    </div>
    <div class="modal-footer">
      <button class="btn pull-left" data-dismiss="modal" aria-hidden="true">キャンセル：編集をやり直す</button>
      <button id="demoCallButton" class="btn btn-danger" type="button" data-loading-text="発信手続き中....">発信：実際に電話（メール）を発信する</button>
    </div>
  </div>
  <div id="doCallModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h3>発信の最終確認</h3>
    </div>
    <div class="modal-body">
      <p>電話連絡：<span id="doCallTargetTelNum"></span>人 メール連絡：<span id="doCallTargetMailNum"></span>人</p>
      <label>件名</label>
      <input id="doCallTitle" class="input-block-level" type="text" disabled>
      <label>連絡本文</label>
      <textarea id="doCallBody" data-placement="right" title="200文字以内で、伝えたい要件を記入してください。" class="input-block-level tooltip-target" rows="5" disabled></textarea>
      <div class="alert alert-error">
        <strong>注意！</strong>発信ボタン押すと、実際に電話（メール）が発信されます。必ずテスト発信で確認をしてから。宛先、本文は十分確認の上、実行してください。<br />
        ※電話1発信につき2秒ほどかかります。発信ボタンを押してから応答があるまでしばらくお待ち下さい。
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn pull-left" data-dismiss="modal" aria-hidden="true">キャンセル：編集をやり直す</button>
      <button id="doCallButton" class="btn btn-danger">発信：実際に電話（メール）を発信する</button>
    </div>
  </div>
  <!-- templates -->
  <script id="tmplAccordion" type="text/x-jquery-tmpl">
    {{each value}}
      <div class="accordion-group">
        <div class="accordion-heading">
          <a class="accordion-toggle" data-toggle="collapse" data-parent="#updateStatusBody" href="#collapse${$index}">
            ${title}
            <span class="muted pull-right">${date}</span>
          </a>
        </div>
        <div id="collapse${$index}" class="accordion-body collapse">
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
              {{each target}}
                {{if 'PUSH' == tel_status || 'PUSH' == mail_status}}
                <tr class="success">
                {{else 'FAIL' == tel_status || 'FAIL' == mail_status}}
                <tr class="error">
                {{else 'CALLED' == mail_status}}
                  {{if 'HUZAI' == tel_status || 'CALLED' == tel_status || 'NONE' == tel_status}}
                <tr class="caution">
                  {{else}}
                <tr class="warning">
                  {{/if}}
                {{else 'CALLED' == tel_status || 'CALLWAITING' == tel_status || 'CALLPROCESSING' == tel_status || 'HUZAI' == tel_status || 'CALLWAITING' == mail_status || 'CALLPROCESSING' == mail_status}}
                <tr class="warning">
                {{else}}
                <tr>
                {{/if}}
                  <td>${name}</td>
                  <td>${tel}</td>
                  <td>${mail}</td>
                  {{if 'CALLWAITING' == tel_status}}
                  <td>発信待機</td>
                  {{else 'CALLPROCESSING' == tel_status}}
                  <td>発信作業中</td>
                  {{else 'FAIL' == tel_status}}
                  <td>発信失敗</td>
                  {{else 'HUZAI' == tel_status}}
                  <td>不在着信</td>
                  {{else 'PUSH' == tel_status}}
                  <td>発信：了解有</td>
                  {{else 'CALLED' == tel_status}}
                  <td>発信：了解無</td>
                  {{else}}
                  <td>ー</td>
                  {{/if}}
                  {{if 'CALLWAITING' == mail_status}}
                  <td>発信待機</td>
                  {{else 'CALLPROCESSING' == mail_status}}
                  <td>発信作業中</td>
                  {{else 'FAIL' == mail_status}}
                  <td>発信失敗</td>
                  {{else 'PUSH' == mail_status}}
                  <td>発信：了解有</td>
                  {{else 'CALLED' == mail_status}}
                  <td>発信：了解無</td>
                  {{else}}
                  <td>ー</td>
                  {{/if}}
                </tr>
              {{/each}}
              </tbody>
            </table>
            <p>
              <a style="margin-bottom: 10px;" data-index="${$index}" class="recreateError duplicate-link btn btn-small pull-right">送信できなかった送信先宛にもう一度作成する</a>
              <a style="margin-bottom: 10px;" data-index="${$index}" class="recreateAll duplicate-link btn btn-small pull-right">同じ内容でもう一度作成する</a>
            </p>
          </div>
        </div>
      </div>
    {{/each}}
  </script>
</body>
</html>
