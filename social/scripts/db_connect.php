<?php
define('DB_SERVER', 'sql112.infinityfree.com');
define('DB_USERNAME', 'if0_37640661');
define('DB_PASSWORD', 'LUVM21QzFkZi');
define('DB_NAME', 'if0_37640661_selcuksozluk');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($link === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
