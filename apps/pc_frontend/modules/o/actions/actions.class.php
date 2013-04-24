<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * oActions
 *
 * @package    OpenPNE
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
    $mailId = $request->getParameter('id', '');

    if ($mailId)
    {
      $result = PluginRenrakuMemberTable::updateStatusMail($mailId);
      sfContext::getInstance()->getLogger()->debug('PluginRenrakuMemberTable::updateStatusMail(): '.$result);

      return $this->renderText('了解確認を送信者に報告しました。');
    }
    else
    {
      return $this->renderText('報告できませんでした。');
    }
  }
}
