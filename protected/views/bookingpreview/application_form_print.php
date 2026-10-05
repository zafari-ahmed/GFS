<?php
/**
 * SEVEN WONDERS CITY — Application Form / Terms print pages
 */
if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('appFormMoney')) {
    function appFormMoney($amount)
    {
        if ($amount === '' || $amount === null || $amount == 0) {
            return '';
        }
        return number_format((float) $amount);
    }
}

$plot = @$booking->plot;
$customer = @$booking->customer;
//$printPage = $printPage ?? 'form';
$printPage = $_GET['page'] ?? 'form';

$book_by = @$booking->agent->name ?: str_replace(' ', '<br/>', @$booking->agent_name); 
$plot_no = @$plot->plot_number;
$plot_type = @$plot->plot_type;
$plot_size = @$plot->size->size;
$cluster = @$plot->block_number;

$applicant_name = @$customer->name;
$cnic_no = @$customer->cnic;
$father_husband_name = @$customer->father_husband_name;
$guardian_name = @$customer->guardian;
$date_of_birth = '';
if (!empty($customer->dob) && $customer->dob !== '0000-00-00') {
    $dobTs = strtotime($customer->dob);
    $date_of_birth = $dobTs ? date('d-m-Y', $dobTs) : $customer->dob;
}
$nationality = @$customer->nationality;
$occupation = @$customer->occupation;
$address = @$customer->address;
$phone_office = @$customer->office;
$cell_no = @$customer->mobile;
$residence_no = @$customer->phone;
$application_date = '';
if (!empty($booking->createdOn) && $booking->createdOn !== '0000-00-00') {
    $appTs = strtotime($booking->createdOn);
    $application_date = $appTs ? date('d-m-Y', $appTs) : $booking->createdOn;
}

$nominee_name = @$customer->nominee_name;
$nominee_relation = @$customer->nominee_relation;
$nominee_age = '';

if (!empty($customer->dob) && $customer->dob !== '0000-00-00') {
    try {
        $dob = new DateTime($customer->dob);
        $today = new DateTime();

        $nominee_age = $dob->diff($today)->y;
    } catch (Exception $e) {
        $nominee_age = '';
    }
}
$nominee_parent_name = @$customer->name;

$cost_of_plot = @$plot->total;
$corner_charges = (@$plot->is_corner == 1) ? $this->Percentage($plot->total, $plot->is_corner_amount, false) : '';
$west_open_charges = (@$plot->is_west_open == 1) ? $this->Percentage($plot->total, $plot->is_west_open_amount, false) : '';
$road_facing_charges = (@$plot->is_road_facing == 1) ? $this->Percentage($plot->total, $plot->is_road_facing_amount, false) : '';
$park_facing_charges = (@$plot->is_park_facing == 1) ? $this->Percentage($plot->total, $plot->is_park_facing_amount, false) : '';

$total_amount = (float) $cost_of_plot;
foreach (array($corner_charges, $west_open_charges, $road_facing_charges, $park_facing_charges) as $extraCharge) {
    if ($extraCharge !== '' && $extraCharge !== null) {
        $total_amount += (float) $extraCharge;
    }
}
$discount_amount = @$plot->discount;
$net_amount = $total_amount - (float) $discount_amount;

$pageTitle = ($printPage === 'terms') ? 'Terms & Conditions — Seven Wonders City' : 'Application Form — Seven Wonders City';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo e($pageTitle); ?> — GB-<?php echo e(@$booking->id); ?></title>
<?php if ($printPage === 'terms'): ?>
<style>
    .page {
        width: 8.5in;
        height: 12in!important;
        margin: 0 auto;
        background: transparent;
        position: relative;
        overflow: hidden;
    }
