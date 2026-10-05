<?php
// ----- CONFIG -----
$numRows = 6; // Initial rows when no saved data exists

// Decode saved JSON if available
$data = [];
$cop = 0;

if (!empty($booking->payment_schedule_json)) {
    $decoded = json_decode($booking->payment_schedule_json, true);

    $cop = $decoded['cop'] ?? 0;

    if (isset($decoded['rows']) && is_array($decoded['rows'])) {
        $data = $decoded['rows'];
    }
}

$heading1Options = [
    'Empty Box',
    'Registration',
    'Start Of Work',
    'Booking',
    'Confirmation',
    'Allocation',
    'Monthly',
    'Yearly',
    'Half Yearly',
    'Quarterly',
    'Before Possession',
    'Possession',
    'Demarcation',
    'Development',
    'Documentation',
    'Electricity Charges',
    'Quarterly Installment',
    'Own Money',
    'Penalty',
    'Transfer Fee',
    'Lease Charges',
    'Water Sewerage Charges',
    'Others',
    'Road Facing',
    'West Open',
    'Corner',
    'Extra Land',
    'Park Facing',
];

$heading2Options = [
    'Empty Box',
    '1st Payment',
    '2nd Payment',
    '3rd Payment',
    'Monthly',
    'Yearly',
    'Half Yearly',
    'Quarterly',
    '2nd Last Payment',
    'Last Payment',
];

// Helper: HTML-escape
function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function psRepeatType($heading1, $heading2 = '') {
    $values = array(
        strtolower(trim((string)$heading1)),
        strtolower(trim((string)$heading2)),
    );
    foreach ($values as $value) {
        if ($value === 'monthly' || $value === 'monthly installment') {
            return 'monthly';
        }
        if ($value === 'yearly') {
            return 'yearly';
        }
        if ($value === 'half yearly') {
            return 'half_yearly';
        }
        if ($value === 'quarterly' || $value === 'quarterly installment') {
            return 'quarterly';
        }
    }
    return '';
}

function psRepeatLabels($type) {
    if ($type === 'yearly') {
        return array('Yearly installment', 'Years', 'Total');
    }
    if ($type === 'half_yearly') {
        return array('Half yearly installment', 'Times', 'Total');
    }
    if ($type === 'quarterly') {
        return array('Quarterly installment', 'Times', 'Total');
    }
    return array('Monthly installment', 'Months', 'Total');
}

function psDateInputValue($date) {
    $date = trim((string)$date);
    if ($date === '') {
        return '';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return $date;
    }
    $ts = strtotime($date);
    return $ts ? date('Y-m-d', $ts) : '';
}

function psSplitMonthlyParts($value, $row = array()) {
    $installment = isset($row['installment']) ? trim((string)$row['installment']) : '';
    $months = isset($row['months']) ? trim((string)$row['months']) : '';
    $total = isset($row['total']) ? trim((string)$row['total']) : '';
    if ($installment !== '' || $months !== '' || $total !== '') {
        return array($installment, $months, $total);
    }

    $main = trim((string)$value);
    $firstLine = preg_split("/\r\n|\n/", $main);
    $main = trim($firstLine[0]);
    if (strpos($main, '=') !== false) {
        $parts = preg_split('/\s*=\s*/', $main, 2);
        $main = trim($parts[0]);
        $total = isset($parts[1]) ? trim($parts[1]) : '';
    }
    if (preg_match('/^([\d,\.]+)\s*[xX*]\s*([\d,\.]+)$/', $main, $matches)) {
        return array($matches[1], $matches[2], $total);
    }
    return array($main, $months, $total);
}

