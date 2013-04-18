$ = jQuery.noConflict();

// pCall定数定義オブジェクト
var pCallConst = {
  /* 定数定義 */
  // 送信タイプ
  // 自分へのデモ発信
  SEND_TYPE_DEMO: 99,
  // 電話＋メール
  SEND_TYPE_TEL: 1,
  // メール
  SEND_TYPE_MAIL: 2,
  // boundioステータス取得繰り返し秒数
  TIMER_BOUNDIO_STATUS: 120,
  // 連絡先最大件数
  DIRECT_TARGET_NUM: 50,
  // 件数最大文字数
  CALL_TITLE_LENGTH: 200,
  // 本文最大文字数
  CALL_BODY_LENGTH: 200,
  // 名前
  TARGET_NAME_LENGTH: 64,
  // 電話番号
  TARGET_TEL_LENGTH: 11,
  // メールアドレス
  TARGET_MAIL_LENGTH: 255,
  // todo: 次バージョンでは最大電話送信数、最大メール送信数をサーバから取得するようにする。
  // 無料通話は10回まで。無料メール送信は500回まで
  maxTelCount: 10,
  maxMailCount: 500
};

// 送信データオブジェクト
var sendDataObject = function(){
  // 送信タイプ(pCallConst.SEND_TYPE_DEMO, pCallConst.SEND_TYPE_TEL, pCallConst.SEND_TYPE_MAIL)
  var sendType = -1;
  // 送信先リスト
  var targetList = null;
  // 連絡先入力値
  var targetText = '';
  // 件名入力値
  var titleText = '';
  // 本文入力値
  var bodyText = '';

  return this;
};

