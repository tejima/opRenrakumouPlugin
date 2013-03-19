<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * RenrakumouUtil
 *
 * @package    opRenrakumouPlugin
 * @author     Mamoru Tejima <tejima@tejimaya.com>
 * @author     tatsuya ichikawa <ichikawa@tejimaya.com>
 */
class RenrakumouUtil
{
  static function updatestatus_mail($mail_id)
  {
    $renrakuMember = Doctrine::getTable('RenrakuMember')->findByMailId($mail_id);

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

  static function sync_boundio()
  {
		$boundio_list = RenrakumouUtil::status_list(300, sfConfig::get('op_userSerialId'), sfConfig::get('op_appId'), sfConfig::get('op_authKey'));
    if (!$boundio_list)
    {
      $this->logMessage('boundio_list empty', 'err');
      return false;
    }

    $map = array();
    foreach ($boundio_list as $line)
    {
      $_status = '';
      if ('1' == (string)$line['_gather'])
      {
        $_status = 'PUSH';
      }
      else
      {
        switch ($line['_status'])
        {
          case '架電完了':
            $_status = 'CALLED';
            break;
          case '架電待機':
            $_status = 'CALLPROCESSING';
            break;
          case '不在':
            $_status = 'HUZAI';
            break;
          default :
            $_status = 'FAIL';
        }
      }
      $renrakuMember = Doctrine::getTable('RenrakuMember')->findByBoundioId($line['_id']);
      foreach ($renrakuMember as $memberLine)
      {
        if ('' !== $_status)
        {
          $memberLine['tel_status'] = $_status;
          Doctrine::getTable('RenrakuMember')->updateStatus($memberLine);
        }
      }
    }
    return true;
	}

  static function process_tel()
  {
    $callWaitingList = Doctrine::getTable('RenrakuMember')->findByTelStatus('CALLWAITING');
    foreach ($callWaitingList as $line)
    {
      $renrakuBody = Doctrine::getTable('RenrakuBody')->find($line['renraku_id']);
      $result = RenrakumouUtil::pushcall($line['tel'], $renrakuBody['body'], sfConfig::get('op_userSerialId'), sfConfig::get('op_appId'), sfConfig::get('op_authKey'));
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

  static function pushcall($tel = null, $text = null, $userSerialId, $appId, $authKey)
  {
    Boundio::configure('userSerialId', $userSerialId);
    Boundio::configure('appId', $appId);
    Boundio::configure('authKey', $authKey);
    Boundio::configure('env', sfConfig::get('op_boundioMode'));

    $str = 'silent()%%silent()%%silent()%%file_d('.$text.',1)%%silent()%%file_d(この件に了解であれば1を、不明な場合は0をプッシュしてください。,1)%%gather(20,1)%%file_d(連絡は以上です。,1)';

    $result = Boundio::call($tel, $str);
    sfContext::getInstance()->getLogger()->debug('Boundio::call() :'.print_r($result, true));
    //FIXME Boundioのエラーパターン位基づいて、クライアントにエラーを通知する
    if ('true' == $result['success'])
    {
      return $result['_id'];
    }
    else
    {
      return false;
    }
  }

  static function status_list($num = 100, $userSerialId, $appId, $authKey)
  {
		Boundio::configure('userSerialId', $userSerialId);
		Boundio::configure('appId', $appId);
		Boundio::configure('authKey', $authKey);
		Boundio::configure('env', sfConfig::get('op_boundioMode'));

		$result = Boundio::status(null, date('Ymd',strtotime('-2 days')), date('Ymd',strtotime('-1 days')), $num);
		sfContext::getInstance()->getLogger()->debug('Boundio::call() :'.print_r($result, true));

    if ('true' == $result[0]['success'])
    {
			return $result[0]['result'];
    }
    else
    {
			return false;
		}
	}

  static function process_mail()
  {
    $callWaitingList = Doctrine::getTable('RenrakuMember')->findByMailStatus('CALLWAITING');
    foreach ($callWaitingList as $line)
    {
      $renrakuBody = Doctrine::getTable('RenrakuBody')->find($line['renraku_id']);
      $uniqid = uniqid(rand(), true);
      $roger_url = sfConfig::get('op_base_url').'/o/roger?id='.$uniqid;
      $body = <<< EOF
${renrakuBody['body']}


■了解報告■
下記リンクをクリックすることで、送信者に了解の報告ができます。
${roger_url}

連絡網サービス pCall
EOF;

      $result = RenrakumouUtil::awsSES($line['mail'], null, $renrakuBody['title'], $body, sfConfig::get('op_smtpUsername'), sfConfig::get('op_smtpPassword'));
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

  static function awsSES($to, $from, $subject, $body, $smtpUsername, $smtpPassword)
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
                'username' => $smtpUsername,
                'password' => $smtpPassword,
                'port' => 465
              );

//		$host = 'email-smtp.us-east-1.amazonaws.com';
		$host = 'smtp.gmail.com';

		$transport = new Zend_Mail_Transport_Smtp($host, $config);
    try
    {
			$mail = new Japanese_Mail();
			$mail->setBodyText($body);
			$mail->setFrom($from, '連絡網 pCall');
			$mail->addTo($to);
			$mail->setSubject($subject);
			$mail->send($transport);
			return true;
    }
    catch (Exception $e)
    {
			return false;
		}
	}
}
