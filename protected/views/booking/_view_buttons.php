<?php
/**
 * Booking view top buttons.
 *
 * Change 'show' on any button below.
 * Helpers already defined:
 *   $isAdmin, $isType5, $canManageBooking, $isActiveBooking
 *
 * Visible now: Add PS, Payment Schedule, Application Form,
 * Terms & Condition, Allocation Letter, Booking Ledger.
 * To enable another button, remove the leading "false &&".
 */
$userTypeId = isset($userModel['user_type']['id']) ? (int)$userModel['user_type']['id'] : 0;
$isAdmin = ($userTypeId === 1);
$isType5 = ($userTypeId === 5);
$canManageBooking = ($isAdmin || $isType5);
$isActiveBooking = empty($booking->customerPlotCancelled) && (int)$booking->status !== 3;
$baseUrl = Yii::app()->baseUrl;
$bookingId = (int)$booking->id;
$plotId = (int)$booking->plot->id;

$flagStatus = isset($booking->flag_status) ? (int)$booking->flag_status : 0;
if ($flagStatus === 2) {
    $fileFlag = array('label' => 'Received By Customer', 'url' => 'javascript:void(0)', 'class' => 'btn btn-success btn-sm');
} elseif ($flagStatus === 1) {
    $fileFlag = array('label' => 'Rec By Customer?', 'url' => $baseUrl.'/api/setbookingflag/view/1/flag/2/booking/'.$bookingId, 'class' => 'flagLink btn btn-primary btn-sm');
} else {
    $fileFlag = array('label' => 'File Complete?', 'url' => $baseUrl.'/api/setbookingflag/view/1/flag/1/booking/'.$bookingId, 'class' => 'flagLink btn btn-danger btn-sm');
}

