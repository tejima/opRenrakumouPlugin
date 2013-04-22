// 連絡網プラグインバリデータ
var pCallValidator = {
  /**
   * 入力チェック
   * @param sendData 送信データ
   * @returns {boolean} 入力エラーがある場合はfalse
   */
  isValid: function(sendData){
    // 連絡先
    var isValidTarget = this.isValidDirectTarget(sendData);
    if (!isValidTarget){
      return false;
    }

    // 件名
    var isValidTitle = this.isValidCallTitle(sendData);
    if (!isValidTitle){
      return false;
    }

    // 本文
    var isValidBody = this.isValidCallBody(sendData);
    if (!isValidBody){
      return false;
    }

    return true;
  },

  // 連絡先のチェック
  // isNotEnterCheck: 未入力チェックを行う場合はtrue
  isValidDirectTarget: function(sendData){
    var targetValue = $.trim($('#directTarget').val());
    if (0 == targetValue.length){
      // 未入力チェックを行う場合
      if (pCall.SEND_TYPE_TEL == sendData.sendType || pCallConst.SEND_TYPE_MAIL == sendData.sendType){
        alert('連絡先が入力されていません。');

        return false;
      }

      return true;
    }

    // 連絡先リストの分解
    sendData.targetList = pCallUtil.parseTarget(targetValue);

    // 件数チェック
    var targetDataLen = sendData.targetList.length;
    if (0 == targetDataLen){
      alert('連絡先が入力されていません。');

      return false;
    }else if (pCallConst.DIRECT_TARGET_NUM < targetDataLen){
      alert('一度に送信できる連絡先は' + pCallConst.DIRECT_TARGET_NUM + '件以下です。');

      return false;
    }

    // 連絡先内容チェック
    var isInvalid = false;
    for (var index = 0; index < targetDataLen; index++){
      var targetInfo = sendData.targetList[index];
      // 名前
      var targetName = 'undefined' == typeof(targetInfo[0]) ? '': targetInfo[0];
      // 電話番号
      var targetTel = 'undefined' == typeof(targetInfo[1]) ? '': targetInfo[1];
      // メールアドレス
      var targetMail = 'undefined' == typeof(targetInfo[2]) ? '': targetInfo[2];

      // 名前文字数チェック
      if (0 == targetName.length){
        isInvalid = true;
        break;
      }else if (pCallConst.TARGET_NAME_LENGTH < targetName.length){
        isInvalid = true;
        break;
      }

      // 電話番号文字数チェック
      if (0 == targetTel.length){
        isInvalid = true;
        break;
      }else if (pCallConst.TARGET_TEL_LENGTH < targetTel.length){
        isInvalid = true;
        break;
      }

      // 電話番号文字種チェック
      //if (!$.isNumeric(targetTel))
      if (null === targetTel.match(/^[0-9]+$/)){
        isInvalid = true;
        break;
      }

      // メールアドレス文字数チェック
      if (pCallConst.TARGET_MAIL_LENGTH < targetMail.length){
        isInvalid = true;
        break;
      }

      // メールアドレス形式チェック
      if (0 < targetMail.length){
        if (!targetMail.match(/.+@.+\..+/g)){
          isInvalid = true;
          break;
        }
      }else{
        if (pCallConst.SEND_TYPE_MAIL == sendData.sendType){
          isInvalid = true;
          break;
        }
      }
    }

    if (isInvalid){
      alert('連絡先は、名字[半角スペース]電話番号[半角スペース]メールアドレス[改行]の形式で記入してください。\n電話番号はハイフン無し数字のみで入力してください。');

      return false;
    }

    // 送信データの作成
    var targets = [];
    var sendTelCount = 0;
    var sendMailCount = 0;
    for (index = 0; index < targetDataLen; index++){
      var targetInfo = sendData.targetList[index];
      var info = {};
      // 名前
      info['name'] = 'undefined' == typeof(targetInfo[0]) ? '': targetInfo[0];
      // 電話番号
      info['tel'] = 'undefined' == typeof(targetInfo[1]) ? '': targetInfo[1];
      if (info['tel']){
        if (pCallConst.SEND_TYPE_MAIL != sendData.sendType){
          sendTelCount++;
        }
      }
      // メールアドレス
      info['mail'] = 'undefined' == typeof(targetInfo[2]) ? '': targetInfo[2];
      if (info['mail']){
        sendMailCount++;
      }
      targets.push(info);
    }
    sendData.targetList = targets;

    // 送信制限数チェック
    // 現在の発信数の取得
    var sendCount = pCall.getCalledCount();
    // 送信数表示
    pCall.showSendCount(sendCount);
    var mailCount = sendCount['mail'];
    var telCount = sendCount['tel'];
    // メール送信の場合
    $('#doCallTargetTelNum').html(sendTelCount);
    $('#doCallTargetMailNum').html(sendMailCount);
    if (pCallConst.SEND_TYPE_MAIL == sendData.sendType){
      if (pCallConst.maxMailCount < (mailCount + sendMailCount)){
        alert('メール送信数が最大を超えてしまうため発信できません。');
        return false;
      }
    }
    if (pCallConst.maxTelCount < (telCount + sendTelCount) || pCallConst.maxMailCount < (mailCount + sendMailCount)){
      alert('電話発信数またはメール送信数が最大を超えてしまうため発信できません。');
      return false;
    }

    return true;
  },

  // 本文入力チェック
  // isNotEnterCheck: 未入力チェックを行う場合はtrue
  isValidCallBody: function(sendData){
    var callBodyLength = $.trim($('#callBody').val()).length;
    // 未入力チェックを行う場合
    if (pCallConst.SEND_TYPE_TEL == sendData.sendType || pCallConst.SEND_TYPE_MAIL == sendData.sendType){
      if (0 == callBodyLength){
        alert('本文が入力されていません。');

        return false;
      }
    }
    // 文字数チェック
    if (pCallConst.CALL_BODY_LENGTH < callBodyLength){
      alert('本文は' + pCallConst.CALL_BODY_LENGTH + '文字以下で入力してください。');

      return false;
    }

    return true;
  },

  // 自分宛にテスト発信画面での入力チェック
  isValidForDemo: function(demoTel, demoMail){
    // テスト発信先電話番号文字数チェック
    if (0 == demoTel.length || pCallConst.TARGET_TEL_LENGTH < demoTel.length || !$.isNumeric(demoTel)){
      alert('テスト発信先電話番号はハイフン無し数字のみで入力してください。');

      return false;
    }

    // テスト発信先メールアドレス文字数チェック
    if (0 == demoMail.length || pCallConst.TARGET_MAIL_LENGTH < demoMail.length || (0 < demoMail.length && !demoMail.match(/.+@.+\..+/g))){
      alert('テスト発信先メールアドレスを正しく入力してください。');

      return false;
    }

    return true;
  },

  // 件名入力チェック
  // isNotEnterCheck: 未入力チェックを行う場合はtrue
  isValidCallTitle: function(sendData){
    var callTitleLength = $.trim($('#callTitle').val()).length;
    // 未入力チェックを行う場合
    if (pCallConst.SEND_TYPE_TEL == sendData.sendType || pCallConst.SEND_TYPE_MAIL == sendData.sendType){
      if (0 == callTitleLength){
        alert('件名が入力されていません。');

        return false;
      }
    }
    // 文字数チェック
    if (pCallConst.CALL_TITLE_LENGTH < callTitleLength){
      alert('件名は' + pCallConst.CALL_TITLE_LENGTH + '文字以下で入力してください。');

      return false;
    }

    return true;
  }
};