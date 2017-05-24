<?php
/**
 * 2016 (C) BOdev Office | bodevoffice.com
 *
 * @license MIT
 *
 * Developed by Biber Ltd. (http://www.biberltd.com), a partner of BOdev Office (http://www.bodevoffice.com)
 *
 * Paid Customers ::
 *
 * Check http://team.bodevoffice.com for technical documentation or consult your representative.
 *
 * Contact support@bodevoffice.com for support requests.
 */
namespace BiberLtd\Bundle\PhpOrientBundle\Odm\Exceptions;

class MissingDatabaseInfoException extends \Exception{
    public function __construct($dbName){
        $this->message = $dbName.' information is missing in your parameters.yml. Please provide hostname, username, password, port, and token info under orientdb.database.'.$dbName.'.';
    }
}