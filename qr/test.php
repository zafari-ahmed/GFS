<?php
require_once 'qrlib.php';
$link = 'https://thetrainedmanwins.com/gfs/ledger/bookingledger/'.$_GET['id'];
header('Content-Type: image/png');
QRcode::png($link);
exit;