// 関数定義
var pCall = {
  // 送信状況
  sendStatusList: null,

  // 現在の発信数の表示
  showSendCount: function(sendCount){
    if (null != sendCount){
      $('#telCount').html(sendCount['tel']);
      $('#mailCount').html(sendCount['mail']);
    }
  },

  // 現在の発信数の取得
  getCalledCount: function(){
    var sendCount = null;
    // 送信状況の取得
    $.ajax({
      type: 'GET',
      url: openpne.apiBase + 'call/count.json',
      data:  {apiKey: openpne.apiKey},
      dataType: 'json',
      async: false,
      success: function(data){
        if ('success' == data['status'])
        {
          sendCount = [];
          sendCount['tel'] = data['data']['tel_count'];
          sendCount['mail'] = data['data']['mail_count'];
        }
        else
        {
          alert('送信数が取得できませんでした。');
        }
      },
      error: function(data){
        alert('送信数が取得できませんでした。');
      }
    });

    return sendCount;
  },

  // 送信状況の表示
  showStatus: function(){
    // 現在の発信数の取得
    var sendCount = pCall.getCalledCount();
    // 送信数表示
    pCall.showSendCount(sendCount);
    // boundioステータスの更新
    pCall.updateBoundio();
    // 送信状況の取得
    var sendStatus = pCall.getSendStatus();
    // 送信状況の表示　
    if (null != sendStatus){
      $('#updateStatusBody > *').remove();
      $('#tmplAccordion').tmpl({value: sendStatus}).appendTo('#updateStatusBody');
      $('#collapse0').collapse('show');
    }
  },

  // 送信状況の取得
  getSendStatus: function(){
    var sendStatus = null;
    // 送信状況の取得
    $.ajax({
      type: 'GET',
      url: openpne.apiBase + 'call/status.json',
      data:  {apiKey: openpne.apiKey},
      dataType: 'json',
      async: false,
      success: function(data){
        if ('success' == data['status'])
        {
          sendStatus = data['data'];
        }
        else
        {
          alert('送信状況が取得できませんでした。');
        }
      },
      error: function(data){
        alert('送信状況が取得できませんでした。');
      }
    });

    return sendStatus;
  },

  /**
   * 再作成
   * @param sendData 送信データ
   * @param index コピー元送信状況のインデックス
   * @param isOnlyError
   */
  recreate: function(sendData, index, isOnlyError){
    var statusList = this.sendStatusList[index].target;
    var str = '';
    for (var i = 0; i < statusList.length; i++) {
      var telStatus = statusList[i]['tel_status'];
      var mailStatus = statusList[i]['mail_status'];
      if (isOnlyError){
        var isCopy = false;
        if ('FAIL' === telStatus
          && ('FAIL' === mailStatus
          || 'CALLED' === mailStatus
          || 'NONE' === mailStatus)){
          isCopy = true;
        }
        else if ('HUZAI' === telStatus
          && ('FAIL' === mailStatus
          || 'NONE' === mailStatus)){
          isCopy = true;
        }
        else if ('CALLED' === telStatus
          && ('FAIL' === mailStatus
          || 'NONE' === mailStatus)){
          isCopy = true;
        }
        else if ('NONE' === telStatus && 'FAIL' === mailStatus){
          isCopy = true;
        }

        if (isCopy){
          str += statusList[i]['name'] + ' ' + statusList[i]['tel'] + ' ' +statusList[i]['mail'] + '\n';
        }
      }else{
        str += statusList[i]['name'] + ' ' + statusList[i]['tel'] + ' ' +statusList[i]['mail'] + '\n';
      }
    }
    if (str){
      $('#directTarget').val(str);
      $('#callTitle').val(this.sendStatusList[index].title);
      $('#callBody').val(this.sendStatusList[index].body);
    }else{
      alert('再作成内容はありません。');
      $('#directTarget').val('');
      $('#callTitle').val('');
      $('#callBody').val('');
    }
  },

  // 送信処理
  send: function(sendData){
    // 送信データの作成
    sendData['apiKey'] = openpne.apiKey;

    // titleの文字列変換
    var titleText = '';
    if (pCallConst.SEND_TYPE_TEL == sendData.sendType || pCallConst.SEND_TYPE_MAIL == sendData.sendType){
      titleText = $.trim($('#callTitle').val());
    }else{
      titleText = $.trim($('#demoCallTitle').val());
    }
    // 改行コード、全角空白、半角空白の置換
    titleText = pCallUtil.replaceString(titleText);

    sendData['title'] = titleText;

    // bodyの文字列変換
    var bodyText = '';
    if (pCallConst.SEND_TYPE_TEL == sendData.sendType || pCallConst.SEND_TYPE_MAIL == sendData.sendType)
    {
      bodyText = $.trim($('#callBody').val());
    }
    else
    {
      bodyText = $.trim($('#demoCallBody').val());
    }
    // 改行コード、全角空白、半角空白の置換
    bodyText = pCallUtil.replaceString(bodyText);

    sendData['body'] = bodyText;

    // 本番の場合
    if (pCallConst.SEND_TYPE_TEL == sendData.sendType || pCallConst.SEND_TYPE_MAIL == sendData.sendType)
    {
      // ボタンをローディング中に変更
      $('#doCallButton').button('loading');
      // 送信データの作成
      sendData['target'] = targetList;
    }
    // デモの場合
    else
    {
      // ボタンをローディング中に変更
      $('#demoCallButton').button('loading');
      // 送信データの作成
      var target = {};
      target['name'] = 'myself';
      // テスト発信先電話番号
      var demoTel = $.trim($('#demoCallTel').val());
      // 全角数字の置換
      demoTel = demoTel.replace(/[０-９]/g, function(s) {
        return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
      });

      target['tel'] = demoTel;
      target['mail'] = $.trim($('#demoCallMail').val());
      var targets = [];
      targets.push(target);

      sendData['target'] = targets;
    }

    // 送信
    $.ajax({
      type: 'POST',
      url: openpne.apiBase + 'call/send.json',
      data: sendTargetList,
      cache: false,
      dataType: 'json',
      success: function(data){
        if ('success' == data['status'])
        {
          alert('発信手続きが完了しました。');
          if (pCallConst.SEND_TYPE_TEL == sendType || pCallConst.SEND_TYPE_MAIL == sendType)
          {
            $('#doCallModal').modal('hide');
          }
          else
          {
            $('#demoCallModal').modal('hide');
          }
        }
        else
        {
          alert('発信手続きができませんでした。');
        }
        if (pCallConst.SEND_TYPE_TEL == sendType || pCallConst.SEND_TYPE_MAIL == sendType)
        {
          $('#doCallButton').button('reset');
        }
        else
        {
          $('#demoCallButton').button('reset');
        }
      },
      error: function(data){
        alert('発信手続きができませんでした。');
        if (pCallConst.SEND_TYPE_TEL == sendType || pCallConst.SEND_TYPE_MAIL == sendType)
        {
          $('#doCallButton').button('reset');
        }
        else
        {
          $('#demoCallButton').button('reset');
        }
      }
    });
  },

  // boundioステータスの取得
  updateBoundio: function(){
    // 送信状況の取得
    $.ajax({
      type: 'GET',
      url: openpne.apiBase + 'call/update.json',
      data:  {apiKey: openpne.apiKey},
      dataType: 'json',
      success: function(data){
        // 何もしない
      },
      error: function(data){
        // 何もしない
      }
    });
  }
};

