<?php

use Francerz\SqlBuilder\DriverManager;
use Francerz\SqlBuilder\MySQL\MySQLDriver;

DriverManager::register('mysql', new MySQLDriver());
