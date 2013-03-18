<?php

/**
 * call actions.
 *
 * @package    OpenPNE
 * @subpackage main
 * @author     Your name here
 */
class callActions extends opJsonApiActions
{
  const MY_SELF = 0;
  const TEL_AND_MAIL = 1;
  const MAIL_ONLY = 2;

  public function preExecute()
  {
    $this->memberId = $this->getUser()->getMemberId();
  }

  // 現在の送信状況を取得する
  public function executeStatus(sfWebRequest $request)
  {
    $max = 10;
    if ($request->hasParameter('max'))
    {
      $max = $request['max'];
    }

    $renrakuBody = Doctrine::getTable('RenrakuBody')->getLatestRenrakuBody($max);

    $resultData = array();
    foreach ($renrakuBody as $line)
    {
      $tmpData = array();
      $tmpData['body'] = $line['body'];
      $tmpData['title'] = $line['title'];
      $tmpRenrakuMember = Doctrine::getTable('RenrakuMember')->retrieveByRenrakuId($line['id']);
      foreach ($tmpRenrakuMember as $memberLine)
      {
        $tmpData['target'][] = array(
          'id' => $memberLine['id'],
          'renraku_id' => $memberLine['renraku_id'],
          'boundio_id' => $memberLine['boundio_id'],
          'name' => $memberLine['name'],
          'mail' => $memberLine['mail'],
          'mail_id' => $memberLine['mail_id'],
          'mail_status' => $memberLine['mail_status'],
          'tel' => $memberLine['tel'],
          'tel_status' => $memberLine['tel_status'],
          'options' => $memberLine['options'],
          'created_at' => $memberLine['created_at'],
          'updated_at' => $memberLine['updated_at'],
        );
      }

      $resultData[] = $tmpData;
    }

    return $this->renderText(json_encode(array('status' => 'success', 'data' => $resultData)));
  }

  // 発信処理
  public function executeSend(sfWebRequest $request)
  {
    $this->forward400Unless($request['type'], 'type parameter not specified.');
    $this->forward400Unless($request['body'], 'body parameter not specified.');
    $this->forward400Unless($request['title'], 'title parameter not specified.');
    $this->forward400Unless($request['target'], 'target parameter not specified.');
    $type = $request['type'];
    $body = $request['body'];
    $title = $request['title'];
    $target = $request['target'];

    $renrakuBody = Doctrine::getTable('RenrakuBody')
      ->updateRenrakuBody(array('body' => $body, 'title' => $title));

    if (is_null($renrakuBody))
    {
      return $this->renderText(json_encode(array('status' => 'error', 'message' => 'could not be stored.')));
    }

    foreach ($target as $line)
    {
      $renrakuMember = array();
      $renrakuMember['renraku_id'] = $renrakuBody['id'];
      $renrakuMember['boundio_id'] = '';
      $renrakuMember['name'] = $line['name'];
      $renrakuMember['mail'] = $line['mail'];
      if (!is_null($renrakuMember['mail']))
      {
        $renrakuMember['mail_status'] = 'CALLWAITING';
      }
      elseif (self::MAIL_ONLY === (int)$type && is_null($renrakuMember['mail']))
      {
        return $this->renderText(json_encode(array('status' => 'error', 'message' => 'mail parameter not specified.')));
      }
      else
      {
        $renrakuMember['mail_status'] = 'NONE';
      }

      $renrakuMember['tel'] = $line['tel'];
      if (self::TEL_AND_MAIL === (int)$type || self::MY_SELF === (int)$type)
      {
        $renrakuMember['tel_status'] = 'CALLWAITING';
      }
      else
      {
        $renrakuMember['tel_status'] = 'NONE';
      }

      if (isset($line['options']))
      {
        $renrakuMember['options'] = $line['options'];
      }

      $renrakuMemberResult = Doctrine::getTable('RenrakuMember')
        ->updateRenrakuMember($renrakuMember);

      if (is_null($renrakuMemberResult))
      {
        return $this->renderText(json_encode(array('status' => 'error', 'message' => 'could not be stored.')));
      }
    }

    if (self::MAIL_ONLY !== $type)
    {
      RenrakumouUtil::process_tel();
    }
    RenrakumouUtil::process_mail();

    return $this->renderText(json_encode(array('status' => 'success', 'message' => 'executeSend DONE')));
  }

