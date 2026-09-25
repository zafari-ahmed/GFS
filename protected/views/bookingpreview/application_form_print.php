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
$printPage = $printPage ?? 'form';

$book_by = @$booking->agent->name ?: @$booking->agent_name;
$plot_no = @$plot->plot_number;
$plot_type = @$plot->plot_type;
$plot_size = @$plot->size->size;
$cluster = @$plot->block_number;

$applicant_name = @$customer->name;
$cnic_no = @$customer->cnic;
$father_husband_name = @$customer->father_husband_name;
$guardian_name = '';
$date_of_birth = '';
if (!empty($customer->dob) && $customer->dob !== '0000-00-00') {
    $dobTs = strtotime($customer->dob);
    $date_of_birth = $dobTs ? date('d-m-Y', $dobTs) : $customer->dob;
}
$nationality = '';
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
$nominee_parent_name = '';

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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    :root {
        --red: #e8252b;
        --yellow: #fbb017;
        --dark: #1b1f24;
        --ink: #2b2b2b;
        --line: #3a3a3a;
    }

    @page { size: letter portrait; margin: 0; }

    * { box-sizing: border-box; }

    html, body { margin: 0; padding: 0; }

    body {
        font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
        color: var(--ink);
        background: #d9d9d9;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .page {
        width: 8.5in;
        height: 11in;
        margin: 0.3in auto;
        background: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.25);
    }

    .bar-bottom {
        position: absolute;
        left: 0; right: 0; bottom: 0;
        height: 0.26in;
        background: var(--yellow);
    }
    .p1 .bar-top { height: 3px; background: var(--yellow); }
    .p2 .bar-top { height: 0.24in; background: var(--dark); border-bottom: 3px solid var(--yellow); }

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
    .f > label { white-space: nowrap; margin-right: 4px; line-height: 1.2; }
    .f .v {
        flex: 1 1 auto;
        min-width: 0.8in;
        border-bottom: 1px solid var(--line);
        min-height: 17px;
        padding: 0 4px 1px;
        font-weight: 600;
        font-size: 10pt;
        text-transform: uppercase;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.25;
    }
    .f .v.wrap { white-space: normal; }
    .grow-2 { flex-grow: 2; }
    .grow-3 { flex-grow: 3; }

    .note { font-size: 10.5pt; margin: -0.07in 0 0.17in; }

    .header {
        display: flex;
        align-items: flex-end;
        margin-bottom: 0.26in;
    }
    .header::before { content: ''; flex: 1 1 0; }
    .title {
        font-family: 'Montserrat', 'Poppins', Arial, sans-serif;
        font-weight: 800;
        font-size: 22pt;
        letter-spacing: 0.5px;
        white-space: nowrap;
        color: var(--red);
        margin: 0;
    }
    .book-by {
        flex: 1 1 0;
        margin-left: 0.2in;
        padding-bottom: 3px;
        font-family: 'Montserrat', 'Poppins', Arial, sans-serif;
        font-weight: 700;
        font-size: 12pt;
    }
    .book-by .v { font-family: 'Poppins', Arial, sans-serif; }

    .plot-row { font-family: 'Montserrat', 'Poppins', Arial, sans-serif; font-weight: 700; font-size: 13pt; }
    .plot-row .v { font-family: 'Poppins', Arial, sans-serif; font-size: 10.5pt; }

    .salutation { font-size: 10.5pt; line-height: 1.5; margin: 0 0 0.12in; }
    .salutation strong { font-weight: 600; }

    .band {
        background: var(--dark);
        color: #fff;
        text-align: center;
        font-size: 11.5pt;
        font-weight: 500;
        letter-spacing: 0.3px;
        padding: 4px 0.2in;
        margin: 0 -0.45in 0.28in;
    }

    .declaration {
        font-size: 10.5pt;
        line-height: 1.55;
        margin: 0.02in 0 0.38in;
    }
    .declaration strong { font-weight: 600; }

    .nomination-text {
        font-size: 10.5pt;
        line-height: 1.55;
        margin: 0.02in 0 0.45in;
    }
    .nomination-text .row { margin-bottom: 0; }
    .inline-tail { white-space: nowrap; margin-left: 0.25in; }

    .terms-title {
        font-family: 'Montserrat', 'Poppins', Arial, sans-serif;
        font-weight: 800;
        font-size: 17pt;
        color: var(--red);
        text-align: center;
        text-decoration: underline;
        text-underline-offset: 3px;
        margin: 0 0 0.1in;
    }
    .terms {
        list-style: none;
        counter-reset: term;
        margin: 0;
        padding: 0;
        font-size: 9pt;
        line-height: 1.26;
        text-align: justify;
    }
    .terms li {
        counter-increment: term;
        margin-bottom: 0.065in;
    }
    .terms li::before { content: counter(term) ". "; }
    .terms strong { font-weight: 600; }

    .office {
        display: grid;
        grid-template-columns: 2.35in 1fr 1.7in;
        margin-top: 0.06in;
        font-size: 9.5pt;
    }
    .accept {
        padding-right: 0.25in;
        border-right: 1.5px solid var(--dark);
        display: flex;
        flex-direction: column;
    }
    .accept .label { font-size: 10pt; margin: 0.2in 0 0.38in; }
    .sig-line { border-top: 1px solid var(--line); padding-top: 6px; text-align: center; font-size: 10.5pt; }
    .accept .row { margin: 0.16in 0 0.04in; font-size: 10.5pt; }
    .accept .caps { font-size: 10.5pt; padding-left: 4px; }

    .office-right { grid-column: 2 / 4; display: grid; grid-template-columns: 1fr 1.7in; padding-left: 0.2in; }
    .office-title {
        grid-column: 1 / 3;
        font-family: 'Montserrat', 'Poppins', Arial, sans-serif;
        font-weight: 800;
        font-size: 17pt;
        color: var(--red);
        text-align: center;
        margin: 0 0 0.08in;
    }
    .costs { padding-right: 0.25in; }
    .cost-row {
        display: grid;
        grid-template-columns: 1.05in 0.35in 1fr;
        align-items: end;
        margin-bottom: 0.075in;
    }
    .cost-row .v {
        border-bottom: 1px solid var(--line);
        min-height: 15px;
        padding: 0 4px 1px;
        font-weight: 600;
        text-align: right;
        white-space: nowrap;
        overflow: hidden;
    }
    .cost-row.auth { grid-template-columns: 1.4in 1fr; margin-top: 0.14in; }

    .totals {
        border: 1px solid var(--line);
        padding: 0.06in 0.16in 0.08in;
        align-self: start;
        margin-top: 0.08in;
    }
    .tot-row { margin-bottom: 0.05in; }
    .tot-row:last-child { margin-bottom: 0; }
    .tot-row .cap { text-align: center; margin-bottom: 2px; }
    .tot-row .amt { display: flex; align-items: flex-end; gap: 4px; }
    .tot-row .amt .v {
        flex: 1;
        border-bottom: 1px solid var(--line);
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
        border: 1px solid var(--line);
        background: #fff;
    }

    @media print {
        body { background: #fff; }
        .page { margin: 0; box-shadow: none; }
    }
</style>
</head>
<body>

<?php if ($printPage === 'form'): ?>
<section class="page p1">
    <?php if (!empty($booking->agent_cnic)): ?>
    <div class="applicant-photo">
        <img src="<?php echo Yii::app()->baseUrl?>/uploads/booking/<?php echo e($booking->agent_cnic)?>" alt="Applicant photo">
    </div>
    <?php endif; ?>
    <div class="bar-top"></div>
    <div class="content">

        <div class="header">
            <h1 class="title">APPLICATION FORM</h1>
            <div class="book-by f"><label>Book by/</label><span class="v"><?= e($book_by) ?></span></div>
        </div>

        <div class="row plot-row">
            <div class="f"><label>Plot no:</label><span class="v"><?= e($plot_no) ?></span></div>
            <div class="f"><label>Type :</label><span class="v"><?= e($plot_type) ?></span></div>
            <div class="f"><label>Size :</label><span class="v"><?= e($plot_size) ?></span></div>
            <div class="f"><label>Cluster :</label><span class="v"><?= e($cluster) ?></span></div>
        </div>

        <p class="salutation">
            Dear Sir,<br>
            I the undersigned hereby request to please register my name for allotment of Plot in your project<br>
            <strong>GFS Builders &amp; Developers &ldquo;SEVEN WONDERS CITY&rdquo;.</strong>
        </p>

        <div class="band">MY PARTICULARS ARE AS UNDER (PLEASE WRITE IN BLOCK LETTERS)</div>

        <div class="row">
            <div class="f"><label>Name in Full. Mr./Mrs /Miss:</label><span class="v"><?= e($applicant_name) ?></span></div>
        </div>
        <div class="row">
            <div class="f"><label style="min-width:1.3in">C.N.I.C No:</label><span class="v"><?= e($cnic_no) ?></span></div>
        </div>
        <div class="row">
            <div class="f"><label>Father&rsquo;s/husband Name:</label><span class="v"><?= e($father_husband_name) ?></span></div>
        </div>
        <div class="row">
            <div class="f"><label>Guardian:</label><span class="v"><?= e($guardian_name) ?></span></div>
        </div>
        <p class="note">(To be filled only if the applicant is a minor)</p>

        <div class="row">
            <div class="f"><label>Date of Birth:</label><span class="v"><?= e($date_of_birth) ?></span></div>
            <div class="f"><label>Nationality :</label><span class="v"><?= e($nationality) ?></span></div>
            <div class="f grow-2"><label>Occupation :</label><span class="v"><?= e($occupation) ?></span></div>
        </div>
        <div class="row">
            <div class="f"><label>Address:</label><span class="v wrap"><?= e($address) ?></span></div>
        </div>
        <p class="note">(Change In address to be intimated immediately)</p>

        <div class="row">
            <div class="f"><label>Phone No. Office:</label><span class="v"><?= e($phone_office) ?></span></div>
            <div class="f"><label>Cell No. :</label><span class="v"><?= e($cell_no) ?></span></div>
            <div class="f grow-2"><label>Residence No. :</label><span class="v"><?= e($residence_no) ?></span></div>
        </div>

        <p class="declaration">
            I hereby declare that I have read and understood the terms and conditions of allotment of Plot and accept the
            same and further declare that I shall abide by the existing rules and regulations, conditions, requirements, etc
            which may be presented by you and approved by the authority for the purchase of <strong>GFS Builders &amp; Developers</strong>
            (&ldquo;SEVEN WONDERS CITY&rdquo;) in this project.
        </p>

        <div class="row">
            <div class="f"><label>Date:</label><span class="v"><?= e($application_date) ?></span></div>
            <div class="f"><label>Signature of Applicant:</label><span class="v"></span></div>
        </div>

        <div class="row" style="margin-bottom:0.08in">
            <div class="f"><label>NOMINATION</label><span class="v"></span></div>
        </div>
        <p class="note" style="margin-top:0">(Nominee should not be a minor)</p>

        <div class="row">
            <div class="f grow-2"><label>I hereby nominate Mr./Mrs./Miss :</label><span class="v"><?= e($nominee_name) ?></span></div>
            <div class="f"><label>Relation :</label><span class="v"><?= e($nominee_relation) ?></span></div>
            <div class="f"><label>Age:</label><span class="v"><?= e($nominee_age) ?></span></div>
        </div>

        <div class="nomination-text">
            <div class="row">
                <div class="f"><label>S/o. W/o. D/o.</label><span class="v"><?= e($nominee_parent_name) ?></span></div>
                <span class="inline-tail">And declare that In case of my death before</span>
            </div>
            the execution of Lease/Sub-Lease of the Plot allotted to me, my above-named nominee shall be my
            successor-in-interest and Lease/Sub-Lease for all purposes under this Agreement of Allotment of Plot subject to the
            compliance of all the terms and conditions/undertakings.
        </div>

        <div class="row">
            <div class="f"><label>Authorized Signature:</label><span class="v"></span></div>
            <div class="f"><label>Signature of Applicant:</label><span class="v"></span></div>
        </div>

    </div>
    <div class="bar-bottom"></div>
</section>
<?php endif; ?>

<?php if ($printPage === 'terms'): ?>
<section class="page p2">
    <div class="bar-top"></div>
    <div class="content" style="padding-top:0.18in">

        <h2 class="terms-title">TERMS &amp; CONDITIONS</h2>

        <ol class="terms">
            <li>The name of the project shall be <strong>&ldquo;SEVEN WONDERS CITY&rdquo;</strong> the Plot will be offered to buyers on first come first served basis.</li>
            <li>The <strong>&ldquo;SEVEN WONDERS CITY&rdquo;</strong> shall offer Plot of various sizes in the project, for sale on ownership basis.</li>
            <li>All Pakistani citizens and non-resident Pakistanis living abroad are eligible to apply for Plot.</li>
            <li>All applications for booking/allotment shall be submitted on the prescribed form and detail filled in and signed by the applicant along with a pay order / demand draft drawn in the name of <strong>&ldquo;SEVEN WONDERS CITY&rdquo;</strong>.</li>
            <li>In case any buyer desires to cancel the booking/allotment of the Plot and get the refund of the amount Deposited towards the cost, the amount shall be refunded after re-booking and deduction of 20% of the total price as service charges. Booking, Allocation amounts are nonrefundable.</li>
            <li>The allottees shall pay all Development and Documentation charges, Electricity, Gas, Water connection and maintenance charges as per Applicable rules.</li>
            <li>The allottees shall not sub-let, transfer or sell the Plot to anyone else without the prior permission of the <strong>&ldquo;SEVEN WONDERS CITY&rdquo;</strong>. However the Plot can be transferred after clearance of outstanding dues payable on the date of transfer. The builder shall charge 10% of Plot cost towards TRANSFER FEE.</li>
            <li>The Schedule fixed for each and every installment for the payments shall be the essence of the contract. A demand notice of (15) fifteen days shall be served to the buyer by registered /AD. Post. This will be followed by another reminder after (30) thirty days for the payment of the installment at the address provided in the application form. If the pay is not received within the stipulated period, the <strong>&ldquo;SEVEN WONDERS CITY&rdquo;</strong> shall serve a final notice and then cancel the booking/allotment. The amount received by the <strong>&ldquo;SEVEN WONDERS CITY&rdquo;</strong> till that time will be refunded when the said Plot is re-booked by a new buyer, after deduction of 20% of the total price as service charges.</li>
            <li>The allottees shall abide by the existing rules and regulations prescribed by the <strong>&ldquo;SEVEN WONDERS CITY&rdquo;</strong>, the Development Authority and other concerned authorities.</li>
            <li>The <strong>&ldquo;SEVEN WONDERS CITY&rdquo;</strong> is allotting Plot in <strong>&ldquo;SEVEN WONDERS CITY&rdquo;</strong> to general public in accordance with <strong>&ldquo;SEVEN WONDERS CITY&rdquo;</strong> by laws No. 48(1) and Rules of Business 2012. The allottees shall abide by the <strong>&ldquo;SEVEN WONDERS CITY&rdquo;</strong> by laws and Rules of Business 2012.</li>
            <li>The <strong>&ldquo;SEVEN WONDERS CITY&rdquo;</strong> undertake to complete and deliver the project within the targeted period. However if for reasons of Force Majeure, which includes acts of God, war (declared or undeclared), civil commotion, natural disaster, hostilities, fire, flood, earthquake, explosions, blockades and any other causes beyond control of <strong>GFS Builders &amp; Developers</strong>, they may abandon the project and will refund installment received from allottees within (6) six months from the announcement made to this effect. It is clearly understood that in such eventuality the allottees will not claim interest or damages of any nature what so ever from the <strong>&ldquo;SEVEN WONDERS CITY&rdquo;</strong>.</li>
            <li>The area of Plot mentioned is approximate, if actual measurement of the area is found more or less, the buyer shall be charged on the actual allocated area on proportionate basis.</li>
            <li>The allottees shall pay all taxes etc. levied by federal government, local bodies and municipal bodies or any other authorities / agencies including those existing at present and those that may be levied by the above mentioned and / or other authorities in future.</li>
            <li>The construction on the Plot shall be strictly in accordance with the Building by Laws of <strong>&ldquo;SEVEN WONDERS CITY&rdquo;</strong>.</li>
            <li><strong>&ldquo;SEVEN WONDERS CITY&rdquo;</strong> reserve the right for any change in location, size and dimension of Plot due to any changes in layout / master plan.</li>
        </ol>

        <div class="office">
            <div class="accept">
                <div class="label">READ, ACCEPTED &amp; CONFIRMED</div>
                <div class="sig-line">SIGNATURE OF APPLICANT :</div>
                <div class="row">
                    <div class="f"><label>NAME :</label><span class="v"><?= e($applicant_name) ?></span></div>
                </div>
                <div class="caps">( IN CAPITAL LETTERS )</div>
            </div>

            <div class="office-right">
                <div class="office-title">FOR OFFICE USE ONLY</div>

                <div class="costs">
                    <div class="cost-row"><span>Cost of Plot</span><span>Rs.</span><span class="v"><?= e(appFormMoney($cost_of_plot)) ?></span></div>
                    <div class="cost-row"><span>Corner</span><span>Rs.</span><span class="v"><?= e(appFormMoney($corner_charges)) ?></span></div>
                    <div class="cost-row"><span>West Open</span><span>Rs.</span><span class="v"><?= e(appFormMoney($west_open_charges)) ?></span></div>
                    <div class="cost-row"><span>Road Facing</span><span>Rs.</span><span class="v"><?= e(appFormMoney($road_facing_charges)) ?></span></div>
                    <div class="cost-row"><span>Park facing</span><span>Rs.</span><span class="v"><?= e(appFormMoney($park_facing_charges)) ?></span></div>
                    <div class="cost-row auth"><span>Authorized signature</span><span class="v"></span></div>
                </div>

                <div class="totals">
                    <div class="tot-row">
                        <div class="cap">Total</div>
                        <div class="amt"><span>Rs.</span><span class="v"><?= e(appFormMoney($total_amount)) ?></span></div>
                    </div>
                    <div class="tot-row">
                        <div class="cap">Discount</div>
                        <div class="amt"><span>Rs.</span><span class="v"><?= e(appFormMoney($discount_amount)) ?></span></div>
                    </div>
                    <div class="tot-row">
                        <div class="cap">Net Amount</div>
                        <div class="amt"><span>Rs.</span><span class="v"><?= e(appFormMoney($net_amount)) ?></span></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="bar-bottom"></div>
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