function psFormatAmountValue($value) {
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    return preg_replace_callback('/\d+(?:\.\d+)?/', function ($matches) {
        $number = $matches[0];
        if (strpos($number, '.') !== false) {
            return number_format((float)$number, 2, '.', ',');
        }
        return number_format((float)$number, 0, '.', ',');
    }, str_replace(',', '', $value));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Payment Schedule</title>

<style>
    body {
        font-family: Arial, sans-serif;
        padding: 24px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        max-width: 1200px;
    }

    .value-fields {
        display: flex;
        gap: 6px;
        align-items: center;
        justify-content: center;
    }

    .value-fields input[type="text"] {
        width: 100%;
        flex: 1;
    }

    .monthly-extra-col {
        display: none;
    }

    .value-fields.is-monthly .monthly-extra-col {
        display: block;
    }

    .ps-value-col {
        flex: 1;
        min-width: 0;
        text-align: center;
    }

    .ps-value-label {
        display: none;
        font-size: 10px;
        font-weight: bold;
        line-height: 1.2;
        margin-bottom: 4px;
        color: #333;
        white-space: nowrap;
    }

    .value-fields.is-monthly .ps-value-label {
        display: block;
    }

    th, td {
        border: 1px solid #000;
        padding: 10px;
        text-align: center;
    }

    th {
        background: #f2f2f2;
    }

    select,
    input[type="text"],
    input[type="date"] {
        width: 95%;
        padding: 6px;
        border: 1px solid #555;
        text-align: center;
        box-sizing: border-box;
    }

    .submit-row {
        margin-top: 16px;
    }

    .btn {
        padding: 8px 16px;
        border: none;
        cursor: pointer;
        color: #fff;
        border-radius: 4px;
    }

    .btn-add {
        background: #28a745;
        margin-top: 10px;
    }

    .btn-remove {
        background: #dc3545;
        padding: 6px 12px;
    }

    .btn-submit {
        background: #007bff;
    }

    #costOfPlot {
        background: #f7f7f7;
        font-weight: bold;
    }
</style>
</head>

<body>

<h2>Payment Schedule</h2>

