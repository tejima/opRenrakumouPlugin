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
 * @package    opRenrakumouPlugin
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

  public function insertRenrakuBody($renrakuBody = array())
  {
    $object = new RenrakuBody();

    if (isset($renrakuBody['body']))
    {
      if (200 < mb_strlen($renrakuBody['body'], 'utf-8'))
      {
        sfContext::getInstance()->getLogger()->err('body is should be 200 characters.', 'error');

        return null;
      }
      $object->setBody($renrakuBody['body']);
    }
    else
    {
      sfContext::getInstance()->getLogger()->err('body is not specified.', 'error');

      return null;
    }

    if (isset($renrakuBody['title']))
    {
      if (200 < mb_strlen($renrakuBody['title'], 'utf-8'))
      {
        sfContext::getInstance()->getLogger()->err('title is should be 200 characters.', 'error');

        return null;
      }
      $object->setTitle($renrakuBody['title']);
    }
    else
    {
      sfContext::getInstance()->getLogger()->err('title is not specified.', 'error');

      return null;
    }
    $object->save();

    return $object;
  }
}
