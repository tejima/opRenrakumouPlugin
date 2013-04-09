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
  static function updatestatusMail($mailId)
  {
    $renrakuMember = Doctrine::getTable('RenrakuMember')->findByMailId($mailId);

    $result = false;
    foreach ($renrakuMember as $line)
    {
      if ('CALLED' === $line['mail_status'])
      {
        $line['mail_status'] = 'PUSH';
        Doctrine::getTable('RenrakuMember')->updateStatus($line);
        $result = true;
      }
    }

    return $result;
  }

  static function syncBoundio()
  {
    $boundioList = opRenrakumouUtil::statusList(300, sfConfig::get('op_userSerialId'), sfConfig::get('op_appId'), sfConfig::get('op_authKey'));
    if (!$boundioList)
    {
      sfContext::getInstance()->getLogger()->err('boundioList empty', 'error');

      return false;
    }

    foreach ($boundioList as $line)
    {
      $status = '';
      if ('1' == (string)$line['_gather'])
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
      $renrakuMember = Doctrine::getTable('RenrakuMember')->findByBoundioId($line['_id']);
      foreach ($renrakuMember as $memberLine)
      {
        if ('' !== $status)
        {
          $memberLine['tel_status'] = $status;
          Doctrine::getTable('RenrakuMember')->updateStatus($memberLine);
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
      Doctrine::getTable('RenrakuMember')->updateStatus($line);
    }
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
    if (true == $result['success'])
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

    $result = Boundio::status(null, date('Ymd', strtotime('-2 days')), date('Ymd', strtotime('-1 days')), $num);
    sfContext::getInstance()->getLogger()->debug('Boundio::call() :'.print_r($result, true));

    if (true == $result[0]['success'])
    {
      return $result[0]['result'];
    }
    else
    {
      return false;
    }
  }

  static function processMail()
  {
    $callWaitingList = Doctrine::getTable('RenrakuMember')->findByMailStatus('CALLWAITING');
    foreach ($callWaitingList as $line)
    {
      $renrakuBody = Doctrine::getTable('RenrakuBody')->find($line['renraku_id']);
      $uniqid = uniqid(rand(), true);
      $rogerUrl = sfConfig::get('op_base_url').'/o/roger?id='.$uniqid;
      $body = <<< EOF
${renrakuBody['body']}


■了解報告■
下記リンクをクリックすることで、送信者に了解の報告ができます。
${rogerUrl}

連絡網サービス pCall
EOF;

      $result = opRenrakumouUtil::awsSES($line['mail'], null, $renrakuBody['title'], $body, sfConfig::get('op_smtpUserName'), sfConfig::get('op_smtpPassword'));
      $line['mail_id'] = $uniqid;
      if ($result)
      {
        $line['mail_status'] = 'CALLED';
      }
      else
      {
        $line['mail_status'] = 'FAIL';
      }
      Doctrine::getTable('RenrakuMember')->updateStatus($line);
    }
  }

  static function awsSES($to, $from, $subject, $body, $smtpUserName, $smtpPassword)
  {
    if (!$to)
    {
      return false;
    }
    if (!$from)
    {
      $from = 'noreply@pne.jp';
    }
    $config = array('ssl' => 'ssl',
                'auth' => 'login',
                'username' => $smtpUserName,
                'password' => $smtpPassword,
                'port' => 465,
              );

    $host = 'smtp.gmail.com';

    $transport = new Zend_Mail_Transport_Smtp($host, $config);
    try
    {
      $mail = new JapaneseMail();
      $mail->setBodyText($body);
      $mail->setFrom($from, '連絡網 pCall');
      $mail->addTo($to);
      $mail->setSubject($subject);
      $mail->send($transport);

      return true;
    }
    catch (Exception $e)
    {
      sfContext::getInstance()->getLogger()->err('failed to send mail', 'error');
      return false;
    }
  }

  static function isValidMail($mailaddress) {
      return preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $mailaddress);
  }
}