<form method="post">

    <input type="hidden"
           name="booking_id"
           value="<?php echo @$booking->id; ?>">

    <table id="paymentScheduleTable">

        <thead>
            <tr>
                <th width="160">Date</th>
                <th>Heading 1</th>
                <th>Heading 2</th>
                <th>Value</th>
                <th width="80">Action</th>
            </tr>
        </thead>

        <tbody id="paymentScheduleBody">

        <?php

        // If saved data exists, use saved rows
        if (!empty($data)) {

            $rowNumber = 1;

            foreach ($data as $rowKey => $row):

                $selDate = psDateInputValue($row['date'] ?? '');
                $selH1 = $row['heading1'] ?? '';
                $selH2 = $row['heading2'] ?? '';
                if (strcasecmp($selH1, 'Monthly Installment') === 0) {
                    $selH1 = 'Monthly';
                }
                if (strcasecmp($selH2, 'Monthly Installment') === 0) {
                    $selH2 = 'Monthly';
                }
                $val   = $row['value'] ?? '';
                $repeatType = psRepeatType($selH1, $selH2);
                $isRepeat = $repeatType !== '';
                $repeatLabels = psRepeatLabels($repeatType);
                $valMonths = '';
                $valExtra = '';
                if ($isRepeat) {
                    list($val, $valMonths, $valExtra) = psSplitMonthlyParts($val, $row);
                }

        ?>

            <tr>

                <td>
                    <input
                        type="date"
                        name="rows[<?php echo $rowKey; ?>][date]"
                        class="form-control"
                        value="<?php echo h($selDate); ?>">
                </td>

                <td>
                    <select
                        name="rows[<?php echo $rowKey; ?>][heading1]"
                        class="form-control heading1-select"
                        onchange="toggleMonthlyExtra(this)">

                        <?php foreach ($heading1Options as $opt): ?>

                            <option value="<?php echo h($opt); ?>"
                                <?php echo ($opt === $selH1 ? 'selected' : ''); ?>>

                                <?php echo h($opt); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>
                </td>

                <td>
                    <select
                        name="rows[<?php echo $rowKey; ?>][heading2]"
                        class="form-control heading2-select"
                        onchange="toggleMonthlyExtra(this)">

                        <?php foreach ($heading2Options as $opt): ?>

                            <option value="<?php echo h($opt); ?>"
                                <?php echo ($opt === $selH2 ? 'selected' : ''); ?>>

                                <?php echo h($opt); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>
                </td>

                <td>
                    <div class="value-fields<?php echo $isRepeat ? ' is-monthly' : ''; ?>">
                        <div class="ps-value-col">
                            <div class="ps-value-label"><?php echo h($repeatLabels[0]); ?></div>
                            <input
                                type="text"
                                class="ps-amount ps-installment"
                                name="rows[<?php echo $rowKey; ?>][value]"
                                value="<?php echo h(psFormatAmountValue($val)); ?>"
                                placeholder="1000"
                                oninput="updateMonthlyTotal(this)">
                        </div>
                        <div class="ps-value-col monthly-extra-col">
                            <div class="ps-value-label"><?php echo h($repeatLabels[1]); ?></div>
                            <input
                                type="text"
                                class="ps-months"
                                name="rows[<?php echo $rowKey; ?>][value_months]"
                                value="<?php echo h($valMonths); ?>"
                                placeholder="2"
                                <?php echo $isRepeat ? '' : 'disabled'; ?>
                                oninput="updateMonthlyTotal(this)">
                        </div>
                        <div class="ps-value-col monthly-extra-col">
                            <div class="ps-value-label"><?php echo h($repeatLabels[2]); ?></div>
                            <input
                                type="text"
                                class="ps-amount ps-total"
                                name="rows[<?php echo $rowKey; ?>][value_extra]"
                                value="<?php echo h(psFormatAmountValue($valExtra)); ?>"
                                placeholder="2000"
                                <?php echo $isRepeat ? '' : 'disabled'; ?>
                                oninput="updateCostOfPlot()">
                        </div>
                    </div>
                </td>

                <td>
                    <button
                        type="button"
                        class="btn btn-remove"
                        onclick="removeRow(this)">
                        Remove
                    </button>
                </td>

            </tr>

        <?php

                $rowNumber++;

            endforeach;

        } else {

            // Create initial rows
            for ($i = 1; $i <= $numRows; $i++):
                $rowKey = "row{$i}";
        ?>

            <tr>

                <td>
                    <input
                        type="date"
                        name="rows[<?php echo $rowKey; ?>][date]"
                        class="form-control"
                        value="">
                </td>

                <td>
                    <select
                        name="rows[<?php echo $rowKey; ?>][heading1]"
                        class="form-control heading1-select"
                        onchange="toggleMonthlyExtra(this)"
                        required>

                        <?php foreach ($heading1Options as $opt): ?>

                            <option value="<?php echo h($opt); ?>">
                                <?php echo h($opt); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </td>

                <td>
                    <select
                        name="rows[<?php echo $rowKey; ?>][heading2]"
                        class="form-control heading2-select"
                        onchange="toggleMonthlyExtra(this)">

                        <?php foreach ($heading2Options as $opt): ?>

                            <option value="<?php echo h($opt); ?>">
                                <?php echo h($opt); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </td>

                <td>
                    <div class="value-fields">
                        <div class="ps-value-col">
                            <div class="ps-value-label">Monthly installment</div>
                            <input
                                type="text"
                                class="ps-amount ps-installment"
                                name="rows[<?php echo $rowKey; ?>][value]"
                                value=""
                                placeholder="0.00"
                                oninput="updateMonthlyTotal(this)">
                        </div>
                        <div class="ps-value-col monthly-extra-col">
                            <div class="ps-value-label">Months</div>
                            <input
                                type="text"
                                class="ps-months"
                                name="rows[<?php echo $rowKey; ?>][value_months]"
                                value=""
                                placeholder="2"
                                disabled
                                oninput="updateMonthlyTotal(this)">
                        </div>
                        <div class="ps-value-col monthly-extra-col">
                            <div class="ps-value-label">Total</div>
                            <input
                                type="text"
                                class="ps-amount ps-total"
                                name="rows[<?php echo $rowKey; ?>][value_extra]"
                                value=""
                                placeholder="2000"
                                disabled
                                oninput="updateCostOfPlot()">
                        </div>
                    </div>
                </td>

                <td>
                    <button
                        type="button"
                        class="btn btn-remove"
                        onclick="removeRow(this)">
                        Remove
                    </button>
                </td>

            </tr>

        <?php
            endfor;
        }
        ?>

        </tbody>

    </table>

    <button
        type="button"
        class="btn btn-add"
        onclick="addRow()">
        + Add Row
    </button>

    <br><br>

    <div style="width:20%">

        <label>Cost of Plot</label>

        <input
            type="text"
            class="ps-amount"
            id="costOfPlot"
            name="cost_of_plot"
            value="<?php echo h(psFormatAmountValue($cop)); ?>"
            readonly
            required>

    </div>

    <div class="submit-row">

        <button
            type="submit"
            class="btn btn-submit">
            Submit
        </button>

    </div>

