<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opRenrakumouUtil
 *
 * @package    opRenrakumouPlugin
 * @author     Mamoru Tejima <tejima@tejimaya.com>
 * @author     tatsuya ichikawa <ichikawa@tejimaya.com>
 */
class opRenrakumouUtil
{
  static function syncBoundio()
  {
    $boundioList = opRenrakumouUtil::statusList(100, sfConfig::get('op_userSerialId'), sfConfig::get('op_appId'), sfConfig::get('op_authKey'));
    if (!$boundioList)
    {
      sfContext::getInstance()->getLogger()->err('boundioList empty', 'error');

      return false;
    }

    foreach ($boundioList as $line)
    {
      $status = '';
      if (isset($line['_gather']) && '1' == (string)$line['_gather'])
      {
        $status = 'PUSH';
      }
      else
      {
        switch ($line['_status'])
        {
          case '架電完了':
            $status = 'CALLED';
            break;
          case '架電待機':
            $status = 'CALLPROCESSING';
            break;
          case '不在':
            $status = 'HUZAI';
            break;
          default :
            $status = 'FAIL';
            break;
        }
      }

      $renrakuMember = Doctrine::getTable('RenrakuMember')->findOneByBoundioId($line['_id']);
      if ('' !== $status && false !== $renrakuMember)
      {
        $renrakuMember['tel_status'] = $status;
        $updateStatusResult = Doctrine::getTable('RenrakuMember')->updateStatus($renrakuMember);
        if (is_null($updateStatusResult))
        {
          return false;
        }
      }
    }

    return true;
  }

  static function processTel()
  {
    $callWaitingList = Doctrine::getTable('RenrakuMember')->findByTelStatus('CALLWAITING');
    foreach ($callWaitingList as $line)
    {
      $renrakuBody = Doctrine::getTable('RenrakuBody')->find($line['renraku_id']);
      $result = opRenrakumouUtil::pushCall($line['tel'], $renrakuBody['body'], sfConfig::get('op_userSerialId'), sfConfig::get('op_appId'), sfConfig::get('op_authKey'));
      if ($result)
      {
        $line['tel_status'] = 'CALLPROCESSING';
        $line['boundio_id'] = $result;
      }
      else
      {
        $line['tel_status'] = 'FAIL';
      }
      $line['mail_id'] = '';

      $updateStatusResult = Doctrine::getTable('RenrakuMember')->updateStatus($line);
      if (is_null($updateStatusResult))
      {
        return false;
      }
    }

    return true;
  }

  static function pushCall($tel = null, $text = null, $userSerialId, $appId, $authKey)
  {
    Boundio::configure('userSerialId', $userSerialId);
    Boundio::configure('appId', $appId);
    Boundio::configure('authKey', $authKey);
    Boundio::configure('env', sfConfig::get('op_boundioMode'));

    $str = 'silent()%%silent()%%silent()%%file_d('.$text.',1)%%silent()%%file_d(この件に了解であれば1を、不明な場合は0をプッシュしてください。,1)%%gather(20,1)%%file_d(連絡は以上です。,1)';

    $result = Boundio::call($tel, $str);
    sfContext::getInstance()->getLogger()->debug('Boundio::call() :'.print_r($result, true));
    if ('true' === $result['success'])
    {
      return $result['_id'];
    }
    else
    {
      return false;
    }
  }

  static function statusList($num = 100, $userSerialId, $appId, $authKey)
  {
    Boundio::configure('userSerialId', $userSerialId);
    Boundio::configure('appId', $appId);
    Boundio::configure('authKey', $authKey);
    Boundio::configure('env', sfConfig::get('op_boundioMode'));

    $result = Boundio::status(null, date('Ymd', strtotime('-2 days')), date('Ymd', strtotime('+1 days')), $num);
    sfContext::getInstance()->getLogger()->debug('Boundio::status() :'.print_r($result, true));

    if (true == $result[0]['success'])
    {
      return $result[0]['result'];
    }
    else
    {
      return false;
    }
  }
}
