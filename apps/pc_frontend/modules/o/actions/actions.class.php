<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * RenrakumouActions
 *
 * @package    opRenrakumouPlugin
 * @author     Mamoru Tejima <tejima@tejimaya.com>
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
    $mail_id = $request->getParameter('id', '');

    if ($mail_id)
    {
      $result = RenrakumouUtil::updatestatus_mail($mail_id);
      sfContext::getInstance()->getLogger()->debug("RenrakumouUtil::updatestatus_mail(): $result");

      return $this->renderText('了解確認を送信者に報告しました。');
    }
    else
    {
      return $this->renderText('報告できませんでした。');
    }
  }
}