</form>


<script>

const heading1Options = <?php echo json_encode($heading1Options); ?>;
const heading2Options = <?php echo json_encode($heading2Options); ?>;

let rowCounter = <?php echo count($data) > 0 ? count($data) + 1 : $numRows + 1; ?>;


// Add new row
function addRow() {

    const tbody = document.getElementById('paymentScheduleBody');

    const rowKey = 'row' + rowCounter;

    const row = document.createElement('tr');

    // Heading 1 dropdown
    let heading1Html = '';

    heading1Options.forEach(function(option) {

        heading1Html += `
            <option value="${escapeHtml(option)}">
                ${escapeHtml(option)}
            </option>
        `;

    });


    // Heading 2 dropdown
    let heading2Html = '';

    heading2Options.forEach(function(option) {

        heading2Html += `
            <option value="${escapeHtml(option)}">
                ${escapeHtml(option)}
            </option>
        `;

    });


    row.innerHTML = `

        <td>
            <input
                type="date"
                name="rows[${rowKey}][date]"
                class="form-control"
                value="">
        </td>

        <td>

            <select
                name="rows[${rowKey}][heading1]"
                class="form-control heading1-select"
                onchange="toggleMonthlyExtra(this)"
                required>

                ${heading1Html}

            </select>

        </td>


        <td>

            <select
                name="rows[${rowKey}][heading2]"
                class="form-control heading2-select"
                onchange="toggleMonthlyExtra(this)"
                required>

                ${heading2Html}

            </select>

        </td>


        <td>
            <div class="value-fields">
                <div class="ps-value-col">
                    <div class="ps-value-label">Monthly installment</div>
                    <input
                        type="text"
                        class="ps-amount ps-installment"
                        name="rows[${rowKey}][value]"
                        value=""
                        placeholder="1000"
                        required
                        oninput="updateMonthlyTotal(this)">
                </div>
                <div class="ps-value-col monthly-extra-col">
                    <div class="ps-value-label">Months</div>
                    <input
                        type="text"
                        class="ps-months"
                        name="rows[${rowKey}][value_months]"
                        value=""
                        placeholder="2"
                        disabled
                        oninput="updateMonthlyTotal(this)">
                </div>
                <div class="ps-value-col monthly-extra-col">
                    <div class="ps-value-label">Total</div>
                    <input
                        type="text"
                        class="ps-amount ps-total"
                        name="rows[${rowKey}][value_extra]"
                        value=""
                        placeholder="2000"
                        disabled
                        oninput="updateCostOfPlot()">
                </div>
            </div>
        </td>


        <td>

            <button
                type="button"
                class="btn btn-remove"
                onclick="removeRow(this)">

                Remove

            </button>

        </td>

    `;


    tbody.appendChild(row);
    toggleMonthlyExtra(row.querySelector('.heading1-select') || row.querySelector('.heading2-select'));
    rowCounter++;
    updateCostOfPlot();
}


