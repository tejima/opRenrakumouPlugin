$ = jQuery.noConflict();
$(document).ready(function (){
  /* 定数定義 */
  var DEMO = 'demo';
  var PROD = 'do';
  // 送信タイプ
  // 自分へのデモ発信
  SEND_TYPE_DEMO = 99;
  // 電話＋メール
  SEND_TYPE_TEL = 1;
  // メール
  SEND_TYPE_MAIL = 2;
  // boundioステータス取得繰り返し秒数
  var TIMER_BOUNDIO_STATUS = 120;
  // 連絡先最大件数
  var DIRECT_TARGET_NUM = 50;
  // 件数最大文字数
  var CALL_TITLE_LENGTH = 200;
  // 本文最大文字数
  var CALL_BODY_LENGTH = 200;
  // 名前
  var TARGET_NAME_LENGTH = 64;
  // 電話番号
  var TARGET_TEL_LENGTH = 11;
  // メールアドレス
  var TARGET_MAIL_LENGTH = 255;

  /* オブジェクトの初期化 */
  var targetList = null;
  var sendType = -1;
  var sendStatusList = null;
  var sendTargets = null;

  // todo: 次バージョンでは最大電話送信数、最大メール送信数をサーバから取得するようにする。
  var maxTelCount = 110;
  var maxMailCount = 500;

  var telCount = 0;
  var mailCount = 0;

  // 初期表示ここから----------------
  if (!('console' in window))
  {
    window.console = {};
    window.console.log = function (str){
      return str;
    };
  }
  // ツールチップテキストの表示
  $(".tooltip-target").tooltip();
  // 送信状況表示
  updateStatus();
  // 送信数表示
  getCalledCount();
  // 初期表示ここまで----------------

  /* イベント定義 */
  // boundioステータス取得
  var msec = TIMER_BOUNDIO_STATUS * 1000;
  $("body").everyTime(msec, updateStatus);

  // 自分宛にテスト発信ボタン押下時
  $('#demoModalButton').on('click', function (){
    sendType = SEND_TYPE_DEMO;
  });
  // 自分宛にテスト発信ダイアログ表示時
  $('#demoCallModal').on('show', function (){
    $('#demoCallTitle').val($.trim($("#callTitle").val()) ? $.trim($("#callTitle").val()) : $.trim($("#callTitle").attr("placeholder")));
    $('#demoCallBody').val($.trim($("#callBody").val()) ? $.trim($("#callBody").val()) : $.trim($("#callBody").attr("placeholder")));
  });
  // 自分宛にテスト発信ダイアログ表示後
  $('#demoCallModal').on('shown', function (){
    var valid = isValid(false);
    if (!valid)
    {
      return false;
    }
  });
  // 自分宛にテスト発信ダイアログ非表示時
  $('#demoCallModal').on('hide', function (){
    $('#demoCallButton').button('reset');
  });
  // 自分宛にテスト発信ダイアログ：発信ボタン押下時
  $('#demoCallButton').live('click', function (){
    var valid = isValidForDemo();
    if (!valid)
    {
      return false;
    }
    send(false);
  });

  // 電話・メール発信ボタン押下時
  $('#doTelModalButton').on('click', function (){
    sendType = SEND_TYPE_TEL;
  });
  // メール発信ボタン押下時
  $('#doMailModalButton').on('click', function (){
    sendType = SEND_TYPE_MAIL;
  });
  // 発信の最終確認ダイアログ表示時
  $('#doCallModal').on('show', function (){
    $('#doCallTitle').val($.trim($("#callTitle").val()));
    $('#doCallBody').val($.trim($("#callBody").val()));
  });
  // 発信の最終確認ダイアログ表示後
  $('#doCallModal').on('shown', function (){
    var valid = isValid(true);
    if (!valid)
    {
      $('#doCallModal').modal('hide');
      return false;
    }
  });
  // 発信の最終確認ダイアログ非表示時
  $('#doCallModal').on('hide', function (){
    $('#doCallButton').button('reset');
  });
  // 発信の最終確認ダイアログ：発信ボタン押下時
  $('#doCallButton').live('click', function (){
    send(true);
  });

  // 更新ボタン押下時
  $('#updateStatus').live('click',function (){
    updateStatus();
  });

  // 同じ内容でもう一度作成するボタン押下時
  $('#recreateAll').live('click', function (){
    var index = $(this).attr("data-index");
    recreate(index, false);
  });
  // 送信できなかった送信先宛にもう一度作成するボタン押下時
  $('#recreateError').live('click', function (){
    var index = $(this).attr("data-index");
    recreate(index, true);
  });

  // 入力チェック
  // isProd: デモの場合はfalse
  function isValid(isProd)
  {
    // 連絡先
    var isValidTarget = isValidDirectTarget(isProd);
    if (!isValidTarget)
    {
      return false;
    }

    // 件名
    var isValidTitle = isValidCallTitle(isProd);
    if (!isValidTitle)
    {
      return false;
    }

    // 本文
    var isValidBody = isValidCallBody(isProd);
    if (!isValidBody)
    {
      return false;
    }

    return true;
  }

  // 連絡先のチェック
  // isNullCheck: 未入力チェックを行う場合はtrue
  function isValidDirectTarget(isNullCheck)
  {
    var targetValue = $.trim($("#directTarget").val());
    if (0 == targetValue.length)
    {
      // 未入力チェックを行う場合
      if (isNullCheck)
      {
        alert('連絡先が入力されていません。');

        return false;
      }

      return true;
    }

    // 連絡先リストの分解
    targetList = parseTarget();

    // 件数チェック
    var targetListLen = targetList.length;
    if (0 == targetListLen)
    {
      alert('連絡先が入力されていません。');

      return false;
    }
    else if (DIRECT_TARGET_NUM < targetListLen)
    {
      alert('一度に送信できる連絡先は' + DIRECT_TARGET_NUM + '件以下です。');

      return false;
    }

    // 連絡先内容チェック
    var isInvalid = false;
    for (var index = 0; index < targetListLen; index++)
    {
      var targetInfo = targetList[index];
      // 名前
      var targetName = 'undefined' == typeof(targetInfo[0]) ? '': targetInfo[0];
      // 電話番号
      var targetTel = 'undefined' == typeof(targetInfo[1]) ? '': targetInfo[1];
      // メールアドレス
      var targetMail = 'undefined' == typeof(targetInfo[2]) ? '': targetInfo[2];

      // 名前文字数チェック
      if (0 == targetName.length)
      {
        isInvalid = true;
        break;
      }
      else if (TARGET_NAME_LENGTH < targetName.length)
      {
        isInvalid = true;
        break;
      }

      // 電話番号文字数チェック
      if (0 == targetTel.length)
      {
        isInvalid = true;
        break;
      }
      else if (TARGET_TEL_LENGTH < targetTel.length)
      {
        isInvalid = true;
        break;
      }
      // 電話番号文字種チェック
      if (!$.isNumeric(targetTel))
      {
        isInvalid = true;
        break;
      }

      // メールアドレス文字数チェック
      if (TARGET_MAIL_LENGTH < targetMail.length)
      {
        isInvalid = true;
        break;
      }
      // メールアドレス形式チェック
      if (0 < targetMail.length)
      {
        if (!targetMail.match(/.+@.+\..+/g))
        {
          isInvalid = true;
          break;
        }
      }
      else
      {
        if (SEND_TYPE_MAIL == sendType)
        {
          isInvalid = true;
          break;
        }
      }
    }

    if (isInvalid)
    {
      alert('連絡先は、名字[半角スペース]電話番号[半角スペース]メールアドレス[改行]の形式で記入してください。\n電話番号はハイフン無し数字のみで入力してください。');

      return false;
    }

    // 送信データの作成
    var targetListLen = targetList.length;
    var targets = [];
    var sendTelCount = 0;
    var sendMailCount = 0;
    for (var index = 0; index < targetListLen; index++)
    {
      var targetInfo = targetList[index];
      var info = {};
      // 名前
      info['name'] = 'undefined' == typeof(targetInfo[0]) ? '': targetInfo[0];
      // 電話番号
      info['tel'] = 'undefined' == typeof(targetInfo[1]) ? '': targetInfo[1];
      if (info['tel'])
      {
        if (SEND_TYPE_MAIL != sendType)
        {
          sendTelCount++;
        }
      }
      // メールアドレス
      info['mail'] = 'undefined' == typeof(targetInfo[2]) ? '': targetInfo[2];
      if (info['mail'])
      {
        sendMailCount++;
      }
      targets.push(info);
    }
    sendTargets = targets;

    // 送信制限数チェック
    // 現在の送信数の取得
    getCalledCount();
    // メール送信の場合
    $('#doCallTargetTelNum').html(sendTelCount);
    $('#doCallTargetMailNum').html(sendMailCount);
    if (SEND_TYPE_MAIL == sendType)
    {
      if (maxMailCount < (mailCount + sendMailCount))
      {
        alert('メール送信数が最大を超えてしまうため発信できません。');
        return false;
      }
    }
    if (maxTelCount < (telCount + sendTelCount) || maxMailCount < (mailCount + sendMailCount))
    {
      alert('電話発信数またはメール送信数が最大を超えてしまうため発信できません。');
      return false;
    }

    return true;
  }

  // 連絡先リストの分解
  function parseTarget()
  {
    var targetValue = $.trim($("#directTarget").val());
    // 改行コードの置換
    targetValue = targetValue.replace(/\r\n/g, '<>');
    targetValue = targetValue.replace(/(\n|\r)/g, '<>');
    // 全角空白の置換
    targetValue = targetValue.replace(/　/g, ' ');
    // 全角数字の置換
    targetValue = targetValue.replace(/[０-９]/g, function (s) {
      return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
    });

    // 連絡先情報を改行毎に区切る
    var targets = targetValue.split('<>');
    var targetsLen = targets.length;
    var targetValues = [];
    for (var index = 0; index < targetsLen; index++)
    {
      var target = targets[index];
      // 連絡先1件を名前、電話番号、メールアドレスに分ける
      var targetVals = target.split(' ');
      targetValues.push(targetVals);
    }

    return targetValues;
  }

  // 件名入力チェック
  // isNullCheck: 未入力チェックを行う場合はtrue
  function isValidCallTitle(isNullCheck)
  {
    var callTitleLength = $.trim($("#callTitle").val()).length;
    // 未入力チェックを行う場合
    if (isNullCheck)
    {
      if (0 == callTitleLength)
      {
        alert('件名が入力されていません。');

        return false;
      }
    }
    // 文字数チェック
    if (CALL_TITLE_LENGTH < callTitleLength)
    {
      alert('件名は' + CALL_TITLE_LENGTH + '文字以下で入力してください。');

      return false;
    }
    return true;
  }

  // 本文入力チェック
  // isNullCheck: 未入力チェックを行う場合はtrue
  function isValidCallBody(isNullCheck)
  {
    var callBodyLength = $.trim($("#callBody").val()).length;
    // 未入力チェックを行う場合
    if (isNullCheck)
    {
      if (0 == callBodyLength)
      {
        alert('本文が入力されていません。');

        return false;
      }
    }
    // 文字数チェック
    if (CALL_BODY_LENGTH < callBodyLength)
    {
      alert('本文は' + CALL_BODY_LENGTH + '文字以下で入力してください。');

      return false;
    }
    return true;
  }

  // 自分宛にテスト発信画面での入力チェック
  function isValidForDemo()
  {
    // テスト発信先電話番号
    var demoTel = $.trim($("#demoCallTel").val());
    // 全角数字の置換
    demoTel = demoTel.replace(/[０-９]/g, function (s) {
      return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
    });
    // テスト発信先メールアドレス
    var demoMail = $.trim($("#demoCallMail").val());

    // テスト発信先電話番号文字数チェック
    if (0 == demoTel.length || TARGET_TEL_LENGTH < demoTel.length || !$.isNumeric(demoTel))
    {
      alert('テスト発信先電話番号はハイフン無し数字のみで入力してください。');

      return false;
    }

    // テスト発信先メールアドレス文字数チェック
    if (0 == demoMail.length || TARGET_MAIL_LENGTH < demoMail.length || (0 < demoMail.length && !demoMail.match(/.+@.+\..+/g)))
    {
      alert('テスト発信先メールアドレスを正しく入力してください。');

      return false;
    }

    return true;
  }

  // 現在の発信数の取得
  function getCalledCount()
  {
    // 送信状況の取得
    $.ajax({
      type: "GET",
      url: openpne.apiBase + "call/count.json",
      data:  {apiKey: openpne.apiKey},
      async: false,
      dataType: "json",
      success: function (data){
        if ('success' == data['status'])
        {
          telCount = data['data']['tel_count'];
          mailCount = data['data']['mail_count'];
          $('#telCount').html(telCount);
          $('#mailCount').html(mailCount);
        }
        else
        {
          alert("送信数が取得できませんでした:");
        }
      },
      error: function (data){
        alert("送信数が取得できませんでした:");
      }
    });
  }

  // 送信状況の表示
  function updateStatus()
  {
    // boundioステータスの更新
    updateBoundio();
    // 送信状況の取得
    $.ajax({
      type: "GET",
      url: openpne.apiBase + "call/status.json",
      data:  {apiKey: openpne.apiKey},
      async: false,
      dataType: "json",
      success: function (data){
        if ('success' == data['status'])
        {
          sendStatusList = data['data'];
          $('#updateStatusBody > *').remove();
          $("#tmplAccordion").tmpl({value: sendStatusList}).appendTo('#updateStatusBody');
          $("#collapse0").collapse('show');
        }
        else
        {
          alert("送信状況が取得できませんでした:");
        }
      },
      error: function (data){
        alert("送信状況が取得できませんでした:");
      }
    });
  }

  // 送信状況から各入力項目への値コピー
  function recreate(index, isOnlyError)
  {
    var statusList = sendStatusList[index].target;
    var str = "";
    for (var i = 0; i < statusList.length; i++) {
      if (isOnlyError)
      {
        if ('PUSH' !== statusList[i]['tel_status'] || 'PUSH' !== statusList[i]['mail_status'])
        {
          str += statusList[i]['name'] + " " + statusList[i]['tel'] + " " +statusList[i]['mail'] + "\n";
        }
      }
      else
      {
        str += statusList[i]['name'] + " " + statusList[i]['tel'] + " " +statusList[i]['mail'] + "\n";
      }
    };
    if (str)
    {
      $('#directTarget').val(str);
      $('#callTitle').val(sendStatusList[index].title);
      $('#callBody').val(sendStatusList[index].body);
    }
    else
    {
      alert('再作成内容はありません。');
      $('#directTarget').val('');
      $('#callTitle').val('');
      $('#callBody').val('');
    }
  }

  // 送信処理
  function send(isProd)
  {
    // 送信データの作成
    var sendTargetList = {};
    sendTargetList['apiKey'] = openpne.apiKey;
    sendTargetList['type'] = sendType;

    // titleの文字列変換
    var titleText = '';
    if (isProd)
    {
      titleText = $.trim($("#callTitle").val());
    }
    else
    {
      titleText = $.trim($("#demoCallTitle").val());
    }
    // 改行コードの置換
    titleText = titleText.replace(/\r\n/g, '');
    titleText = titleText.replace(/(\n|\r)/g, '');
    // 全角空白の置換
    titleText = titleText.replace(/　/g, '');
    // 半角空白の置換
    titleText = titleText.replace(/ /g, '');

    sendTargetList['title'] = titleText;

    // bodyの文字列変換
    var bodyText = '';
    if (isProd)
    {
      bodyText = $.trim($("#callBody").val());
    }
    else
    {
      bodyText = $.trim($("#demoCallBody").val());
    }
    // 改行コードの置換
    bodyText = bodyText.replace(/\r\n/g, '');
    bodyText = bodyText.replace(/(\n|\r)/g, '');
    // 全角空白の置換
    bodyText = bodyText.replace(/　/g, '');
    // 半角空白の置換
    bodyText = bodyText.replace(/ /g, '');

    sendTargetList['body'] = bodyText;

    // 本番の場合
    if (isProd)
    {
      // ボタンをローディング中に変更
      $('#doCallButton').button('loading');
      // 送信データの作成
      sendTargetList['target'] = sendTargets;
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
      var demoTel = $.trim($("#demoCallTel").val());
      // 全角数字の置換
      demoTel = demoTel.replace(/[０-９]/g, function (s) {
        return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
      });

      target['tel'] = demoTel;
      target['mail'] = $.trim($("#demoCallMail").val());
      var targets = [];
      targets.push(target);

      sendTargetList['target'] = targets;
    }

    // 送信
    $.ajax({
      type: "POST",
      url: openpne.apiBase + "call/send.json",
      data: sendTargetList,
      async: true,
      cache: false,
      dataType: "json",
      success: function (data){
        if ('success' == data['status'])
        {
          alert("発信手続きが完了しました");
          if (isProd)
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
          alert("発信手続きができませんでした:");
        }
        if (isProd)
        {
          $('#doCallButton').button('reset');
        }
        else
        {
          $('#demoCallButton').button('reset');
        }
      },
      error: function (data){
        alert("発信手続きができませんでした:");
        if (isProd)
        {
          $('#doCallButton').button('reset');
        }
        else
        {
          $('#demoCallButton').button('reset');
        }
      }
    });
  }

  // boundioステータスの取得
  function updateBoundio()
  {
    // 送信状況の取得
    $.ajax({
      type: "GET",
      url: openpne.apiBase + "call/update.json",
      data:  {apiKey: openpne.apiKey},
      async: false,
      dataType: "json",
      success: function (data){
        // 何もしない
      },
      error: function (data){
        // 何もしない
      }
    });
  }
});
