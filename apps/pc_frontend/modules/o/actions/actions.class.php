<?php

/**
 * o actions.
 *
 * @package    OpenPNE
 * @subpackage o
 * @author     Your name here
 */
class oActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeRoger(sfWebRequest $request)
  {
  	$mail_id = $request->getParameter("id","");

  	if($mail_id){
	  	$result = RenrakumouUtil::update_mail($mail_id);
	  	sfContext::getInstance()->getLogger()->debug("RenrakumouUtil::update_mail(): $result");
	    return $this->renderText("了解確認を送信者に報告しました。");
  	}else{
  		return $this->renderText("確認できません。エラーコード:4192");
  	}
  }
}
