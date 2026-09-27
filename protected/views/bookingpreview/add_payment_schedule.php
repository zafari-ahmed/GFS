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

function psIsMonthlyHeading($heading1, $heading2 = '') {
    $values = array(
        strtolower(trim((string)$heading1)),
        strtolower(trim((string)$heading2)),
    );
    return in_array('monthly', $values, true)
        || in_array('monthly installment', $values, true);
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

function psSplitMonthlyValue($value) {
    $main = trim((string)$value);
    $extra = '';
    $firstLine = preg_split("/\r\n|\n/", $main);
    $main = trim($firstLine[0]);
    if (strpos($main, '=') !== false) {
        $parts = preg_split('/\s*=\s*/', $main, 2);
        $main = trim($parts[0]);
        $extra = isset($parts[1]) ? trim($parts[1]) : '';
    }
    return array($main, $extra);
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

    .monthly-extra-input {
        display: none;
    }

    .monthly-extra-input.is-visible {
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
                $isMonthly = psIsMonthlyHeading($selH1, $selH2);
                $valExtra = '';
                if ($isMonthly) {
                    list($val, $valExtra) = psSplitMonthlyValue($val);
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
                    <div class="value-fields">
                        <input
                            type="text"
                            class="ps-amount"
                            name="rows[<?php echo $rowKey; ?>][value]"
                            value="<?php echo h(psFormatAmountValue($val)); ?>"
                            placeholder="0.00"
                            >
                        <input
                            type="text"
                            class="monthly-extra-input ps-amount<?php echo $isMonthly ? ' is-visible' : ''; ?>"
                            name="rows[<?php echo $rowKey; ?>][value_extra]"
                            value="<?php echo h(psFormatAmountValue($valExtra)); ?>"
                            placeholder="Total"
                            <?php echo $isMonthly ? '' : 'disabled'; ?>>
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
                        <input
                            type="text"
                            class="ps-amount"
                            name="rows[<?php echo $rowKey; ?>][value]"
                            value=""
                            placeholder="0.00">
                        <input
                            type="text"
                            class="monthly-extra-input ps-amount"
                            name="rows[<?php echo $rowKey; ?>][value_extra]"
                            value=""
                            placeholder="Total"
                            disabled>
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
            name="cost_of_plot"
            value="<?php echo h(psFormatAmountValue($cop)); ?>"
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
                <input
                    type="text"
                    class="ps-amount"
                    name="rows[${rowKey}][value]"
                    value=""
                    placeholder="100 * 20"
                    required>
                <input
                    type="text"
                    class="monthly-extra-input ps-amount"
                    name="rows[${rowKey}][value_extra]"
                    value=""
                    placeholder="Total"
                    disabled>
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
}


function isMonthlyHeading(value) {
    const heading = (value || '').toLowerCase();
    return heading === 'monthly' || heading === 'monthly installment';
}

function toggleMonthlyExtra(select) {
    if (!select) {
        return;
    }

    const row = select.closest('tr');
    if (!row) {
        return;
    }

    const extra = row.querySelector('.monthly-extra-input');
    if (!extra) {
        return;
    }

    const heading1 = row.querySelector('.heading1-select');
    const heading2 = row.querySelector('.heading2-select');
    const isMonthly = isMonthlyHeading(heading1 ? heading1.value : '')
        || isMonthlyHeading(heading2 ? heading2.value : '');
    extra.classList.toggle('is-visible', isMonthly);
    extra.disabled = !isMonthly;
    extra.style.display = isMonthly ? 'block' : 'none';
}


document.querySelectorAll('.heading1-select, .heading2-select').forEach(function(select) {
    toggleMonthlyExtra(select);
});


// Remove row
function removeRow(button) {

    const row = button.closest('tr');

    if (row) {
        row.remove();
    }

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