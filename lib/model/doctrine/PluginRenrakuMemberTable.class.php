<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * PluginRenrakuMemberTable
 *
 * @package    opRenrakumouPlugin
 * @author     tatsuya ichikawa <ichikawa@tejimaya.com>
 */
class PluginRenrakuMemberTable extends Doctrine_Table
{
  public function getMonthlyCalled()
  {
    $nowDate = getdate();
    $year = $nowDate['year'];
    $month = $nowDate['mon'];
    if (1 <= strlen($month))
    {
      $month = sprintf('%02d', $nowDate['mon']);
    }

    $telCount = $this->createQuery()
      ->where('DATE_FORMAT(created_at, "%Y%m") = ?', $year.$month)
      ->andWhere('(tel_status = "CALLED" OR tel_status = "PUSH")')
      ->count();

    $mailCount = $this->createQuery()
      ->where('DATE_FORMAT(created_at, "%Y%m") = ?', $year.$month)
      ->andWhere('(mail_status = "CALLED" OR mail_status = "PUSH")')
      ->count();

    return array('tel_count' => $telCount, 'mail_count' => $mailCount);
  }

  public function updateStatus($renrakuMember)
  {
    $q = $this->createQuery()->update();
    $q->set('boundio_id', '?', $renrakuMember['boundio_id']);
    $q->set('mail_id', '?', $renrakuMember['mail_id']);
    $q->set('mail_status', '?', $renrakuMember['mail_status']);
    $q->set('tel_status', '?', $renrakuMember['tel_status']);
    $q->where('id = ?', $renrakuMember['id']);

    return $q->execute();
  }

  public function insertRenrakuMember($renrakuMember = array())
  {
    $object = new RenrakuMember();

    if (isset($renrakuMember['renraku_id']))
    {
      $object->setRenrakuId($renrakuMember['renraku_id']);
    }
    else
    {
      sfContext::getInstance()->getLogger()->err('renraku_id is not specified.', 'error');

      return null;
    }

    if (isset($renrakuMember['boundio_id']))
    {
      $object->setBoundioId($renrakuMember['boundio_id']);
    }
    else
    {
      sfContext::getInstance()->getLogger()->err('boundio_id is not specified.', 'error');

      return null;
    }

    if (isset($renrakuMember['name']))
    {
      $object->setName($renrakuMember['name']);
    }
    else
    {
      sfContext::getInstance()->getLogger()->err('name is not specified.', 'error');

      return null;
    }

    if (isset($renrakuMember['mail']))
    {
      $object->setMail($renrakuMember['mail']);
    }

    if (isset($renrakuMember['mail_id']))
    {
      $object->setMailId($renrakuMember['mail_id']);
    }

    if (isset($renrakuMember['mail_status']))
    {
      $object->setMailStatus($renrakuMember['mail_status']);
    }

    if (isset($renrakuMember['tel']))
    {
      $object->setTel($renrakuMember['tel']);
    }
    else
    {
      sfContext::getInstance()->getLogger()->err('tel is not specified.', 'error');

      return null;
    }

    if (isset($renrakuMember['tel_status']))
    {
      $object->setTelStatus($renrakuMember['tel_status']);
    }

    if (isset($renrakuMember['options']))
    {
      $object->setOptions($renrakuMember['options']);
    }

    $object->save();

    return $object;
  }

  static public function updateStatusMail($mailId)
  {
    $renrakuMember = Doctrine::getTable('RenrakuMember')->findOneByMailId($mailId);

    $result = false;

    if ('CALLED' === $renrakuMember['mail_status'])
    {
      $renrakuMember['mail_status'] = 'PUSH';
      Doctrine::getTable('RenrakuMember')->updateStatus($renrakuMember);
      $result = true;
    }

    return $result;
  }

  static public function isValidMail($mailaddress)
  {
      return preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $mailaddress);
  }
}
