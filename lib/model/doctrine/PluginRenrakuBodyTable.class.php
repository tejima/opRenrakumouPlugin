<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * PluginRenrakuBody
 *
 * @package    opRenrakumouPlugin
 * @author     tatsuya ichikawa <ichikawa@tejimaya.com>
 */
class PluginRenrakuBodyTable extends Doctrine_Table
{
  public function getLatestRenrakuBody($limit = 10)
  {
    return $this->createQuery()
      ->orderBy('id desc')
      ->limit($limit)
      ->execute();
  }

  public function updateRenrakuBody($renrakuBody = array())
  {
    $object = new RenrakuBody();

    if (isset($renrakuBody['body']))
    {
      if (200 < strlen($renrakuBody['body']))
      {
        throw new LogicException('body is should be 200 characters.');
      }
      $object->setBody($renrakuBody['body']);
    }
    else
    {
      throw new LogicException('body is not specified.');
    }

    if (isset($renrakuBody['title']))
    {
      if (200 < strlen($renrakuBody['title']))
      {
        throw new LogicException('title is should be 200 characters.');
      }
      $object->setTitle($renrakuBody['title']);
    }
    else
    {
      throw new LogicException('title is not specified.');
    }

    $object->save();
    return $object;
  }
}
