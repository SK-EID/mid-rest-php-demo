<?php
/**
 * Created by IntelliJ IDEA.
 * User: mikks
 * Date: 3/6/2019
 * Time: 12:55 PM
 */

namespace Sk\Mid\HashType;


class Sha384 extends HashType
{
    public function __construct()
    {
        parent::__construct("SHA-384", "SHA384", 384, array(48, 65, 48, 13, 6, 9, 96, -122, 72, 1, 101, 3, 4, 2, 2, 5, 0, 4, 48));
    }
}
