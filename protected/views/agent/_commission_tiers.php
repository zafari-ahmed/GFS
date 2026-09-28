<?php
$tiers = isset($tiers) ? $tiers : array();
if (!$tiers) {
    $tiers = array((object)array('min_bookings' => '', 'max_bookings' => '', 'percentage' => ''));
}
?>
<div class="col-lg-12" style="margin-top: 10px; margin-bottom: 15px;">
    <div class="panel panel-info">
        <div class="panel-heading">
            Commission by Booking Count
            <span class="pull-right">
                <button type="button" class="btn btn-success btn-xs" id="addCommissionTier">Add Slab</button>
            </span>
        </div>
        <div class="panel-body">
            <p class="help-block" style="margin-top:0;">
                Set commission against how many customer plots this dealer has booked.
                Leave <strong>To</strong> empty for onwards (example: 25 onwards = 25%).
            </p>
            <div class="row" style="font-weight:bold;margin-bottom:6px;">
                <div class="col-lg-3">From (bookings)</div>
                <div class="col-lg-3">To (bookings)</div>
                <div class="col-lg-3">Commission %</div>
                <div class="col-lg-3"></div>
            </div>
            <div id="commissionTierRows">
                <?php foreach ($tiers as $tier): ?>
                <div class="row commission-tier-row" style="margin-bottom:8px;">
                    <div class="col-lg-3">
                        <input type="number" min="1" class="form-control" name="commission_min[]" placeholder="e.g. 1" value="<?php echo CHtml::encode($tier->min_bookings)?>">
                    </div>
                    <div class="col-lg-3">
                        <input type="number" min="1" class="form-control" name="commission_max[]" placeholder="Empty = onwards" value="<?php echo CHtml::encode($tier->max_bookings)?>">
                    </div>
                    <div class="col-lg-3">
                        <input type="number" min="0" step="0.01" class="form-control" name="commission_percent[]" placeholder="e.g. 10" value="<?php echo CHtml::encode($tier->percentage)?>">
                    </div>
                    <div class="col-lg-3">
                        <button type="button" class="btn btn-danger btn-sm removeCommissionTier">Remove</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(function () {
    $('#addCommissionTier').off('click.commissionTier').on('click.commissionTier', function () {
        var $row = $('#commissionTierRows .commission-tier-row:first').clone();
        $row.find('input').val('');
        $('#commissionTierRows').append($row);
    });
    $(document).off('click.commissionTier', '.removeCommissionTier').on('click.commissionTier', '.removeCommissionTier', function () {
        if ($('#commissionTierRows .commission-tier-row').length > 1) {
            $(this).closest('.commission-tier-row').remove();
        } else {
            $(this).closest('.commission-tier-row').find('input').val('');
        }
    });
});
</script>
