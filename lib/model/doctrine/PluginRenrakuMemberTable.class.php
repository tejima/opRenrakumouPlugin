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
 * @package    OpenPNE
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
    try
    {
      $q = $this->createQuery()->update();
      $q->set('boundio_id', '?', $renrakuMember['boundio_id']);
      $q->set('mail_id', '?', $renrakuMember['mail_id']);
      $q->set('mail_status', '?', $renrakuMember['mail_status']);
      $q->set('tel_status', '?', $renrakuMember['tel_status']);
      $q->where('id = ?', $renrakuMember['id']);

      return $q->execute();
    }
    catch (Exception $e)
    {
      sfContext::getInstance()->getLogger()->err('updateStatus()::'.$e, 'error');

      return null;
    }
  }

  static public function updateStatusMail($mailId)
  {
    $renrakuMember = Doctrine::getTable('RenrakuMember')->findOneByMailId($mailId);

    $result = false;

    if ('CALLED' === $renrakuMember['mail_status'])
    {
      $renrakuMember['mail_status'] = 'PUSH';

      $updateStatusResult = Doctrine::getTable('RenrakuMember')->updateStatus($renrakuMember);
      if (is_null($updateStatusResult))
      {
        $result = false;
      }
      else
      {
        $result = true;
      }
    }

    return $result;
  }

  static public function isValidMail($mailaddress)
  {
    $result = preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $mailaddress);
    if (false === $result || 0 === $result)
    {
      return false;
    }
    else
    {
      return true;
    }
  }
}
