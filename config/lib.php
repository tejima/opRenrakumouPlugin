<?php
class TejimayaNotify
{
	public function execute($event){
		$action = $event['actionInstance'];
    if(sfRequest::POST != $action->getRequest()->getMethod()){
      return;
    }
		$topic = $action->getRequestParameter('community_topic');
		$community_member_list = $action->getRoute()->getObject()->getMembers(100);
		
    $id = $action->getUser()->getMemberId();
    $body = $topic['body'];
    $body = str_replace(array("\r\n","\r","\n",","), '', $body);

		foreach($community_member_list as $member){
			$tel = $member->getProfile("tel");
			sfContext::getInstance()->getLogger()->info(date("c"). $member->id . ":" .$tel);
			TejimayaBoundioUtil::pushcall($tel,$body);
		}
	}
}

class TejimayaBoundioUtil
{
	static function update(){
    $map = json_decode(Doctrine::getTable('SnsConfig')->get('boundio_status_map'),true);
    $public_pcall_status = json_decode(Doctrine::getTable('SnsConfig')->get('public_pcall_status'),true);
    if(null == $public_pcall_status){
      $this->logMessage('public_pcall_status empty',"err");
    }

    foreach($public_pcall_status as &$ref_status){
      /*
      if('ACTIVE' != $status["status"]){ //FIXME Impl status field
        continue; //SKIP
      }
      */
      foreach($ref_status['status_list'] as &$ref_line){
        if('CALLPROCESSING' != $ref_line['telstat']){
          continue;
        }
        if(!$map[(string)$ref_line['boundio_id']]){
          //echo "MAP_NOT_HIT\n";
          continue;
        }else{
          //echo "MAP_HIT\n";
        }
        if("HUZAI1" == $ref_line['telstat'] && "HUZAI" == $map[$ref_line['boundio_id']]){
          $ref_line['telstat'] = "HUZAI2";
        }else{
          $ref_line['telstat'] = $map[$ref_line['boundio_id']];
        }
      }
    }
    unset($ref_status);
    unset($ref_line);
    Doctrine::getTable('SnsConfig')->set('public_pcall_status',json_encode($public_pcall_status));
	}
	static function boundio(){
		$boundio_list = TejimayaBoundioUtil::status_list(300,$_SERVER['userSerialId'],$_SERVER['appId'],$_SERVER['authKey']);
    if(!$boundio_list){
      //echo "Boundio access error";
    }
    
    $map = array();
    foreach($boundio_list as $line){
      $_status = "";
      if("1" == (string)$line['_gather']){
        $_status = "PUSH1";
      }else{
        switch ($line['_status']) {
          case '架電完了':
            $_status = "CALLED";
            break;
          case '不在':
            $_status = "HUZAI";
            break;
        }
      }
      $map[(string)$line['_id']] = $_status;
    }

    Doctrine::getTable('SnsConfig')->set('boundio_status_raw',json_encode($boundio_list));
    Doctrine::getTable('SnsConfig')->set('boundio_status_map',json_encode($map));
	}
	static function process(){
    $public_pcall_status = json_decode(Doctrine::getTable('SnsConfig')->get('public_pcall_status'),true);
    if(null == $public_pcall_status){
      $this->logMessage('public_pcall_status empty',"err");
    }

    foreach($public_pcall_status as &$ref_status){
    	/*
    	if('ACTIVE' != $status["status"]){ //FIXME Impl status field
    		continue; //SKIP
    	}
    	*/
    	foreach($ref_status['status_list'] as &$ref_line){
    		if('CALLWAITING' != $ref_line['telstat']){
    			continue;
    		}
        //echo "\nbody:".$ref_status['body'];
        //echo "\ntel:".$ref_line['tel'];

    		$result = TejimayaBoundioUtil::pushcall($ref_line['tel'],$ref_status['body'],$_SERVER['userSerialId'],$_SERVER['appId'],$_SERVER['authKey']);
    		//echo "BOUNDIO CALL DONE status: " . $result. "\n";
    		if($result){
    			$ref_line['telstat'] = 'CALLPROCESSING';
    			$ref_line['boundio_id'] = $result;
    		}else{
          $ref_line['telstat'] = 'FAIL';
        }
    	}
    }
    unset($ref_status);
    unset($ref_line);
    
    sfContext::getInstance()->getLogger()->info('TASK DONE');
   	//print_r($public_pcall_status);
    Doctrine::getTable('SnsConfig')->set('public_pcall_status',json_encode($public_pcall_status));
	}
	static function pushcall($tel=null,$text=null,$userSerialId,$appId,$authKey){
		Boundio::configure('userSerialId', $userSerialId);
		Boundio::configure('appId', $appId);
		Boundio::configure('authKey', $authKey);
		//$str = 'silent()%%silent()%%file_d('.$text.')%%silent()%%file_d('.$text.')%%silent()%%file_d(この件に了解であれば1を、不明な場合は0をプッシュしてください。)%%gather(20,1)%%file_d(連絡は以上です。)';
		$str = 'silent()%%silent()%%silent()%%file_d('.$text.')%%silent()%%file_d(この件に了解であれば1を、不明な場合は0をプッシュしてください。)%%gather(20,1)%%file_d(連絡は以上です。)';

		$result = Boundio::call($tel, $str);
		//FIXME Boundioのエラーパターン位基づいて、クライアントにエラーを通知する
		//print_r($result);
		if("true" == $result["success"]){
			return $result["_id"];
		}else{
			return false;
		}
	}
	static function status_list($num=100,$userSerialId,$appId,$authKey){
		Boundio::configure('userSerialId', $userSerialId);
		Boundio::configure('appId', $appId);
		Boundio::configure('authKey', $authKey);
		
		$result = Boundio::status(null, date("Ymd",strtotime("-2 days")), date("Ymd",strtotime("-1 days")), $num);
		if("true" == $result[0]['success']){
			return $result[0]['result'];
		}else{
			return false;
		}
	}
}

