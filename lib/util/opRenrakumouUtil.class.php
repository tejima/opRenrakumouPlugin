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
 */
class RenrakumouUtil
{
  static function updatestatus_tel()
  {
    $map = json_decode(Doctrine::getTable('SnsConfig')->get('boundio_status_map'), true);
    $public_pcall_status = json_decode(Doctrine::getTable('SnsConfig')->get('public_pcall_status'), true);
    if (null == $public_pcall_status)
    {
      $this->logMessage('public_pcall_status empty', 'err');
    }

    foreach ($public_pcall_status as &$ref_status)
    {
      foreach ($ref_status['status_list'] as &$ref_line)
      {
        if ('CALLPROCESSING' != $ref_line['telstat'])
        {
          continue;
        }
        if (!$map[(string)$ref_line['boundio_id']])
        {
          //echo 'MAP_NOT_HIT\n';
          continue;
        }
        else
        {
          //echo 'MAP_HIT\n';
        }
        if ('HUZAI1' == $ref_line['telstat'] && 'HUZAI' == $map[$ref_line['boundio_id']])
        {
          $ref_line['telstat'] = 'HUZAI2';
        }
        else
        {
          $ref_line['telstat'] = $map[$ref_line['boundio_id']];
        }
      }
    }
    unset($ref_status);
    unset($ref_line);
    Doctrine::getTable('SnsConfig')->set('public_pcall_status', json_encode($public_pcall_status));
	}

  static function updatestatus_mail($mail_id)
  {
		$public_pcall_status = json_decode(Doctrine::getTable('SnsConfig')->get('public_pcall_status'), true);
    if (null == $public_pcall_status)
    {
      $this->logMessage('public_pcall_status empty', 'err');
    }
    $result = false;
    foreach ($public_pcall_status as &$ref_status)
    {
      foreach ($ref_status['status_list'] as &$ref_line)
      {
        if ('CALLED' != $ref_line['mailstat'])
        {
          continue;
        }
        if ($mail_id == $ref_line['mail_id'])
        {
          $ref_line['mailstat'] = 'PUSH1';
          $result = true;
          break 2;
        }
      }
    }
    unset($ref_status);
    unset($ref_line);
    if ($result)
    {
	  	sfContext::getInstance()->getLogger()->debug('updatestatus_mail() match');
    }
    else
    {
	  	sfContext::getInstance()->getLogger()->debug('updatestatus_mail() unmatch');
    }

    Doctrine::getTable('SnsConfig')->set('public_pcall_status', json_encode($public_pcall_status));
    return $result;
	}

  static function sync_boundio()
  {
		$boundio_list = RenrakumouUtil::status_list(300, $_SERVER['userSerialId'], $_SERVER['appId'], $_SERVER['authKey']);
    if (!$boundio_list)
    {
      //echo 'Boundio access error';
    }

    $map = array();
    foreach ($boundio_list as $line)
    {
      $_status = '';
      if ('1' == (string)$line['_gather'])
      {
        $_status = 'PUSH1';
      }
      else
      {
        switch ($line['_status'])
        {
          case '架電完了':
            $_status = 'CALLED';
            break;
          case '不在':
            $_status = 'HUZAI';
            break;
        }
      }
      $map[(string)$line['_id']] = $_status;
    }

    Doctrine::getTable('SnsConfig')->set('boundio_status_raw',json_encode($boundio_list));
    Doctrine::getTable('SnsConfig')->set('boundio_status_map',json_encode($map));
	}

  static function process_tel()
  {
    $public_pcall_status = json_decode(Doctrine::getTable('SnsConfig')->get('public_pcall_status'), true);
    if(null == $public_pcall_status)
    {
			sfContext::getInstance()->getLogger()->info('public_pcall_status empty');
    }

    foreach($public_pcall_status as &$ref_status)
    {
      foreach($ref_status['status_list'] as &$ref_line)
      {
        if('CALLWAITING' != $ref_line['telstat'])
        {
    			continue;
    		}

    		$result = RenrakumouUtil::pushcall($ref_line['tel'], $ref_status['body'], $_SERVER['userSerialId'], $_SERVER['appId'], $_SERVER['authKey']);
        if ($result)
        {
    			$ref_line['telstat'] = 'CALLPROCESSING';
    			$ref_line['boundio_id'] = $result;
        }
        else
        {
          $ref_line['telstat'] = 'FAIL';
        }
    	}
    }
    unset($ref_status);
    unset($ref_line);

    sfContext::getInstance()->getLogger()->info('TASK DONE');
   	//print_r($public_pcall_status);
    Doctrine::getTable('SnsConfig')->set('public_pcall_status', json_encode($public_pcall_status));
	}

  static function pushcall($tel = null, $text = null, $userSerialId, $appId, $authKey)
  {
    Boundio::configure('userSerialId', $userSerialId);
    Boundio::configure('appId', $appId);
    Boundio::configure('authKey', $authKey);
    Boundio::configure('env', $_SERVER['boundioMode']);

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
		Boundio::configure('env', $_SERVER['boundioMode']);

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
		$public_pcall_status = json_decode(Doctrine::getTable('SnsConfig')->get('public_pcall_status'), true);
    if(null == $public_pcall_status)
    {
      $this->logMessage('public_pcall_status empty', 'err');
    }

    foreach($public_pcall_status as &$ref_status)
    {
      foreach ($ref_status['status_list'] as &$ref_line)
      {
        if ('CALLWAITING' != $ref_line['mailstat'])
        {
    			continue;
    		}
    		$uniqid = uniqid(null, true); //FIXME strict uniqueness
	  		$roger_url = sfConfig::get('op_base_url').'/o/roger?id='.$uniqid;
    		$body = <<< EOF
${ref_status['body']}


■了解報告■
下記リンクをクリックすることで、送信者に了解の報告ができます。
${roger_url}

連絡網サービス pCall
EOF;

    		$result = RenrakumouUtil::awsSES($ref_line['mail'], null, $ref_status['title'], $body, $_SERVER['smtpUsername'], $_SERVER['smtpPassword']);
    		$ref_line['mail_id'] = $uniqid;

        if ($result)
        {
    			sfContext::getInstance()->getLogger()->info($ref_line['mail'].' mailstat changed '.$ref_line['mailstat'].' => CALLED');
    			$ref_line['mailstat'] = 'CALLED';
        }
        else
        {
    			sfContext::getInstance()->getLogger()->info($ref_line['mail'].' mailstat changed '.$ref_line['mailstat'].' => FAIL');
          $ref_line['mailstat'] = 'FAIL';
        }
    	}
    }
    unset($ref_status);
    unset($ref_line);

    Doctrine::getTable('SnsConfig')->set('public_pcall_status', json_encode($public_pcall_status));
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
