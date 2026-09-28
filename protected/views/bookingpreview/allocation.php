<?php
if (!isset($booking) && isset($customerPlot)) {
    $booking = $customerPlot;
}
$base = Yii::app()->baseUrl;
$plot = @$booking->plot;
$customer = @$booking->customer;
$letterNo = method_exists($this, 'getBookingRegNo') ? $this->getBookingRegNo($booking->id) : ('GB-'.@$booking->id);
$receiptNo = ltrim((string)@$booking->id, '0');
$created = '';
if (!empty($booking->createdOn) && $booking->createdOn !== '0000-00-00') {
    $createdTs = strtotime($booking->createdOn);
    $created = $createdTs ? date('d-M-Y', $createdTs) : $booking->createdOn;
}
$soDoWo = trim((string)@$booking->agent_name);
$father = trim((string)@$customer->father_husband_name);
$soLine = trim($soDoWo.' '.$father);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Allocation Letter</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            color: #111;
        }
        .page {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            position: relative;
            background: #fbf7ee;
            overflow: hidden;
        }
        .frame {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            z-index: 20;
            pointer-events: none;
        }
        .inner {
            position: relative;
            z-index: 1;
            height: 100%;
            padding: 32mm 28mm 34mm 28mm;
            overflow: hidden;
        }
        .skyline {
            position: absolute;
            left: 8%;
            right: 8%;
            bottom: 18mm;
            height: 55mm;
            background: url('<?php echo $base?>/images/gfs-invoice-back.png') center bottom / contain no-repeat;
            opacity: 0.12;
            pointer-events: none;
        }
        .stamp {
            position: absolute;
            right: 30mm;
            bottom: 36mm;
            width: 38mm;
            z-index: 3;
        }
        .stamp img {
            width: 100%;
            height: auto;
            display: block;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 7mm;
        }
        .header .col {
            display: table-cell;
            vertical-align: middle;
        }
        .header .left,
        .header .right {
            width: 28%;
        }
        .header .center {
            width: 44%;
            text-align: center;
        }
        .header .center img {
            width: 38mm;
            height: auto;
        }
        .mini-box {
            border: 1px solid #111;
            font-size: 12px;
            width: 42mm;
        }
        .mini-box .title {
            font-weight: bold;
            font-style: italic;
            padding: 2px 6px;
            border-bottom: 1px solid #111;
        }
        .mini-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .mini-box td {
            border-top: 1px solid #111;
            padding: 3px 6px;
            height: 7mm;
            vertical-align: middle;
        }
        .mini-box td.label {
            width: 10mm;
            font-weight: bold;
            border-right: 1px solid #111;
        }
        .header .right .mini-box {
            margin-left: auto;
        }
        .header .right .mini-box .title {
            text-align: right;
            font-style: normal;
        }
        .line {
            display: table;
            width: 100%;
            margin: 3.2mm 0;
            font-size: 14.5px;
        }
        .line .lbl {
            display: table-cell;
            white-space: nowrap;
            padding-right: 4px;
            vertical-align: bottom;
            font-weight: 600;
        }
        .line .val {
            display: table-cell;
            width: 100%;
            border-bottom: 1px solid #111;
            min-height: 6mm;
            padding: 0 4px 1px;
            font-weight: bold;
            vertical-align: bottom;
            padding-left: 10%;
        }
        .unit-row {
            display: table;
            width: 100%;
            margin: 2mm 0 5mm;
            font-size: 14.5px;
        }
        .unit-row .part {
            display: table-cell;
            vertical-align: bottom;
            padding-right: 4mm;
        }
        .unit-row .part:last-child {
            padding-right: 0;
        }
        .unit-row .lbl {
            white-space: nowrap;
            font-weight: 600;
        }
        .unit-row .val {
            display: inline-block;
            min-width: 28mm;
            border-bottom: 1px solid #111;
            padding: 0 4px 1px;
            font-weight: bold;
            margin-left: 3px;
            padding-left: 10%;
        }
        .cnic-date {
            display: table;
            width: 100%;
            margin: 3.2mm 0;
        }
        .cnic-date .half {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
        }
        .cnic-date .half:first-child {
            padding-right: 4mm;
        }
        .cnic-date .line {
            margin: 0;
        }
        .note {
            margin-top: 7mm;
            font-size: 13.5px;
            line-height: 1.45;
            max-width: 78%;
        }
        .note b {
            font-style: italic;
        }
        .bottom-date {
            margin-top: 14mm;
            font-size: 14.5px;
            width: 55%;
        }
        .bottom-date .val {
            display: inline-block;
            min-width: 38mm;
            border-bottom: 1px solid #111;
            padding: 0 6px 1px;
            font-weight: bold;
            margin-left: 4px;
        }
        .signs {
            position: absolute;
            left: 28mm;
            right: 28mm;
            bottom: 25%;
            display: table;
            width: calc(100% - 56mm);
            font-size: 13.5px;
            z-index: 2;
        }
        .signs .s {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
        }
        .signs .s.right {
            text-align: right;
        }
        .signs .line-box {
            display: inline-block;
            min-width: 48mm;
            text-align: center;
        }
        .signs .sig-line {
            border-bottom: 1px solid #111;
            height: 12mm;
            margin-bottom: 3px;
        }
        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
                background: #fff;
            }
            .page {
                margin: 0;
                box-shadow: none;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        @media screen {
            body { background: #555; padding: 12px 0; }
            .page { box-shadow: 0 0 12px rgba(0,0,0,.45); }
        }
    </style>
</head>
<body>
<div class="page">
    <img class="frame" src="<?php echo $base?>/images/allocation-border-frame.png" alt="">
    <div class="inner">
        <div class="skyline"></div>
        <div class="stamp"><img src="<?php echo $base?>/images/gfs-stamp.png" alt="GFS Stamp"></div>

        <div class="header">
            <div class="col left">
                <div class="mini-box">
                    <div class="title" style="text-align: center!important;">Ref: Receipt</div>
                    <table>
                        <tr>
                            <td class="label">No.</td>
                            <td style="font-weight: bold;text-align: center!important;"><?php echo htmlspecialchars($receiptNo)?></td>
                        </tr>
                        <tr>
                            <td class="label">Dt:</td>
                            <td style="font-weight: bold;text-align: center!important;"><?php echo htmlspecialchars($created)?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col center">
                <img src="<?php echo $base?>/images/seven-wonder-1.png" alt="Seven Wonders City">
            </div>
            <div class="col right">
                <div class="mini-box">
                    <div class="title" style="text-align: center!important;">Allocation <br/>Letter</div>
                    <table>
                        <tr>
                            <td class="label">No.</td>
                            <td style="font-weight: bold;text-align: center!important;"><?php echo htmlspecialchars($letterNo)?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="unit-row">
            <div class="part">
                <span class="lbl">Unit No.</span><span class="val"><?php echo htmlspecialchars(@$plot->plot_number)?></span>
            </div>
            <div class="part">
                <span class="lbl">Type</span><span class="val"><?php echo htmlspecialchars(@$plot->plot_type)?></span>
            </div>
            <div class="part">
                <span class="lbl">Sector</span><span class="val"><?php echo htmlspecialchars(@$plot->block_number)?></span>
            </div>
        </div>

        <div class="line">
            <div class="lbl">Name :</div>
            <div class="val"><?php echo htmlspecialchars(@$customer->name)?></div>
        </div>
        <div class="line">
            <div class="lbl">S/o. D/o. W/o.</div>
            <div class="val"><?php echo htmlspecialchars($soLine)?></div>
        </div>
        <div class="line">
            <div class="lbl">Address</div>
            <div class="val"><?php echo htmlspecialchars(@$customer->address)?></div>
        </div>
        <div class="cnic-date">
            <div class="half">
                <div class="line">
                    <div class="lbl">C.N.I.C No :</div>
                    <div class="val"><?php echo htmlspecialchars(@$customer->cnic)?></div>
                </div>
            </div>
            <div class="half">
                <div class="line">
                    <div class="lbl">Date:</div>
                    <div class="val"><?php echo htmlspecialchars($created)?></div>
                </div>
            </div>
        </div>
        <div class="line">
            <div class="lbl">Nominee:</div>
            <div class="val"><?php echo htmlspecialchars(@$customer->nominee_name)?></div>
        </div>
        <div class="line">
            <div class="lbl">Nominee Address:</div>
            <div class="val"></div>
        </div>
        <div class="line">
            <div class="lbl">Nominee C.N.I.C:</div>
            <div class="val"><?php echo htmlspecialchars(@$customer->nominee_cnic)?></div>
        </div>
        <div class="line">
            <div class="lbl">Relation:</div>
            <div class="val"><?php echo htmlspecialchars(@$customer->nominee_relation)?></div>
        </div>

        <div class="note">
            We hereby provisionally allocate you the above mentioned Unit in <b>SEVEN<br>
            WONDERS CITY</b> as per Terms &amp; Conditions prescribed on the Application<br>
            Form duly signed by you.
        </div>

        <div class="bottom-date">
            Date:<span class="val"><?php echo htmlspecialchars($created)?></span>
        </div>

        <div class="signs">
            <div class="s">
                <div class="line-box">
                    <div class="sig-line"></div>
                    Signature of Allottee
                </div>
            </div>
            <div class="s right">
                <div class="line-box">
                    <div class="sig-line"></div>
                    Authorized Signature
                </div>
            </div>
        </div>
    </div>
</div>
<script>
window.addEventListener('load', function () {
    setTimeout(function () { window.print(); }, 500);
});
</script>
</body>
</html>
