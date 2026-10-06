<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Bookings Sale Summary</h1>
        <?php
            foreach(Yii::app()->user->getFlashes() as $key => $message) {
                echo '<div class="alert alert-info alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'.$message.'</div>';
            }
        ?>
    </div>
    <!-- /.col-lg-12 -->
</div>
<?php $userModel = Yii::app()->session->get('userModel'); ?>
<?php 
    $params = $_GET;
    array_shift($params);
    $params = http_build_query($params);
?>
<!-- /.row -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                All Bookings Summary
                <span class="pull-right">
                    <a href="<?php echo Yii::app()->baseUrl?>/booking/reportallsales"><span class="label label-success">Export</span></a>
                </span>
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables">
                    <thead>
                        <tr>
                            <th class="hide">#</th>
                            <th>Plot #</th>
                            <th>Customer Name</th>
                            <th>Reg. No.</th>
                            <th>Cost of Land</th>
                            <th>Discount</th>
                            <th>Total Cost of Land</th>
                            <th>Extra Charges</th>
                            <th>Plot Total</th>
                            <th>Paid Total</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $col = $dis = $tcol = $ext = $plt = $pt = $bal = 0; $list = true;if($bookings){ foreach($bookings as $booking):
                        if($documentFlag){
                            if($booking->CPDCount == 6){
                                $list = true;
                            } else{
                                $list = false;    
                            }
                            
                        } else{
                            $list = true;
                        }
                        
                        if($list){
                        $costOfLand = (float)$booking->plot->total;
                        $discount = (float)$booking->plot->discount;
                        $totalCostOfLand = $costOfLand - $discount;
                        $extraCharges = 0;
                        if ($booking->plot->is_corner == 1) {
                            $extraCharges += (float)$this->Percentage($costOfLand, $booking->plot->is_corner_amount, false);
                        }
                        if ($booking->plot->is_road_facing == 1) {
                            $extraCharges += (float)$this->Percentage($costOfLand, $booking->plot->is_road_facing_amount, false);
                        }
                        if ($booking->plot->is_park_facing == 1) {
                            $extraCharges += (float)$this->Percentage($costOfLand, $booking->plot->is_park_facing_amount, false);
                        }
                        if ($booking->plot->is_west_open == 1) {
                            $extraCharges += (float)$this->Percentage($costOfLand, $booking->plot->is_west_open_amount, false);
                        }
                        $plotTotalText = $totalCostOfLand + $extraCharges;
                        $paidTotal = intval(@$booking->customerPlotTransactionSum) + intval(@$booking->customerPlotExtraTransactionSum);
                        $balanceAmount = $plotTotalText - $paidTotal;

                        $col += $costOfLand;
                        $dis += $discount;
                        $tcol += $totalCostOfLand;
                        $ext += $extraCharges;
                        $plt += $plotTotalText;
                        $pt += $paidTotal;
                        $bal += $balanceAmount;
                        ?>
                       		<tr>
                                <td class="hidden"><?php echo $booking->id?></td>
	                            
                                <td><a href="<?php echo Yii::app()->baseUrl?>/booking/viewbooking/<?php echo $booking->id?>"><?php echo '*'.$booking->plot->block_number.'-'.$booking->plot->plot_type.'-'.$booking->plot->plot_number?>*</a></td>
                                <td><?php echo $booking->customer->name?></td>
                                <td><?php echo $this->getBookingRegNo($booking->id)?></td>
                                <td><?php echo 'Rs. '.number_format($costOfLand)?></td>
                                <td><?php echo 'Rs. '.number_format($discount)?></td>
                                <td><?php echo 'Rs. '.number_format($totalCostOfLand)?></td>
                                <td><?php echo 'Rs. '.number_format($extraCharges)?></td>
                                <td><?php echo 'Rs. '.number_format($plotTotalText)?></td>
                                <td><?php echo 'Rs. '.number_format($paidTotal)?></td>
                                <td><?php echo 'Rs. '.number_format($balanceAmount)?></td>
	                        </tr>
                            <?php }?>
                       <?php endforeach;}?>
                    </tbody>
                </table>
                <table width="100%" class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th colspan="3">Total</th>
                            <th>Cost of Land</th>
                            <th>Discount</th>
                            <th>Total Cost of Land</th>
                            <th>Extra Charges</th>
                            <th>Plot Total</th>
                            <th>Paid Total</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="font-weight: bold;">
                            <td colspan="3">Total</td>
                            <td><?php echo 'PKR '.number_format($col)?></td>
                            <td><?php echo 'PKR '.number_format($dis)?></td>
                            <td><?php echo 'PKR '.number_format($tcol)?></td>
                            <td><?php echo 'PKR '.number_format($ext)?></td>
                            <td><?php echo 'PKR '.number_format($plt)?></td>
                            <td><?php echo 'PKR '.number_format($pt)?></td>
                            <td><?php echo 'PKR '.number_format($bal)?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-12 -->
</div>