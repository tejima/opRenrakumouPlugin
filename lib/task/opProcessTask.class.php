<?php

class opProcessTask extends sfBaseTask
{
	protected function configure()
  {
    $this->namespace        = 'renrakumou';
    $this->name             = 'process';
    $this->briefDescription = 'This plugin makes friend link automatically.';
    $this->databaseManager = null;
    $this->detailedDescription = 
<<< EOF
  [./symfony renrakumou:process]
EOF;

		$this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'pc_frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
    ));
  }
  protected function execute($arguments = array(), $options = array())
  {

  	$this->configuration = parent::createConfiguration($options['application'], $options['env']);
    new sfDatabaseManager($this->configuration);

  	sfContext::createInstance($this->configuration);

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
        echo "\nbody:".$ref_status['body'];
        echo "\ntel:".$ref_line['tel'];

    		$result = TejimayaBoundioUtil::pushcall($ref_line['tel'],$ref_status['body'],$_SERVER['userSerialId'],$_SERVER['appId'],$_SERVER['authKey']);
    		echo "BOUNDIO CALL DONE status: " . $result. "\n";
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
   	print_r($public_pcall_status);
    Doctrine::getTable('SnsConfig')->set('public_pcall_status',json_encode($public_pcall_status));
    
  }
}