<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opRenrakumouMail
 *
 * @package    opRenrakumouPlugin
 * @author     tatsuya ichikawa <ichikawa@tejimaya.com>
 */
class opRenrakumouMail
{
  public static function processMail()
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

      $result = opRenrakumouMail::awsSES($line['mail'], null, $renrakuBody['title'], $body, sfConfig::get('op_smtpUserName'), sfConfig::get('op_smtpPassword'));
      $line['mail_id'] = $uniqid;
      if ($result)
      {
        $line['mail_status'] = 'CALLED';
      }
      else
      {
        $line['mail_status'] = 'FAIL';
      }

      $updateStatusResult = Doctrine::getTable('RenrakuMember')->updateStatus($line);
      if (is_null($updateStatusResult))
      {
        return false;
      }
    }

    return true;
  }

  public static function awsSES($to, $from, $subject, $body, $smtpUserName, $smtpPassword)
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
}
