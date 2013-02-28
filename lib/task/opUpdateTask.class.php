<?php

class opUpdateTask extends sfBaseTask
{
	protected function configure()
  {
    $this->namespace        = 'renrakumou';
    $this->name             = 'update';
    $this->briefDescription = 'update status';
    $this->databaseManager = null;
    $this->detailedDescription = 
<<< EOF
  [./symfony renrakumou:update]
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
          echo "MAP_NOT_HIT\n";
          continue;
        }else{
          echo "MAP_HIT\n";
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
}