</style>
<?php endif; ?>
<style>
    @page { size: letter portrait; margin: 0; }

    * { box-sizing: border-box; }

    html, body { margin: 0; padding: 0; }

    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
        background: #fff;
    }

    .page {
        width: 8.5in;
        height: 10in;
        margin: 0 auto;
        background: transparent;
        position: relative;
        overflow: hidden;
    }

    .content { padding: 0.32in 0.45in 0.45in; }

    .row {
        display: flex;
        align-items: flex-end;
        gap: 0.18in;
        margin-bottom: 0.17in;
        font-size: 10.5pt;
    }
    .f {
        display: flex;
        align-items: flex-end;
        flex: 1 1 0;
        min-width: 0;
    }
    .f .v {
        flex: 1 1 auto;
        min-width: 1in;
        min-height: 17px;
        padding: 0 4px 1px;
        font-weight: 600;
        font-size: 10pt;
        text-transform: uppercase;
        white-space: nowrap;
        overflow: hidden;
        line-height: 1.25;
    }
    .f .v.wrap { white-space: normal; }
    .grow-2 { flex-grow: 2; }

    .header {
        display: flex;
        align-items: flex-end;
        margin-bottom: 0.26in;
    }
    .header::before { content: ''; flex: 1 1 0; }
    .book-by {
        flex: 1 1 0;
        margin-left: 0.2in;
        padding-bottom: 3px;
        font-size: 12pt;
    }

    .plot-row { font-size: 13pt; }
    .plot-row .v { font-size: 10.5pt; }

    .spacer { height: 0.7in; }
    .spacer-sm { height: 0.22in; }
    .spacer-md { height: 0.38in; }
    .spacer-lg { height: 0.45in; }
    .spacer-decl { height: 1.05in; }

    .office {
        display: grid;
        grid-template-columns: 2.35in 1fr 1.7in;
        margin-top: 6.2in;
        font-size: 9.5pt;
    }
    .accept {
        padding-right: 0.25in;
        display: flex;
        flex-direction: column;
    }
    .accept .row { margin: 0.74in 0 0.04in; font-size: 10.5pt; }

    .office-right { grid-column: 2 / 4; display: grid; grid-template-columns: 1fr 1.7in; padding-left: 0.7in; }
    .costs { padding-right: 0.25in; }
    .cost-row {
        display: grid;
        grid-template-columns: 1.4in 1fr;
        align-items: end;
        margin-bottom: 0.075in;
    }
    .cost-row .v {
        min-height: 15px;
        padding: 0 4px 1px;
        font-weight: 600;
        text-align: right;
        white-space: nowrap;
        overflow: hidden;
    }

    .totals {
        padding: 0.06in 0.16in 0.08in;
        align-self: start;
        margin-top: 0.08in;
    }
    .tot-row { margin-bottom: 0.05in; }
    .tot-row:last-child { margin-bottom: 0; }
    .tot-row .amt { display: flex; align-items: flex-end; }
    .tot-row .amt .v {
        flex: 1;
        min-height: 15px;
        padding: 0 4px 1px;
        font-weight: 600;
        text-align: right;
        white-space: nowrap;
        overflow: hidden;
    }

    .applicant-photo {
        position: absolute;
        top: 0.45in;
        right: 0.45in;
        z-index: 2;
        width: 1.1in;
    }
    .applicant-photo img {
        width: 100%;
        height: auto;
        display: block;
        background: transparent;
    }

    @media print {
        body { background: #fff; }
        .page { margin: 0; }
    }
</style>
</head>
<body>

<?php if ($printPage === 'form'): ?>
<section class="page p1" style="margin-top:215px;">
    <?php /*if (!empty($booking->agent_cnic)): ?>
    <div class="applicant-photo">
        <img src="<?php echo Yii::app()->baseUrl?>/uploads/booking/<?php echo e($booking->agent_cnic)?>" alt="">
    </div>
    <?php endif;*/ ?>
    <div class="content">

        <div class="header">
            <div class="book-by f">
            <span class="v" style="margin-left: 300px; font-size: 9px; display: inline-block; vertical-align: top;">
            <?php
            $name = @$booking->agent->name ?: @$booking->agent_name;
            $words = explode(' ', trim($name));
            
            if (count($words) > 2) {
                // Output first 2 words
                echo htmlspecialchars($words[0] . ' ' . $words[1]);
                // Force the break
                echo '<br/>';
                // Output the rest of the name
                echo htmlspecialchars(implode(' ', array_slice($words, 2)));
            } else {
                // Fallback if there are less than 2 spaces
                echo htmlspecialchars($name);
            }
            ?>
        </span>
</div>
        </div>

        <div class="row plot-row">
            <div class="f"><span class="v" style="margin-left:120px;margin-top:5px"><?= e($plot_no) ?></span></div>
            <div class="f"><span class="v" style="margin-left:120px"><?= e($plot_type) ?></span></div>
            <div class="f"><span class="v" style="margin-left:110px"><?= e($plot_size) ?></span></div>
            <div class="f"><span class="v" style="margin-left:150px"><?= e($cluster) ?></span></div>
        </div>

        <div class="spacer"></div>

        <div class="row" style="margin-top:50px">
            <div class="f"><span class="v" style="position:absolute;margin-left:300px"><?= e($applicant_name) ?></span></div>
        </div>
        <div class="row" style="padding-top:10px;">
            <div class="f"><span class="v" style="position:absolute;margin-left:300px"><?= e($cnic_no) ?></span></div>
        </div>
        <div class="row" style="padding-top:10px;">
            <div class="f"><span class="v" style="position:absolute;margin-left:300px"><?= e($father_husband_name) ?></span></div>
        </div>
        <div class="row">
            <div class="f"><span class="v" style="margin-left:100px"><?= e($guardian_name) ?></span></div>
        </div>

        <div class="spacer-sm"></div>

        <div class="row" style="margin-top:-3px">
            <div class="f"><span class="v" style="margin-left:100px;"><?= e($date_of_birth) ?></span></div>
            <div class="f"><span class="v" style="margin-left:150px;"><?= e($nationality) ?></span></div>
            <div class="f grow-2"><span class="v" style="margin-left:200px"><?= e($occupation) ?></span></div>
        </div>
        <div class="row" style="margin-top: -5px;">
            <div class="f" style="margin-left:65px;"><span class="v wrap"><?= e($address) ?></span></div>
        </div>

        <div class="spacer-sm"></div>

        <div class="row" style="margin-top: 3px;">
            <div class="f"><span class="v" style="margin-left:110px"><?= e($phone_office) ?></span></div>
            <div class="f"><span class="v" style="margin-left:125px"><?= e($cell_no) ?></span></div>
            <div class="f grow-2"><span class="v" style="margin-left:200px"><?= e($residence_no) ?></span></div>
        </div>

        <div class="spacer-decl"></div>

        <div class="row" style="margin-top: 3%;">
            <div class="f"><span class="v" style="margin-left:100px"><?= e($application_date) ?></span></div>
            <div class="f"><span class="v"></span></div>
        </div>

        <div class="spacer-md"></div>
        <div class="row" style="margin-top: -5%;">
            <div class="f"><span class="v" style="margin-left:150px;"><?= e($nominee_name) ?></span></div>
        </div>

        <div class="row" style="margin-top: 5%;">
            <div class="f grow-2"><span class="v" style="margin-left:210px;font-size:10px"><?= e($nominee_name) ?></span></div>
            <div class="f"><span class="v" style="margin-left:90px;font-size:10px"><?= e($nominee_relation) ?></span></div>
            <div class="f"><span class="v" style="margin-left:90px;font-size:10px"><?= e($nominee_age) ?></span></div>
        </div>

        <div class="row">
            <div class="f"><span class="v" style="margin-left:150px;"><?= e($applicant_name) ?></span></div>
        </div>

    </div>
</section>
<?php endif; ?>

<?php if ($printPage === 'terms'): ?>
<section class="page p2">
    <div class="content" style="padding-top:3.5in">
        <div class="office">
            <div class="accept" style="margin-top:60px;margin-left:60px;">
                <div class="row">
                    <div class="f"><span class="v"><?= e($applicant_name) ?></span></div>
                </div>
            </div>

            <div class="office-right" style="margin-top:9%;margin-left:20%">
                <div class="costs">
                    <div class="cost-row"><span class="v" style="padding-bottom:5px"><?= e(appFormMoney($cost_of_plot)) ?></span></div>
                    <div class="cost-row"><span class="v"><?= e(appFormMoney($corner_charges)) ?></span></div>
                    <div class="cost-row"><span class="v"><?= e(appFormMoney($west_open_charges)) ?></span></div>
                    <div class="cost-row"><span class="v"><?= e(appFormMoney($road_facing_charges)) ?></span></div>
                    <div class="cost-row"><span class="v"><?= e(appFormMoney($park_facing_charges)) ?></span></div>
                </div>

                <div class="totals" style="margin-top:20%">
                    <div class="tot-row">
                        <div class="amt"><span class="v"><?= e(appFormMoney($total_amount)) ?></span></div>
                    </div>
                    <div class="tot-row">
                        <div class="amt"><span class="v" style="margin-top:15%"><?= e(appFormMoney($discount_amount)) ?></span></div>
                    </div>
                    <div class="tot-row">
                        <div class="amt"><span class="v" style="margin-top:15%"><?= e(appFormMoney($net_amount)) ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 300);
    });
</script>
</body>
</html>
