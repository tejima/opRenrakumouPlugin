<?php

/**
 * call actions.
 *
 * @package    OpenPNE
 * @subpackage main
 * @author     Your name here
 */
class callActions extends sfActions
{
  public function executeCron(sfWebRequest $request)
  {
    if(!$this->getUser()->getMemberId()){
      return $this->renderText(json_encode(array("status" => "error","message"=> "ACL required")));
      //FIXME root権限のみ実行に制限する
    }
    $mode = $request->getParameter("mode");
    switch($mode){
      case "update":
        TejimayaBoundioUtil::update();
        break;
      case "process":
        TejimayaBoundioUtil::process();
        break;
      case "boundio":
        TejimayaBoundioUtil::boundio();
        break;
      case "test":
        break;
      case "all":
        TejimayaBoundioUtil::process();
        TejimayaBoundioUtil::boundio();
        TejimayaBoundioUtil::update();
        break;
    }

    return $this->renderText(json_encode(array("status" => "success","message"=> "$mode DONE")));
  }
  public function executeDemo(sfWebRequest $request)
  {
  	$tel = $request->getParameter("tel");
  	$body = $request->getParameter("body");
  	$body = str_replace(array("\r\n","\r","\n"), '', $body);
		$result = TejimayaBoundioUtil::pushcall($tel,$body,$_SERVER['userSerialId'],$_SERVER['appId'],$_SERVER['authKey']);
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

    //FIXME  Data Validationしよう

    $data = str_replace(array("\r\n","\r"), "\n", $data);

    $status = array();
    $status['title'] = $title;
    $status['body'] = $body;
    $status['date'] = "2013/02/26"; //FIXME
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