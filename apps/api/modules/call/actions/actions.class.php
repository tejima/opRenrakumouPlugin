<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * call actions.
 *
 * @package    OpenPNE
 * @author     tatsuya ichikawa <ichikawa@tejimaya.com>
 */
class callActions extends opJsonApiActions
{
  const MY_SELF = 99;
  const TEL_AND_MAIL = 1;
  const MAIL_ONLY = 2;

  public function preExecute()
  {
    $this->memberId = $this->getUser()->getMemberId();
  }

  // 現在の送信状況を取得する
  public function executeStatus(sfWebRequest $request)
  {
    $max = $request->getParameter('max', 10);

    $renrakuBody = Doctrine::getTable('RenrakuBody')->getLatestRenrakuBody($max);

    $resultData = array();
    foreach ($renrakuBody as $line)
    {
      $tmpData = array();
      $tmpData['body'] = $line['body'];
      $tmpData['title'] = $line['title'];
      $tmpRenrakuMember = Doctrine::getTable('RenrakuMember')->findByRenrakuId($line['id']);
      foreach ($tmpRenrakuMember as $memberLine)
      {
        $tmpData['target'][] = array(
          'id' => $memberLine['id'],
          'name' => $memberLine['name'],
          'mail' => $memberLine['mail'],
          'mail_status' => $memberLine['mail_status'],
          'tel' => $memberLine['tel'],
          'tel_status' => $memberLine['tel_status'],
          'options' => $memberLine['options'],
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
    $titleAndBodyMaxLength = 200;
    $nameMaxLength = 64;
    $mailMaxLength = 255;

    if (!is_numeric($type))
    {
      return $this->renderText(json_encode(array('status' => 'error', 'message' => 'type is not numeric.')));
    }

    $con = Doctrine::getTable('RenrakuBody')->getConnection();
    $con->beginTransaction();
    try
    {
      $renrakuBody = new RenrakuBody();

      if ($titleAndBodyMaxLength < mb_strlen($body, 'utf-8') || 1 > mb_strlen($body, 'utf-8'))
      {
        return $this->renderText(json_encode(array('status' => 'error', 'message' => 'body can not be set in more than '.$titleAndBodyMaxLength.' characters.')));
      }

      if ($titleAndBodyMaxLength < mb_strlen($title, 'utf-8') || 1 > mb_strlen($title, 'utf-8'))
      {
        return $this->renderText(json_encode(array('status' => 'error', 'message' => 'title can not be set in more than '.$titleAndBodyMaxLength.' characters.')));
      }

      $renrakuBody->setBody($this->convertDoubleByteCharacter($body));
      $renrakuBody->setTitle($title);
      $renrakuBody->save();

      foreach ($target as $line)
      {
        $renrakuMember = new RenrakuMember();;
        $renrakuMember->setRenraku_id($renrakuBody['id']);
        $renrakuMember->setBoundio_id('');

        // name valid
        if ($nameMaxLength < mb_strlen($line['name']) || 1 > mb_strlen($line['name']))
        {
          return $this->renderText(json_encode(array('status' => 'error', 'message' => 'name can not be set in more than '.$nameMaxLength.' characters.')));
        }
        $renrakuMember->setName($line['name']);

        // mail & mail_status valid
        if ($mailMaxLength < mb_strlen($line['mail']) || 6 > mb_strlen($line['mail']))
        {
          return $this->renderText(json_encode(array('status' => 'error', 'message' => 'mail can not be set in more than '.$mailMaxLength.' characters.')));
        }

        if (!is_null($line['mail']) || '' !== $line['mail'])
        {
          if (false === PluginRenrakuMemberTable::isValidMail($line['mail']) || 0 === PluginRenrakuMemberTable::isValidMail($line['mail']))
          {
            return $this->renderText(json_encode(array('status' => 'error', 'message' => 'mail parameter not alphanumeric.')));
          }

          $renrakuMember->setMail_status('CALLWAITING');
        }
        else
        {
          if (self::MAIL_ONLY === $type)
          {
            return $this->renderText(json_encode(array('status' => 'error', 'message' => 'mail parameter not specified.')));
          }

          $renrakuMember->setMail_status('NONE');
        }
        $renrakuMember->setMail($line['mail']);

        // tel valid
        if (is_null($line['tel']) || '' == $line['tel'])
        {
          return $this->renderText(json_encode(array('status' => 'error', 'message' => 'tel parameter not specified.')));
        }

        if (false === preg_match('/^0\d{9,10}$/', $line['tel']))
        {
          return $this->renderText(json_encode(array('status' => 'error', 'message' => 'tel parameter not alphanumeric.')));
        }
        $renrakuMember->setTel($line['tel']);

        if (self::TEL_AND_MAIL === $type || self::MY_SELF === $type)
        {
          $renrakuMember->setTel_status('CALLWAITING');
        }
        else
        {
          $renrakuMember->setTel_status('NONE');
        }

        if (isset($line['options']))
        {
          $renrakuMember->setOptions($line['options']);
        }

        $renrakuMember->save();
      }
      $con->commit();
    }
    catch (Exception $e)
    {
      $con->rollback();
      sfContext::getInstance()->getLogger()->err('executeSend', 'error');

      return $this->renderText(json_encode(array('status' => 'error', 'message' => 'could not be stored.')));
    }

    if (self::MAIL_ONLY !== $type)
    {
      $processTelResult = opRenrakumouUtil::processTel();
      if (!$processTelResult)
      {
        return $this->renderText(json_encode(array('status' => 'error', 'message' => 'processTel Error')));
      }
    }

    $processMailResult = opRenrakumouMail::processMail();
    if (!$processMailResult)
    {
      return $this->renderText(json_encode(array('status' => 'error', 'message' => 'processMail Error')));
    }

    return $this->renderText(json_encode(array('status' => 'success', 'message' => 'executeSend DONE')));
  }

  // boundioの情報取得
  public function executeUpdate(sfWebRequest $request)
  {
    $result = opRenrakumouUtil::syncBoundio();
    if ($result)
    {
      return $this->renderText(json_encode(array('status' => 'success', 'message' => 'executeUpdate DONE')));
    }
    else
    {
      return $this->renderText(json_encode(array('status' => 'error', 'message' => 'Update Error')));
    }
  }

  // 月間コール数の取得
  public function executeCount(sfWebRequest $request)
  {
    $count = Doctrine::getTable('RenrakuMember')->getMonthlyCalled();
    if (!is_null($count['tel_count']) && !is_null($count['mail_count']))
    {
      return $this->renderText(json_encode(array('status' => 'success', 'message' => 'executeCount DONE', 'data' => array('tel_count' => $count['tel_count'], 'mail_count' => $count['mail_count']))));
    }
    else
    {
      return $this->renderText(json_encode(array('status' => 'error', 'message' => 'Count Error')));
    }
  }

  private function convertDoubleByteCharacter($text)
  {
    $returnText = mb_convert_kana($text, 'A');
    $returnText = str_replace('"', '”', $returnText);
    $returnText = str_replace("'", '’', $returnText);

    return $returnText;
  }
}
