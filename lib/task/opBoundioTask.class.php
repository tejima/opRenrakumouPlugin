<?php

class opBoundioTask extends sfBaseTask
{
	protected function configure()
  {
    $this->namespace        = 'renrakumou';
    $this->name             = 'boundio';
    $this->briefDescription = 'Check Boundio status';
    $this->databaseManager = null;
    $this->detailedDescription = 
<<< EOF
  [./symfony renrakumou:boundio]
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

    $boundio_list = TejimayaBoundioUtil::status_list(300,$_SERVER['userSerialId'],$_SERVER['appId'],$_SERVER['authKey']);
    if(!$boundio_list){
      echo "Boundio access error";
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
}
