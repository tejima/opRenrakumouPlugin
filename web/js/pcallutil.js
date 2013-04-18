/**
 * 連絡網プラグインユーティリティ
 *
 * @type {{replaceString: Function, parseTarget: Function}}
 */
var pCallUtil = {
  /**
   * 文字列の置換を行う
   *
   * @param text 置換元の文字列
   * @param breakChar 改行コードの置換文字列
   * @param spaceChar 空白の置換文字列
   * @param isReplaceNumber 全角数字を半角数字に置換数場合はtrue
   * @return {string} 置換結果の文字列
   */
  replaceString: function(text, breakChar, spaceChar, isReplaceNumber){
    if (null == breakChar || 'undefined' == typeof(breakChar)){
      breakChar = '';
    }
    if (null == spaceChar || 'undefined' == typeof(spaceChar)){
      spaceChar = '';
    }
    text = text.replace(/\r\n/g, breakChar);
    text = text.replace(/(\n|\r)/g, breakChar);
    text = text.replace(/　/g, spaceChar);
    text = text.replace(/ /g, spaceChar);

    if (isReplaceNumber){
      text = text.replace(/[０-９]/g, function(s) {
        return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
      });
    }

    return text;
  },

  /**
   * 入力された連絡先の分解
   * @param targetText 入力された連絡先
   * @returns {Array} 分解した連絡先
   */
  parseTarget: function(targetText){
    // 入力された連絡先の改行コード等の置換
    targetText = this.replaceString(targetText, '<>', ' ', true);

    // 連絡先情報を改行毎に区切る
    var targetList = targetText.split('<>');
    var len = targetList.length;
    var targets = [];
    for (var index = 0; index < len; index++){
      var target = targetList[index];
      // 連絡先1件を名前、電話番号、メールアドレスに分ける
      var targetInfo = target.split(' ');
      targets.push(targetInfo);
    }

    return targets;
  }
};
