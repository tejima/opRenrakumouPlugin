opRenrakumouPlugin
==================

小学校の連絡網／緊急連絡網を実現するOpenPNEプラグイン

・コミュニティトピックを立ち上げると参加メンバーの携帯電話に一斉配信する


インストール環境
----
・opChatTaskPlugin が必要です
・opSkinThemePluginが必要です

設定方法
----
・Apache ENV 環境変数で以下の値をセットする必要があります

<pre>
  SetEnv boundioMode develop # develop / production
  SetEnv userSerialId  #BOUNDIO
  SetEnv appId  #BOUNDIO
  SetEnv authKey  #BOUNDIO
  SetEnv smtpUsername #有効なgmailアカウント
  SetEnv smtpPassword #有効なgmailアカウント
</pre>

動作イメージ
----
<img src="http://p.pne.jp/d/500/201303011422.png">


改訂履歴
----
- v0.3.1
 - デモ電話モード
 - 基本ユーザーインターフェース
 - 音声自動合成
- v0.5.0
 - 電話一斉コール
 - メール一斉コール
 - メール了解機能
 - 電話了解機能
 - 同じ内容でコールを作り直す機能
 - 開発・本番切り替え

TODO
----
- [ ] 件数リミット
- [ ] 発信履歴ログ出力機能
- [ ] 学校・学年一括配信
- [ ] 紹介ボーナス機能




