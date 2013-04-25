<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * PluginRenrakuBodyTable
 *
 * @package    OpenPNE
 * @author     tatsuya ichikawa <ichikawa@tejimaya.com>
 */
class PluginRenrakuBodyTable extends Doctrine_Table
{
  public function getLatestRenrakuBody($limit = 10)
  {
    return $this->createQuery()
      ->orderBy('created_at desc')
      ->limit($limit)
      ->execute();
  }
}
