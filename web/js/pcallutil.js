// 連絡網プラグインユーティリティ
var pCallUtil = {
  replaceSpaceChar: function(text){
    text = text.replace(/\r\n/g, '');
    text = text.replace(/(\n|\r)/g, '');
    text = text.replace(/　/g, '');
    text = text.replace(/ /g, '');

    return text;
  },

  // 連絡先リストの分解
  parseTarget: function()
  {
    var targetValue = $.trim($('#directTarget').val());
    // 改行コードの置換
    targetValue = targetValue.replace(/\r\n/g, '<>');
    targetValue = targetValue.replace(/(\n|\r)/g, '<>');
    // 全角空白の置換
    targetValue = targetValue.replace(/　/g, ' ');
    // 全角数字の置換
    targetValue = targetValue.replace(/[０-９]/g, function(s) {
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
};
