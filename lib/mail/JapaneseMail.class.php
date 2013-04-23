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
 * @author     Mamoru Tejima <tejima@tejimaya.com>
 * @author     tatsuya ichikawa <ichikawa@tejimaya.com>
 */

class JapaneseMail extends Zend_Mail
{

  public function __construct()
  {
    parent::__construct('ISO-2022-JP');
  }

  public function setBodyText($txt, $charset = 'ISO-2022-JP', $encoding = Zend_Mime::ENCODING_7BIT)
  {
    parent::setBodyText(mb_convert_encoding($txt, 'JIS', 'UTF-8'), $charset, $encoding);
  }

  public function setSubject($txt)
  {
    parent::setSubject(mb_encode_mimeheader($txt, 'ISO-2022-JP'));
  }

  public function setTo($a, $b)
  {
    parent::setTo($a, mb_encode_mimeheader(mb_convert_encoding($b, 'ISO-2022-JP', 'UTF-8'),'ISO-2022-JP'));
  }

  public function setFrom($email, $name = null)
  {
    $name = mb_encode_mimeheader(mb_convert_encoding($name, 'ISO-2022-JP', 'UTF-8'),'ISO-2022-JP');
    sfContext::getInstance()->getLogger()->debug('setFrom() email: '.$email);
    parent::setFrom($email, $name);
  }
}