  // boundioの情報取得
  public function executeUpdate(sfWebRequest $request)
  {
    return $this->renderText(json_encode(array('status' => 'success', 'message' => 'executeUpdate DONE')));
  }

  // 月間コール数の取得
  public function executeCount(sfWebRequest $request)
  {
    return $this->renderText(json_encode(array('status' => 'success', 'message' => 'executeCount DONE', 'data' => array('tel_count' => 110, 'mail_count' => 290))));
  }






  public function executeCron(sfWebRequest $request)
  {
    if(1 != $this->getUser()->getMemberId()){
      return $this->renderText(json_encode(array("status" => "error","message"=> "ACL required")));
    }
    $mode = $request->getParameter("mode");
    switch($mode){
      case "update":
        //RenrakumouUtil::updatestatus_tel();
        break;
      case "process":
        //RenrakumouUtil::process_tel();
        //RenrakumouUtil::process_mail();
        break;
      case "boundio":
        //RenrakumouUtil::sync_boundio();
        break;
      case "test":
        break;
      case "all":
        //RenrakumouUtil::process_tel();
        //RenrakumouUtil::process_mail();
        //RenrakumouUtil::sync_boundio();
        //RenrakumouUtil::updatestatus_tel();
        break;
    }
    return $this->renderText(json_encode(array("status" => "success","message"=> "$mode mode DONE")));
  }
  public function executeDemo(sfWebRequest $request)
  {
  	$tel = $request->getParameter("tel");
  	$body = $request->getParameter("body");
  	$body = str_replace(array("\r\n","\r","\n"), '', $body);
		$result = RenrakumouUtil::pushcall($tel,$body,$_SERVER['userSerialId'],$_SERVER['appId'],$_SERVER['authKey']);
    //FIXME クライアントのJS側に、エラーパターンを伝える、入力値がおかしいのか？文章が長すぎるのか？サーバがおかしいのか？
		if($result){
      return $this->renderText(json_encode(array("status" => "success","tel" => $tel,"body" => $body, "result" => $result)));
    }else{
      return $this->renderText(json_encode(array("status" => "error","message" => " error")));      
    }
	}

  public function executeQueue(sfWebRequest $request)
  {
    $community_id = $request->getParameter("community_id","");
    $title = $request->getParameter("title");
    $body = $request->getParameter("body");

    $data = $request->getParameter("member_text");

    //FIXME Validationしよう
    $data = str_replace(array("\r\n","\r"), "\n", $data);

    $status = array();
    $status['title'] = $title;
    $status['body'] = $body;
    $status['date'] = date("Y/m/d");
    $status['community_id'] = $community_id;
    $status['status'] = "ACTIVE";
    $status['status_list'] = array();

    if(!$data){
      $this->logMessage('if(!$data)',"err");
      //FIXME HTTP400で返すべきではあるが、、、
      return $this->renderText(json_encode(array("status" => "error","message" => "parameter error")));
    }
    $data_list =  explode("\n" , $data);

    foreach($data_list as $line){
      $single = array();
      $line = str_replace(array("\t"," "), ' ', $line);

      list($nickname,$tel,$mail) = explode(" ",$line);
      if(!$nickname){
        continue; //PASS EMPTY
      }
      $single["boundio_id"] = "";
      $single["nickname"] = $nickname;
      $single["member_id"] = "";
      $single["tel"] = $tel;
      $single["telstat"] = "CALLWAITING";
      $single["mail"] = $mail;
      if($mail){
        $single["mailstat"] = "CALLWAITING";
      }else{
        $single["mailstat"] = "UNSENT";        
      }
      $status['status_list'][] = $single;
    }
    $public_pcall_status = json_decode(Doctrine::getTable('SnsConfig')->get('public_pcall_status'));
    if(null == $public_pcall_status){
      $public_pcall_status = array();
    }
    array_unshift($public_pcall_status,$status);
    Doctrine::getTable('SnsConfig')->set('public_pcall_status', json_encode($public_pcall_status));
    $result = true;
    if($result){
      return $this->renderText(json_encode(array("status" => "success","title" => $title,"body" => $body, "data" => $data, "result" => $result)));
    }else{
      return $this->renderText(json_encode(array("status" => "error","message" => " error")));      
    }
  }
}
