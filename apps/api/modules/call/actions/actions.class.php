<?php

/**
 * call actions.
 *
 * @package    OpenPNE
 * @subpackage main
 * @author     Your name here
 */
class callActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('default', 'module');
  }
}
