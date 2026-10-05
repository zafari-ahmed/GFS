<?php
require_once 'qrlib.php';
$link = 'https://portal.sevenwonderscity.com/ledger/bookingledger/'.$_GET['id'];
header('Content-Type: image/png');
QRcode::png($link);
exit;
