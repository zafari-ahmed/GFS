<?php
require_once 'qrlib.php';
$link = 'https://theplaynova.com/redirect.php';
header('Content-Type: image/png');
QRcode::png($link);
exit;