function getRepeatType(value) {
    const heading = (value || '').toLowerCase();
    if (heading === 'monthly' || heading === 'monthly installment') {
        return 'monthly';
    }
    if (heading === 'yearly') {
        return 'yearly';
    }
    if (heading === 'half yearly') {
        return 'half_yearly';
    }
    if (heading === 'quarterly' || heading === 'quarterly installment') {
        return 'quarterly';
    }
    return '';
}

function getRepeatLabels(type) {
    if (type === 'yearly') {
        return ['Yearly installment', 'Years', 'Total'];
    }
    if (type === 'half_yearly') {
        return ['Half yearly installment', 'Times', 'Total'];
    }
    if (type === 'quarterly') {
        return ['Quarterly installment', 'Times', 'Total'];
    }
    return ['Monthly installment', 'Months', 'Total'];
}

function toggleMonthlyExtra(select) {
    if (!select) {
        return;
    }

    const row = select.closest('tr');
    if (!row) {
        return;
    }

    const wrap = row.querySelector('.value-fields');
    const extras = row.querySelectorAll('.monthly-extra-col input');
    const heading1 = row.querySelector('.heading1-select');
    const heading2 = row.querySelector('.heading2-select');
    const type = getRepeatType(heading1 ? heading1.value : '')
        || getRepeatType(heading2 ? heading2.value : '');
    const isRepeat = type !== '';

    if (wrap) {
        wrap.classList.toggle('is-monthly', isRepeat);
        const labels = getRepeatLabels(type);
        wrap.querySelectorAll('.ps-value-label').forEach(function(label, index) {
            if (labels[index]) {
                label.textContent = labels[index];
            }
        });
    }
    extras.forEach(function(extra) {
        extra.disabled = !isRepeat;
        if (!isRepeat) {
            extra.value = '';
        }
    });
    if (isRepeat) {
        updateMonthlyTotal(row.querySelector('.ps-installment') || row.querySelector('.ps-months'));
    } else {
        updateCostOfPlot();
    }
}

function parsePlainNumber(value) {
    const number = parseFloat(String(value || '').replace(/,/g, ''));
    return isNaN(number) ? 0 : number;
}

function updateMonthlyTotal(input) {
    if (input) {
        const row = input.closest('tr');
        if (row) {
            const wrap = row.querySelector('.value-fields');
            if (wrap && wrap.classList.contains('is-monthly')) {
                const installment = parsePlainNumber((row.querySelector('.ps-installment') || {}).value);
                const months = parsePlainNumber((row.querySelector('.ps-months') || {}).value);
                const totalInput = row.querySelector('.ps-total');
                if (totalInput && installment > 0 && months > 0) {
                    totalInput.value = formatAmountValue(String(installment * months));
                }
            }
        }
    }
    updateCostOfPlot();
}

function updateCostOfPlot() {
    let total = 0;
    document.querySelectorAll('#paymentScheduleBody tr').forEach(function(row) {
        const wrap = row.querySelector('.value-fields');
        if (wrap && wrap.classList.contains('is-monthly')) {
            total += parsePlainNumber((row.querySelector('.ps-total') || {}).value);
        } else {
            total += parsePlainNumber((row.querySelector('.ps-installment') || {}).value);
        }
    });
    const copInput = document.getElementById('costOfPlot');
    if (copInput) {
        copInput.value = formatAmountValue(String(total));
    }
}


document.querySelectorAll('.heading1-select, .heading2-select').forEach(function(select) {
    toggleMonthlyExtra(select);
});
updateCostOfPlot();


// Remove row
function removeRow(button) {

    const row = button.closest('tr');

    if (row) {
        row.remove();
    }
    updateCostOfPlot();

}


// Escape HTML
function escapeHtml(value) {

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

}

function formatAmountValue(value) {
    return String(value || '').replace(/\d+(?:\.\d+)?/g, function (number) {
        var parts = number.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.join('.');
    });
}

document.addEventListener('focusout', function (event) {
    if (!event.target.classList.contains('ps-amount')) {
        return;
    }
    event.target.value = formatAmountValue(event.target.value.replace(/,/g, ''));
});

</script>

</body>
</html>