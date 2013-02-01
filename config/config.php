<?php

require_once(dirname(__FILE__).'/lib.php');
$this->dispatcher->connect('op_action.post_execute_communityTopic_create', array('TejimayaNotify', 'execute'));
