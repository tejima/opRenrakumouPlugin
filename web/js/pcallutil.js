// 連絡網プラグインユーティリティ
var pCallUtil = {
  replaceSpaceChar: function(text){
    text = text.replace(/\r\n/g, '');
    text = text.replace(/(\n|\r)/g, '');
    text = text.replace(/　/g, '');
    text = text.replace(/ /g, '');

    return text;
  }
};
