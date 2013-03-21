<?php

class Japanese_Mail extends Zend_Mail{

  function __construct(){
    parent::__construct('ISO-2022-JP');
  }

  function setBodyText( $txt , $charset = null , $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE ){
    parent::setBodyText( mb_convert_encoding($txt, 'ISO-2022-JP', 'UTF-8') , $charset , $encoding );
  }

  function setSubject( $txt ){
    parent::setSubject( mb_convert_encoding($txt, 'ISO-2022-JP', 'UTF-8' ));
  }

  function setTo($a,$b){
    parent::setTo($a,mb_encode_mimeheader(mb_convert_encoding($b, 'ISO-2022-JP', 'UTF-8'),'ISO-2022-JP'));
  }

  public function setFrom($email, $name = null){
    $name = mb_encode_mimeheader(mb_convert_encoding($name, 'ISO-2022-JP', 'UTF-8'),'ISO-2022-JP');
    sfContext::getInstance()->getLogger()->debug('setFrom() email: ' . $email);
    parent::setFrom($email,$name);
  }
}