$bookingTopButtons = array(
    array(
        'label' => 'Add PS',
        'url' => $baseUrl.'/bookingpreview/addps/'.$bookingId,
        'class' => 'btn btn-info btn-sm',
        'target' => '_blank',
        'show' => $canManageBooking && $isActiveBooking,
    ),
    array(
        'label' => 'Payment Schedule',
        'url' => $baseUrl.'/bookingpreview/payment/'.$bookingId,
        'class' => 'btn btn-success btn-sm',
        'target' => '_blank',
        'show' => $canManageBooking && $isActiveBooking,
    ),
    array(
        'label' => 'Application Form',
        'url' => $baseUrl.'/bookingpreview/applicationform/'.$bookingId,
        'class' => 'btn btn-success btn-sm',
        'target' => '_blank',
        'show' => $canManageBooking && $isActiveBooking,
    ),
    array(
        'label' => 'Terms & Condition',
        'url' => $baseUrl.'/bookingpreview/applicationterms/'.$bookingId,
        'class' => 'btn btn-success btn-sm',
        'target' => '_blank',
        'show' => $canManageBooking && $isActiveBooking,
    ),
    array(
        'label' => 'Allocation Letter',
        'url' => $baseUrl.'/bookingpreview/allocation/'.$bookingId,
        'class' => 'btn btn-success btn-sm',
        'target' => '_blank',
        'show' => $canManageBooking && $isActiveBooking,
    ),
    array(
        'label' => 'Booking Ledger',
        'url' => $baseUrl.'/booking/bookingledger/'.$bookingId,
        'class' => 'btn btn-warning btn-sm',
        'show' => $canManageBooking && $isActiveBooking,
    ),
    array(
        'label' => 'Welcome Letter',
        'url' => $baseUrl.'/bookingpreview/welcome/'.$bookingId,
        'class' => 'btn btn-success btn-sm',
        'target' => '_blank',
        'show' => false && $canManageBooking && $isActiveBooking,
    ),
    array(
        'label' => 'Confirmation',
        'url' => $baseUrl.'/bookingpreview/confirmation/'.$bookingId,
        'class' => 'btn btn-success btn-sm',
        'target' => '_blank',
        'show' => false && $canManageBooking && $isActiveBooking,
    ),
    array(
        'label' => 'Generate Booking Form',
        'url' => $baseUrl.'/bookingpreview/duplicate/'.$bookingId,
        'class' => 'btn btn-info btn-sm',
        'target' => '_blank',
        'show' => false && $isActiveBooking,
    ),
    array(
        'label' => 'Generate Transfer Letter',
        'url' => $baseUrl.'/booking/bookingtransferletter/'.$bookingId,
        'class' => 'btn btn-info btn-sm',
        'target' => '_blank',
        'show' => false && $isAdmin && $isActiveBooking,
    ),
    array(
        'label' => 'Booking Message',
        'url' => $baseUrl.'/api/messages/type/booking/id/'.$bookingId,
        'class' => 'btn btn-primary btn-sm',
        'linkClass' => 'performTask',
        'show' => false && $isAdmin && $isActiveBooking,
    ),
    array(
        'label' => 'Generate Certificate',
        'url' => 'javascript:void(0)',
        'class' => 'btn btn-info btn-sm',
        'modal' => '#generateCert',
        'show' => false && $isAdmin && $isActiveBooking,
    ),
    array(
        'label' => $fileFlag['label'],
        'url' => $fileFlag['url'],
        'class' => $fileFlag['class'],
        'show' => false && $isAdmin && $isActiveBooking,
    ),
    array(
        'label' => 'Cancel Booking',
        'url' => $baseUrl.'/booking/cancel/'.$bookingId,
        'class' => 'btn btn-danger btn-sm',
        'id' => 'cancelBooking',
        'show' => false && $isAdmin && $isActiveBooking,
    ),
    array(
        'label' => 'Block Booking',
        'url' => 'javascript:void(0)',
        'class' => 'btn btn-info btn-sm',
        'modal' => '#bookingReason',
        'show' => false && $isActiveBooking,
    ),
    array(
        'label' => 'Transfer Booking',
        'url' => $baseUrl.'/booking/transfer/'.$bookingId,
        'class' => 'btn btn-success btn-sm',
        'id' => 'transferBooking',
        'show' => false && $canManageBooking && $isActiveBooking,
    ),
    array(
        'label' => 'Add Transaction',
        'url' => $baseUrl.'/booking/addtransaction/'.$bookingId,
        'class' => 'btn btn-success btn-sm',
        'show' => true && $canManageBooking,
    ),
    array(
        'label' => 'Edit Booking',
        'url' => $baseUrl.'/booking/editbooking/'.$bookingId,
        'class' => 'btn btn-primary btn-sm',
        'show' => true && $isAdmin && $isActiveBooking,
    ),
    array(
        'label' => 'Edit Plot',
        'url' => $baseUrl.'/plot/edit/'.$plotId,
        'class' => 'btn btn-primary btn-sm',
        'show' => true   && $canManageBooking,
    ),
    array(
        'label' => 'Custom Payment Schedule',
        'url' => $baseUrl.'/paymentschedule/customschedule/id/'.$bookingId,
        'class' => 'btn btn-primary btn-sm',
        'show' => false && $isAdmin && $isActiveBooking,
    ),
);
?>
<div class="booking-top-buttons">
<?php foreach ($bookingTopButtons as $btn):
    if (empty($btn['show'])) {
        continue;
    }
    $href = isset($btn['url']) ? $btn['url'] : 'javascript:void(0)';
    $target = !empty($btn['target']) ? ' target="'.CHtml::encode($btn['target']).'"' : '';
    $linkId = !empty($btn['id']) ? ' id="'.CHtml::encode($btn['id']).'"' : '';
    $linkClass = !empty($btn['linkClass']) ? ' class="'.CHtml::encode($btn['linkClass']).'"' : '';
    $modal = '';
    if (!empty($btn['modal'])) {
        $modal = ' data-toggle="modal" data-target="'.CHtml::encode($btn['modal']).'"';
    }
?>
    <a href="<?php echo CHtml::encode($href); ?>"<?php echo $linkId.$linkClass.$target; ?>>
        <button type="button" class="<?php echo CHtml::encode($btn['class']); ?>"<?php echo $modal; ?>><?php echo CHtml::encode($btn['label']); ?></button>
    </a>
<?php endforeach; ?>
</div>
