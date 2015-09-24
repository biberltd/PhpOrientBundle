<?php
/**
 * @package     bodev-core-bundles/php-orient-bundle
 * @subpackage  Odm/Repository
 * @name        RepositoryInterface
 *
 * @author      Biber Ltd. (www.biberltd.com)
 * @author      Can Berkol
 *
 * @copyright   Biber Ltd. (C) 2015
 *
 * @version     1.0.0
 */

namespace BiberLtd\Bundle\PhpOrientBundle\Odm\Repository;

interface RepositoryInterface{

	function insert(array $collection);

	function update(array $collection);

	function select($query);

	function delete(array $collection);
}