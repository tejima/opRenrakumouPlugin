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
  SetEnv userSerialId  #BOUNDIO
  SetEnv appId  #BOUNDIO
  SetEnv authKey  #BOUNDIO
  SetEnv smtpUsername #有効なgmailアカウント
  SetEnv smtpPassword #有効なgmailアカウント
</pre>

イメージ
----

<img src="http://p.pne.jp/d/500/201303011422.png">

