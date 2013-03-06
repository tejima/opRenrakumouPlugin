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

    RenrakumouUtil::process_tel();
    RenrakumouUtil::process_mail();
    
  }
}