<?php $userModel = Yii::app()->session->get('userModel');?>
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Direct Link</h1>
        <?php
            foreach(Yii::app()->user->getFlashes() as $key => $message) {
                echo '<div class="alert alert-info alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'.$message.'</div>';
            }
        ?>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">Filter</div>
            <div class="panel-body">
                <form method="GET" action="<?php echo Yii::app()->baseUrl?>/booking/directlink">
                    <div class="row">
                        <div class="form-group col-lg-2">
                            <label>Block Number</label>
                            <select name="block_number" id="direct_block_number" class="form-control select2">
                                <option value="">Select block number</option>
                                <?php foreach($blocks as $block):?>
                                    <option value="<?php echo CHtml::encode($block->block_number)?>" <?php echo ($block_number==$block->block_number)?'selected':''?>><?php echo CHtml::encode($block->block_number)?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                        <div class="form-group col-lg-2">
                            <label>Plot Number</label>
                            <select name="plot_number" id="direct_plot_number" class="form-control select2">
                                <option value="">All Plot Numbers</option>
                                <?php foreach($plotOptions as $plotNumber):?>
                                    <option value="<?php echo CHtml::encode($plotNumber)?>" <?php echo ($plot_number==$plotNumber)?'selected':''?>><?php echo CHtml::encode($plotNumber)?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                        <div class="form-group col-lg-3">
                            <label>Customer Name</label>
                            <input type="text" name="customer_name" class="form-control" placeholder="Customer Name" value="<?php echo CHtml::encode(@$customer_name)?>" autocomplete="off">
                        </div>
                        <div class="form-group col-lg-3">
                            <label>Customer CNIC</label>
                            <input type="text" name="customer_cnic" class="form-control cnic" placeholder="Customer CNIC" value="<?php echo CHtml::encode(@$customer_cnic)?>" autocomplete="off">
                        </div>
                        <div class="form-group col-lg-2">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" name="search" value="1" class="btn btn-success">Search</button>
                                <a href="<?php echo Yii::app()->baseUrl?>/booking/directlink" class="btn btn-default">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php if($searched):?>
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php if(!empty($block_number) && !empty($plot_number)):?>
                    Block <?php echo CHtml::encode($block_number)?> / Plot <?php echo CHtml::encode($plot_number)?>
                <?php elseif(!empty($block_number)):?>
                    All plots in Block <?php echo CHtml::encode($block_number)?>
                <?php else:?>
                    Search Result
                <?php endif;?>
            </div>
            <div class="panel-body">
                <table width="100%" class="table table-striped table-bordered table-hover" <?php echo $bookings ? 'id="dataTables"' : '';?>>
                    <thead>
                        <tr>
                            <th>Plot #</th>
                            <th>Reg. No.</th>
                            <th>Customer Name</th>
                            <th>Customer CNIC</th>
                            <th>Customer Mobile</th>
                            <th>Payments</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($bookings): foreach($bookings as $booking):
                            $netTotal = intval(@$booking->customerPlotTransactionSum);
                        ?>
                            <tr>
                                <td><strong>*<?php echo CHtml::encode(@$booking->plot->plot_type.'-'.@$booking->plot->plot_number.'-'.@$booking->plot->block_number)?>*</strong></td>
                                <td><?php echo $this->getBookingRegNo($booking->id)?></td>
                                <td><?php echo CHtml::encode(@$booking->customer->name)?></td>
                                <td><?php echo CHtml::encode(@$booking->customer->cnic)?></td>
                                <td><?php echo CHtml::encode(@$booking->customer->mobile)?></td>
                                <td><?php echo 'Rs. '.$this->plotTotal($booking->plot->id).' / Rs. '.number_format($netTotal);?></td>
                                <td>
                                    <a href="<?php echo Yii::app()->baseUrl?>/booking/viewbooking/<?php echo $booking->id?>" class="btn btn-info btn-xs" target="_blank">View Booking</a>
                                    <a href="<?php echo Yii::app()->baseUrl?>/booking/addtransaction/<?php echo $booking->id?>" class="btn btn-success btn-xs" target="_blank">Add Transaction</a>
                                    <a href="<?php echo Yii::app()->baseUrl?>/booking/bookingledger/<?php echo $booking->id?>" class="btn btn-warning btn-xs" target="_blank">View Ledger</a>
                                </td>
                            </tr>
                        <?php endforeach; else:?>
                            <tr>
                                <td colspan="7" style="text-align:center;color:#999;padding:20px;">No bookings found</td>
                            </tr>
                        <?php endif;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif;?>
<script>
$(function () {
    $('#direct_block_number').on('change', function () {
        var block = $(this).val();
        var $plot = $('#direct_plot_number');
        $plot.html('<option value="">All Plot Numbers</option>');
        if (!block) {
            $plot.trigger('change');
            return;
        }
        $.ajax({
            url: '<?php echo Yii::app()->baseUrl?>/booking/directlinkplots',
            type: 'GET',
            data: {block: block},
            success: function (response) {
                var data = (typeof response === 'string') ? JSON.parse(response) : response;
                if (data.success) {
                    $plot.html(data.data);
                    $plot.trigger('change');
                }
            }
        });
    });
});
</script>
