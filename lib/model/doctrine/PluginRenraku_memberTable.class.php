<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * PluginRenraku_member
 *
 * @package    opRenrakumouPlugin
 * @author     tatsuya ichikawa <ichikawa@tejimaya.com>
 */
class PluginRenraku_memberTable extends Doctrine_Table
{
  public function retrieveByRenrakuId($renrakuId)
  {
    return $this->createQuery()
      ->where('renraku_id = ?', $renrakuId)
      ->execute();
  }

  public function getMonthlyCalled($year, $month)
  {
    if (2 > strlen($month))
    {
      $month = '0'.$month;
    }

    return $this->createQuery()
      ->where('DATE_FORMAT(created_at, "%Y%m") = ?', $year.$month)
      ->andWhere('tel_status = "CALLED"')
      ->count();
  }

  public function updateRenrakuMember($renrakuMember = array())
  {
    $object = new Renraku_member();

    if (isset($renrakuMember['renraku_id']))
    {
      $object->setRenrakuId($renrakuMember['renraku_id']);
    }
    else
    {
      throw new LogicException('renraku_id is not specified.');
    }

    if (isset($renrakuMember['boundio_id']))
    {
      $object->setBoundioId($renrakuMember['boundio_id']);
    }
    else
    {
      throw new LogicException('boundio_id is not specified.');
    }

    if (isset($renrakuMember['name']))
    {
      $object->setName($renrakuMember['name']);
    }
    else
    {
      throw new LogicException('name is not specified.');
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
      throw new LogicException('tel is not specified.');
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
}