/**
 * boundio API simple client interface.
 *
 * Copyright (c) 2012, KDDI Web Communications Inc.
 * All rights reserved.
 * 
 * @package Boundio
 * @author hiromitz <hiromitz.m@gmail.com>
 * @link https://github.com/boundio/boundio-php
 * @license http://creativecommons.org/licenses/MIT/ MIT
 */
class Boundio {
	
	public static $_config = array(
		'env' => 'develop', // develop || production
		'userSerialId' => 'User Serial ID',
		'appId' => 'API Access Key',
		'authKey' => 'User Auth Key'
	);
	
	protected static $_baseDevUrl = 'https://boundio.jp/api/vd2/';
	protected static $_baseUrl = 'https://boundio.jp/api/v2/';
	
	public static function configure($key, $value) {
		static::$_config[$key] = $value;
	}
	
	protected static function getUrl($develop=false) {
		$url = (static::$_config['env'] == 'develop' && $develop === false) ? static::$_baseDevUrl : static::$_baseUrl;
		$url .= static::$_config['userSerialId'];
		return $url;
	}

	public static function call($tel_to, $cast) {
		$tel_to = str_replace('-', '', $tel_to);
		
		// validation - phone number
		if(!preg_match('/^0\d{9,10}$/', $tel_to)) {
			return false;
		}
		
		// validation - casts
		/*
		foreach($casts as $cast) {
			if(!preg_match('/^(file\([0-9]+\)|num\([0-9]\)|silent\(\))$/', $cast)) {
				return false;
			}
		}
		*/
		// execute call
		$result = static::_execute(static::getUrl(). '/call', array(
			'tel_to' => $tel_to,
			'cast' => $cast
		), 'post');
		
		$result = json_decode($result, true);
		
		return $result;
	}
	
	public static function status($id='', $start='', $end='', $count=100) {
		$start = preg_replace('/[-\/]/', '', $start);
		$end = preg_replace('/[-\/]/', '', $end);
		
		$params = array();
		
		$params['count'] = $count;
		if($id !== '') {
			$params['tel_id'] = $id;
		} elseif($start !== '') {
			// search one day if end day is not given
			if($end === '') {
				$end = $start;
			}
			$params['start'] = $start;
			$params['end'] = $end;
		}
		
		// execute get status
		$result = static::_execute(static::getUrl(). '/tel_status', $params);
		
		$result = json_decode($result, true);
		
		return $result;
	}
	
	public static function file($text='', $file='', $filename) {
		
		$params = array();
		$options = array();
		
		if($text !== '') {
			$params['convtext'] = $text;
			$params['filename'] = $filename;
		} else {
			// file validation
			if(!file_exists($file)) {
				return false;
			}
			$params['file'] = '@'. $file;
			$params['filename'] = $filename;
		}
		
		// execute get status
		$result = static::_execute(static::getUrl(true). '/file/post', $params, 'post');
		
		$result = json_decode($result, true);
		
		return $result;
	}
	
	protected static function _execute($url, array $params, $method='get', array $options = array()) {
		
		$params['auth'] = static::$_config['authKey'];
		$params['key'] = static::$_config['appId'];
		
		$defaults = ($method == 'post') ? array(
			CURLOPT_POST => ($method == 'post') ? 1: 0,
			CURLOPT_HEADER => 0,
			CURLOPT_URL => $url,
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_TIMEOUT => 4,
			CURLOPT_POSTFIELDS => $params
		) : array(
			CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($params),
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_TIMEOUT => 4
		);
		
		$ch = curl_init();
		curl_setopt_array($ch, ($options + $defaults));
		
		$result = curl_exec($ch);
		curl_close($ch);
		
		return $result;
	}
}

/**
 * BoundioException class
 * 
 */
class BoundioException extends Exception{}