$(document).ready(function(){
  // 送信オブジェクトの生成
  var sendData = sendDataObject();

  // 初期表示
  // ツールチップテキストの表示
  $('.tooltip-target').tooltip();
  // 送信状況表示
  pCall.showStatus();

  /* イベント定義 */
  // boundioステータスを一定間隔で取得
  var msec = pCallConst.TIMER_BOUNDIO_STATUS * 1000;
  $('body').everyTime(msec, pCall.showStatus);

  // 自分宛にテスト発信ボタン押下時
  $('#demoModalButton').on('click', function(){
    sendData.sendType = pCallConst.SEND_TYPE_DEMO;
  });

  // 自分宛にテスト発信ダイアログ表示時
  var demoCallModal = $('#demoCallModal');
  demoCallModal.on('show', function(){
    var callTitle = $('#callTitle');
    var callBody = $('#callBody');
    var demoCallTitle = $('#demoCallTitle');
    var demoCallBody = $('#demoCallBody');
    $.trim($(callTitle).val()) ? demoCallTitle.val($.trim($(callTitle).val())) : demoCallTitle.val($.trim($(callTitle).attr('placeholder')));
    $.trim(callBody.val()) ? demoCallBody.val($.trim(callBody.val())) : demoCallBody.val($.trim(callBody.attr('placeholder')));
  });

  // 自分宛にテスト発信ダイアログ表示後
  demoCallModal.on('shown', function(){
    var valid = pCallValidator.isValid(sendData);
    if (!valid){
      return false;
    }
  });

  // 自分宛にテスト発信ダイアログ非表示時
  demoCallModal.on('hide', function(){
    $('#demoCallButton').button('reset');
  });

  // 自分宛にテスト発信ダイアログ：発信ボタン押下時
  $('#demoCallButton').on('click', function(){
    // テスト発信先電話番号
    var demoTel = $.trim($('#demoCallTel').val());
    // 全角数字の置換
    demoTel = demoTel.replace(/[０-９]/g, function(s) {
      return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
    });
    // テスト発信先メールアドレス
    var demoMail = $.trim($('#demoCallMail').val());

    var valid = pCallValidator.isValidForDemo(demoTel, demoMail);
    if (!valid){
      return false;
    }
    pCall.send(false);
  });

  // 電話・メール発信ボタン押下時
  $('#doTelModalButton').on('click', function(){
    sendData.sendType = pCallConst.SEND_TYPE_TEL;
  });

  // メール発信ボタン押下時
  $('#doMailModalButton').on('click', function(){
    sendData.sendType = pCallConst.SEND_TYPE_MAIL;
  });

  // 発信の最終確認ダイアログ表示時
  var doCallModal = $('#doCallModal');
  doCallModal.on('show', function(){
    $('#doCallTitle').val($.trim($('#callTitle').val()));
    $('#doCallBody').val($.trim($('#callBody').val()));
  });

  // 発信の最終確認ダイアログ表示後
  doCallModal.on('shown', function(){
    var valid = pCallValidator.isValid(sendData);
    if (!valid)
    {
      doCallModal.modal('hide');
      return false;
    }
  });

  // 発信の最終確認ダイアログ非表示時
  doCallModal.on('hide', function(){
    $('#doCallButton').button('reset');
  });

  // 発信の最終確認ダイアログ：発信ボタン押下時
  $('#doCallButton').on('click', function(){
    pCall.send(true);
  });

  // 更新ボタン押下時
  $('#updateStatus').on('click',function(){
    pCall.showStatus();
  });

  // 同じ内容でもう一度作成するボタン押下時
  $(document).on('click', '.recreateAll', function(){
    var index = $(this).attr('data-index');
    pCall.recreate(sendData, index, false);
  });

  // 送信できなかった送信先宛にもう一度作成するボタン押下時
  $(document).on('click', '.recreateError', function(){
    var index = $(this).attr('data-index');
    pCall.recreate(sendData, index, true);
  });
});
