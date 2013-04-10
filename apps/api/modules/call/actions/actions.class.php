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
 * @package    opRenrakumouPlugin
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
      $tmpRenrakuMember = Doctrine::getTable('RenrakuMember')->findByRenrakuId($line['id']);
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
    $type = (int)$request['type'];
    $body = $request['body'];
    $title = $request['title'];
    $target = $request['target'];

    $renrakuBody = Doctrine::getTable('RenrakuBody')
      ->updateRenrakuBody(array('body' => $body, 'title' => $title));

    if (is_null($renrakuBody))
    {
      return $this->renderText(json_encode(array('status' => 'error', 'message' => 'could not be stored.')));
    }
    else
    {
      $renrakuBody->save();
    }

    foreach ($target as $line)
    {
      $renrakuMember = array();
      $renrakuMember['renraku_id'] = $renrakuBody['id'];
      $renrakuMember['boundio_id'] = '';
      $renrakuMember['name'] = $line['name'];
      $renrakuMember['mail'] = $line['mail'];
      if (self::MAIL_ONLY === $type && (is_null($renrakuMember['mail']) || '' == $renrakuMember['mail']))
      {
        return $this->renderText(json_encode(array('status' => 'error', 'message' => 'mail parameter not specified.')));
      }

      if (false === opRenrakumouUtil::isValidMail($renrakuMember['mail']))
      {
        return $this->renderText(json_encode(array('status' => 'error', 'message' => 'mail parameter not alphanumeric.')));
      }

      if (!is_null($renrakuMember['mail']) && '' !== $renrakuMember['mail'])
      {
        $renrakuMember['mail_status'] = 'CALLWAITING';
      }
      else
      {
        $renrakuMember['mail_status'] = 'NONE';
      }

      $renrakuMember['tel'] = $line['tel'];
      if (is_null($renrakuMember['tel']) || '' == $renrakuMember['tel'])
      {
        return $this->renderText(json_encode(array('status' => 'error', 'message' => 'tel parameter not specified.')));
      }

      if (false === preg_match('/^[0-9]+$/', $renrakuMember['tel']))
      {
        return $this->renderText(json_encode(array('status' => 'error', 'message' => 'tel parameter not alphanumeric.')));
      }

      if (self::TEL_AND_MAIL === $type || self::MY_SELF === $type)
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
      opRenrakumouUtil::processTel();
    }
    opRenrakumouUtil::processMail();

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
}
