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
    TejimayaBoundioUtil::update();
  }
}
