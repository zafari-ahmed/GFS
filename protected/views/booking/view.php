<?php $userModel = Yii::app()->session->get('userModel');?>
<?php $netTotal = ($this->plotTotal(@$booking->plot->id,false) - intval(@$booking->customerPlotTransactionSum) - intval(@$booking->customerPlotExtraTransactionSum))?>

<?php

// echo '<pre>';
// print_r($this->plotTotal(@$booking->plot->id,false));
// echo '<br/>';
// print_r(intval(@$booking->customerPlotTransactionSum));
// echo '<br/>';
// print_r(intval($this->plotDiscount(@$booking->plot->id,false)));
// exit;
$det = $this->getPlotLedgerDetail($booking->id);
$dues = $this->calculateBookingDues($booking->id);
$plotTotalAmount = $this->plotTotal(@$booking->plot->id, false);
$dealerCommission = ($booking->agent_id && $booking->agent) ? $booking->agent->getBookingCommissionBreakdown($booking, $plotTotalAmount) : null;
$buttonClass = 'hide';
if($userModel['user_type']['id'] == 1 || $userModel['user_type']['id'] == 5){
    $buttonClass = '';
}
?>
<style type="text/css">
    /*button[type="button"], input[type="button"] {
        display: none!important;
    }*/
    .hide{
        display: none!important;
    }
    .booking-top-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin: 0 0 24px;
    }
    .booking-top-buttons .btn {
        margin: 0;
    }
</style>
<div class="row">
    <div class="col-lg-12">
        
        <?php
            foreach(Yii::app()->user->getFlashes() as $key => $message) {
                echo '<div class="alert alert-'.$key.' alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'.$message.'</div>';
            }
        ?>
        <h1 class="page-header">View Booking (<?php echo $this->getBookingRegNo($booking->id)?>)</h1><br/>
        <?php if($booking->status!=3 && !empty($dues['due_items'])){ ?>
            <table width="100%" class="table table-striped table-bordered table-hover">
                <thead style="color: #3c763d;background-color: #dff0d8;    text-transform: UPPERCASE;font-weight: bold;">
                    <th>Payment Mode</th>
                    <th>Due Date</th>
                    <th>Scheduled</th>
                    <th>Paid</th>
                    <th>Due Months</th>
                    <th>Due Amount</th>
                </thead>
                <tbody>
                    <?php foreach ($dues['due_items'] as $dueItem): ?>
                    <tr>
                        <td><b><?php echo CHtml::encode($dueItem['label']); ?></b></td>
                        <td><?php echo !empty($dueItem['due_date']) ? date('d M, Y', strtotime($dueItem['due_date'])) : '-'; ?></td>
                        <td><?php echo 'PKR '.number_format(@$dueItem['scheduled_amount'], 2, '.', ','); ?></td>
                        <td><?php echo 'PKR '.number_format(@$dueItem['paid_amount'], 2, '.', ','); ?></td>
                        <td><?php echo !empty($dueItem['is_monthly']) ? @$dueItem['due_months'] : '-'; ?></td>
                        <td><b><?php echo 'PKR '.number_format(@$dueItem['due_amount'], 2, '.', ','); ?></b></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="background-color: #fcf8e3; font-weight: bold;">
                        <td colspan="5">Total Due</td>
                        <td><?php echo 'PKR '.number_format(@$dues['due_amount'], 2, '.', ','); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php } ?>
        <?php $this->renderPartial('_view_buttons', array(
            'booking' => $booking,
            'userModel' => $userModel,
        )); ?>
    <?php
        foreach(Yii::app()->user->getFlashes() as $key => $message) {
            echo '<div class="alert alert-'.$key.' alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><b>'.$message.'</b></div>';
        }
    ?>
    </div>
    <!-- /.col-lg-12 -->
</div>
<!-- /.row -->
<?php if(!empty($booking->customerPlotCancelled)){?>    
    <img src="<?php echo Yii::app()->baseUrl?>/images/cancelled.png" style="    position: absolute;z-index: 999;width: 50%;margin-left: 15%;margin-top: 5%;opacity: 0.1;">
<?php } ?>

<?php if($netTotal == 0){?>    
    <img src="<?php echo Yii::app()->baseUrl?>/images/completed.png" style="    position: absolute;z-index: 999;width: 80%;margin-left: 2%;margin-top: 5%;opacity: 0.1;">
<?php } ?>

<?php if($booking->blocked == 1 || ($booking->blocked == 2 && $booking->is_open==0)){?>    
    <img src="<?php echo Yii::app()->baseUrl?>/images/blocked.png" style="    position: absolute;z-index: 999;width: 80%;margin-left: 2%;margin-top: 5%;opacity: 0.1;">
<?php } ?>

<div class="row">
    <div class="col-lg-12">
        <?php if(!empty($booking->customerPlotCancelled)){?>
                <p>Cancelled Remarks: <?php echo @$booking->customerPlotCancelled[0]->reason?></p>
            <?php } ?>
        <div class="panel panel-<?php echo ($booking->status==1)?'success':'danger'?>">
            
            <div class="panel-heading">
                <b style="text-transform: UPPERCASE;font-weight: bold;">View Booking (<?php echo $booking->plot->block_number.' / '.$booking->plot->plot_number?>)</b>
                
                <?php if(count($booking->customerPlotCancelled) > 1){?>
                    <a href="<?php echo Yii::app()->baseUrl?>/report/cancelled"><span class="pull-right"><b><span class="label label-danger">Cancelled</span></b></span></a>
                <?php } else{?>
                    
                    <?php if($booking->status==1){?>
                        <span class="pull-right"><b><a target="_blank" href="<?php echo Yii::app()->baseUrl.'/booking/plotdetail/id/'.$booking->plot->id?>"><span class="label label-info">View Plot Detail</span></a>&nbsp;<span class="label label-success">Booked</span></b></span>
                    <?php } else if($booking->status==3){?>
                        <span class="pull-right"><b><span class="label label-danger">Transferred</span></b></span>
                    <?php } else if($booking->status==0){?>
                        <span class="pull-right"><b><span class="label label-danger">Cancelled</span></b></span>
                    <?php } ?>
                    <?php if($booking->plot->plotSitePlans){?>
                        <span class="pull-right" ><b><a style="margin-left: -5%;" target="_blank" href="<?php echo Yii::app()->baseUrl?>/uploads/plot/site_plan/<?php echo @$booking->plot->plotSitePlans[0]->site_plan?>"><span class="label label-success">View Plot Site Plan</span></a></b></span>
                    <?php }?>
                    <?php //}?>
                    <!-- <span class="pull-right"><b><?php //echo ($booking->status==1)?'<a target="_blank" href="'.Yii::app()->baseUrl.'/booking/plotdetail/id/'.$booking->plot->id.'"><span class="label label-info">View Plot Detail</span></a>&nbsp;<span class="label label-success">Booked</span>':'<span class="label label-danger">Temporary Booked</span>'?></b></span> -->
                <?php }?>
                
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <?php //if($booking->agent_id){?>



                <div class="col-lg-12">
                    <?php if($booking->warningLetters){?>
                    <h3 style="text-transform: UPPERCASE;font-weight: bold;">Warning Letters</h3>                        
                    <div class="col-lg-12" style="padding-left: 0px;">
                        <table width="100%" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Reference #</th>
                                    <th>Letter Date</th>
                                    <th>Tracking ID</th>
                                    <th>Received By</th>
                                    <th>Received Date</th>
                                </tr>
                            </thead>   
                            <tbody>
                                <?php foreach($booking->warningLetters as $letter):?>
                                <tr style="background-color: lightgray;">
                                    <td><b><?php echo @$letter->reference_number?></b></td>
                                    <td><b><?php echo date('d M,Y',strtotime(@$letter->createdOn))?></b></td>
                                    <td><b><?php echo @$letter->tracking_id?></b></td>
                                    <td><b><?php echo @$letter->received_by?></b></td>
                                    <td><b><?php echo ($letter->received_on)?date('d M,Y',strtotime(@$letter->received_on)):''?></b></td>
                                    
                                </tr>
                                <?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                    <?php }?>
                </div>
                
                <?php //}?>
                <div class="col-lg-12">
                    <span class=""><b><span class="label label-info">Updated By:&nbsp;&nbsp;&nbsp;<?php echo @$booking->updatedBy?></span></b></span>
                    <h3 style="text-transform: UPPERCASE;font-weight: bold;">Plot Information</h3>
                    
                    <div class="form-group col-lg-2" style="padding-left: 0px;">
                        <label>Plot Type</label>
                        <p><?php echo $booking->plot->plot_type?></p>
                    </div>
                    
                    <div class="form-group col-lg-2" >
                        <label>Plot #</label>
                        <p><?php echo $booking->plot->plot_number?></p>
                    </div>
                    
                    <div class="form-group col-lg-2" style="">
                        <label>Block #</label>
                        <p><?php echo $booking->plot->block_number?></p>
                    </div>

                    

                    
                    
                    <div class="form-group col-lg-2">
                        <label>Plot Category</label>
                        <p><?php echo $booking->plot->category->name?></p>
                    </div>
                    <div class="form-group col-lg-2" style="padding-right: 0px;">
                        <label>Plot Size</label>
                        <p><?php echo $booking->plot->size->size?></p>
                    </div>
                </div>
                <div class="col-lg-12">
                    <?php $class = 'col-lg-2';?>
                    <div class="form-group <?php echo $class?>" style="padding-left: 0px;">
                        <label>Corner</label>
                        <p><?php echo ($booking->plot->is_corner==1)?'<span class="label label-success">YES</span>':'<span class="label label-danger">No</span>'?></p>
                    </div>
                    <div class="form-group <?php echo $class?>">
                        <label>Park Facing</label>
                        <p><?php echo ($booking->plot->is_park_facing==1)?'<span class="label label-success">YES</span>':'<span class="label label-danger">No</span>'?></p>
                    </div>
                    <div class="form-group <?php echo $class?>" style="padding-right: 0px;">
                        <label>West Open</label>
                        <p><?php echo ($booking->plot->is_west_open==1)?'<span class="label label-success">YES</span>':'<span class="label label-danger">No</span>'?></p>
                    </div>
                    <div class="form-group <?php echo $class?>" style="padding-left: 0px;">
                        <label>Extra Land</label>
                        <p><?php echo ($booking->plot->is_road_facing==1)?'<span class="label label-success">YES</span>':'<span class="label label-danger">No</span>'?></p>
                    </div>

                    <?php if($booking->is_special != '' && $booking->is_special != 0){?>
                        <div class="form-group <?php echo $class?>" style="padding-right: 0px;">
                            <label>Payment Schedule</label>
                            <?php if($booking->customerpaymentSchedule){?>
                                <p><span class="label label-info"> <?php echo @$booking->special->name?>&nbsp;&nbsp;Custom PS</span></p>
                            <?php } else{?>
                                <p><span class="label label-info"> <?php echo @$booking->special->name?></span></p>
                            <?php }?>
                        </div>
                    <?php }?>


                    <div class="col-lg-12" style="padding-left: 0px;">
                        <table width="100%" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <?php if($booking->plot->discount!=0){?>
                                        <th>Total Discounted Cost of Land</th>
                                    <?php } else {?>
                                        <th>Total Cost of Land</th>
                                     <?php }?>
                                    <th>Booking Date</th>
                                    <th>Installment Start Date</th>
                                    <th>Transfer Date</th>
                                    <?php if($booking->plot->discount!=0){?>
                                        <th>Discount Amount</th>
                                    <?php }?>
                                    
                                </tr>
                            </thead>   
                            <tbody>
                                <?php if($booking->customerpaymentSchedule){ //if custom payment schedule on booking?>
                                    <tr style="background-color: lightgray;">
                                        <td><b><?php echo 'Rs. '.number_format($booking->customPaymentScheduleTotal())?></b></td>
                                        <td><b><?php echo date('d M,Y',strtotime($booking->createdOn))?></b></td>
                                        <td><b><?php echo date('d M,Y',strtotime($booking->monthly_start_date))?></b></td>
                                        <?php if($booking->plot->customerPlotTransfersRecent){?>
                                        <td><b><?php echo date('F d, Y',strtotime($booking->plot->customerPlotTransfersRecent[0]->createdOn))?></b></td>
                                        <?php } else { ?>
                                            <td>N/A</td>
                                        <?php }?>
                                        <td><b><?php echo 'Rs. '.number_format($booking->plot->total-$booking->customPaymentScheduleTotal())?></b></td>
                                    </tr>
                                <?php } else{?>
                                    <tr style="background-color: lightgray;">
                                        <td><b><?php echo 'Rs. '.number_format($booking->plot->total/*-$booking->plot->discount*/)?></b></td>
                                        <td><b><?php echo date('d M,Y',strtotime($booking->createdOn))?></b></td>
                                        <td><b><?php echo date('d M,Y',strtotime($booking->monthly_start_date))?></b></td>
                                        <?php if($booking->plot->customerPlotTransfersRecent){?>
                                        <td><b><?php echo date('F d, Y',strtotime($booking->plot->customerPlotTransfersRecent[0]->createdOn))?></b></td>
                                        <?php } else { ?>
                                            <td>N/A</td>
                                        <?php }?>
                                        <?php if($booking->plot->discount!=0){?>
                                            <td><b><?php echo 'Rs. '.number_format($booking->plot->discount)?></b></td>
                                        <?php }?>
                                    </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                    <?php $tpp = $booking->plot->total-$booking->plot->discount;?>
                    <?php if($booking->plot->is_road_facing == 1 || $booking->plot->is_park_facing == 1 || $booking->plot->is_corner == 1 || $booking->plot->is_west_open == 1){?>
                    <div class="col-lg-12" style="padding-left: 0px;">
                        <table width="100%" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Extra Charges Calculation</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="hide">
                                    <?php if($booking->plot->discount!=0){?>
                                        <td>Discounted Total Cost of Land</td>
                                    <?php } else {?>
                                        <td>Total Cost of Land</td>
                                    <?php }?>
                                    <td><?php echo 'Rs. '.number_format($booking->plot->total-$booking->plot->discount)?></td>
                                </tr>
                                
                                <?php /*if($booking->plot->is_road_facing == 1){?>
                                <tr>
                                    <?php $sqA = explode(' ',$booking->plot->size->size);?>
                                    <td>Extra Land (<?php echo round($tpp/$sqA[0])?>) / SQ YDS</td>
                                    <td><?php echo @$booking->plot->is_road_facing_amount?> SQ&nbsp;YDS * <?php echo $booking->plot->is_road_facing_amount*(round($tpp/$sqA[0])). ' - Rs. '.number_format($booking->plot->is_road_facing_amount*(round($tpp/$sqA[0])))?></td>
                                    <?php $tpp += $booking->plot->is_road_facing_amount*(round($tpp/$sqA[0]))?>
                                </tr>
                                <?php }*/ ?>
            
                                <?php if($booking->plot->is_corner == 1){?>
                                <tr>
                                    <td>Is Corner</td>
                                    <td><?php echo @$booking->plot->is_corner_amount?>% - <?php echo 'Rs. '.$this->Percentage($booking->plot->total,$booking->plot->is_corner_amount)?></td>
                                    <?php $tpp += $this->Percentage($booking->plot->total,$booking->plot->is_corner_amount,false)?>
                                </tr>
                                <?php } ?>

                                <?php if($booking->plot->is_road_facing == 1){?>
                                <tr>
                                    <td>Road Facing </td>
                                    <td><?php echo @$booking->plot->is_road_facing_amount?>% - <?php echo 'Rs. '.$this->Percentage($booking->plot->total,$booking->plot->is_road_facing_amount)?></td>
                                    <?php $tpp += $this->Percentage($booking->plot->total,$booking->plot->is_road_facing_amount,false)?>
                                </tr>
                                <?php } ?>
                                
                                <?php if($booking->plot->is_park_facing == 1){?>
                                <tr>
                                    <td>Park Facing </td>
                                    <td><?php echo @$booking->plot->is_park_facing_amount?>% - <?php echo 'Rs. '.$this->Percentage($booking->plot->total,$booking->plot->is_park_facing_amount)?></td>
                                    <?php $tpp += $this->Percentage($booking->plot->total,$booking->plot->is_park_facing_amount,false)?>
                                </tr>
                                <?php } ?>

                                

                                <?php if($booking->plot->is_west_open == 1){?>
                                <tr>
                                    <td>West Open</td>
                                    <td><?php echo @$booking->plot->is_west_open_amount?>% - <?php echo 'Rs. '.$this->Percentage($booking->plot->total,$booking->plot->is_west_open_amount)?></td>
                                    <?php $tpp += $this->Percentage($booking->plot->total,$booking->plot->is_west_open_amount,false)?>
                                </tr>
                                <?php } ?>

                                
                               <tr style="background-color: lightgray;"><td><b>Total Extra Charges Applicable</b></td><td><b><?php echo 'Rs. '.number_format(($tpp))?></b></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <?php }?>
                    <!-- <div class="form-group col-lg-6" style="padding-right: 0px;">
                        <label>West Open</label>
                        <p><?php //echo ($booking->plot->is_west_open==1)?'<span class="label label-success">YES</span>':'<span class="label label-danger">No</span>'?></p>
                    </div> -->
                </div>

                <div class="col-lg-12">
                    <h3 style="text-transform: UPPERCASE;font-weight: bold;">Customer Information</h3>
                    <div class="form-group col-lg-3" style="padding-left: 0px;">
                        <label>Name</label>
                        <p><?php echo $booking->customer->name?></p>
                    </div>
                    <div class="form-group col-lg-3">
                        <label><?php echo $booking->agent_name?></label>
                        <p><?php echo $booking->customer->father_husband_name?></p>
                    </div>
                    
                    <div class="form-group col-lg-2">
                        <label>Occupation</label>
                        <p><?php echo $booking->customer->occupation?></p>
                    </div>
                    <?php if($booking->agent_cnic != '' && $booking->agent_cnic != null && file_exists(Yii::app()->baseUrl.'/uploads/booking/'.$booking->agent_cnic)){?>
                    <div class="form-group col-lg-2">
                        <img style="width: 100%;" src="<?php echo Yii::app()->baseUrl?>/uploads/booking/<?php echo $booking->agent_cnic?>" alt="..." class="img-thumbnail">
                    </div>
                    <?php } else {?>
                        <div class="form-group col-lg-2">
                        <img style="width: 100%;" src="<?php echo Yii::app()->baseUrl?>/images/default.jpeg" alt="..." class="img-thumbnail">
                    </div>
                    <?php }?>
                </div>

                <div class="col-lg-12" style="padding-left: 0px;">
                    <div class="form-group col-lg-2">
                        <label>Guardian</label>
                        <p><?php echo @$booking->customer->guardian?></p>
                    </div>
                    <div class="form-group col-lg-2">
                        <label>Nationality</label>
                        <p><?php echo @$booking->customer->nationality?></p>
                    </div>
                    <div class="form-group col-lg-3" style="padding-left: 0px;">
                        <label>CNIC</label>
                        <p><?php echo $booking->customer->cnic?></p>
                    </div>
                    <div class="form-group col-lg-2" >
                        <label>Birth Date</label>
                        <p><?php echo date('d M,Y',strtotime(@$booking->customer->dob))?></p>
                    </div>
                    <?php if(@$booking->customer->email){?>
                        <div class="form-group col-lg-2" >
                            <label>Email Address</label>
                            <p><?php echo @$booking->customer->email?></p>
                        </div>
                    <?php }?>
                </div>
                <div class="col-lg-12">
                    <div class="form-group col-lg-10" style="padding-left: 0px;">
                        <label>Address</label>
                        <p><?php echo $booking->customer->address?></p>
                    </div>
                </div>

                <div class="col-lg-12">
                    <h3 style="text-transform: UPPERCASE;font-weight: bold;">Customer Contact Information</h3>
                    <div class="form-group col-lg-4" style="padding-left: 0px;">
                        <label>Office #</label>
                        <p><?php echo $booking->customer->office?></p>
                    </div>
                    <div class="form-group col-lg-4">
                        <label>Res Phone #</label>
                        <p><?php echo $booking->customer->phone?></p>
                    </div>
                    <div class="form-group col-lg-4">
                        <label>Mobile #</label>
                        <p><?php echo $booking->customer->mobile?></p>
                    </div>

                    <h3 style="text-transform: UPPERCASE;font-weight: bold;">Customer Nominee's Information</h3>
                    <div class="form-group col-lg-4" style="padding-left: 0px;">
                        <label>Nominee's Name</label>
                        <p><?php echo $booking->customer->nominee_name?></p>
                    </div>
                    <div class="form-group col-lg-4">
                        <label>Nominee's CNIC</label>
                        <p><?php echo $booking->customer->nominee_cnic?></p>
                    </div>
                    <div class="form-group col-lg-4">
                        <label>Nominee's Relation</label>
                        <p><?php echo $booking->customer->nominee_relation?></p>
                    </div>
                </div>
                <div class="col-lg-12">
                    <?php if($booking->customerPlotDocuments){?>
                    <h3 style="text-transform: UPPERCASE;font-weight: bold;">Customer Document Information</h3>
                    <?php foreach($this->documentTypes() as $types):?>
                        <?php $fileType = strtolower(str_replace(' ','-',$types)); if($this->getDocument($booking->id,$fileType)){?>
                        <div class="form-group col-lg-3" >
                            <label>Scanned Image (<?php echo ($fileType=='thumb-image')?'Nominee PP':$types?>)</label>
                            <?php //if($fileType != 'nadra-verification-form') {?>
                                <a target="_blank" href="<?php echo Yii::app()->baseUrl.'/uploads/booking/'.$this->getDocument($booking->id,$fileType)?>"><img src="<?php echo Yii::app()->baseUrl.'/uploads/booking/'.$this->getDocument($booking->id,$fileType)?>" alt="..." class="img-thumbnail" style="width: 50%;"></a>
                            <?php /*} else{?> 
                                <a href="<?php echo Yii::app()->baseUrl.'/uploads/booking/'.$this->getDocument($booking->id,$fileType)?>" target="_blank">Nadra Verification Form</a>
                            <?php }*/ ?>
                        </div>
                    <?php } endforeach; }?>
                </div>

                
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-12 -->

    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <b style="text-transform: UPPERCASE;font-weight: bold;">View Land Transactions</b>
                <!--<a href="<?php //echo Yii::app()->baseUrl?>/booking/getmonths/id/<?php //echo $booking->id?>"><span class="pull-right"><b><span class="label label-success">Add penalty</span></b></span></a>-->
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <table width="100%" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Payment Mode</th>
                            <th>Transaction #</th>
                            <th>Transaction Type</th>
                            <th>Amount</th>
                            <th>Bank/Branch</th>
                            <th>Reference Number</th>
                            <th>Comment</th>
                            <th>Date</th>
                            <th>Due Months</th>
                            <th>Status</th>
                            <th>Cr By</th>
                            <th>Up By</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                       <?php /*$agentComArray= ['booking','confirmation','allocation'];$agentComTotal = 0;if($booking->customerPlotTransactions){ foreach($booking->customerPlotTransactions as $cpt):?>
                            <tr>
                                <td style="width: 20%;"><?php echo ucfirst(@$cpt->plotPaymentMode->mode.''.(($cpt->monthlyDate!='')?' ('.$cpt->monthlyDate.')':''))?>
                                    <br/>
                                    <?php if($cpt->plotPaymentMode->mode=='monthly'){?>
                                        <span style="font-size: 10px;"><?php echo $this->getPlotLedgerDetailSingle(@$booking->id,'monthly',false,$cpt->id)?></span>
                                    <?php } ?>
                                    <?php if($cpt->plotPaymentMode->mode=='yearly'){?>
                                        <span style="font-size: 10px;"><?php echo $this->getPlotLedgerDetailSingle(@$booking->id,'yearly',false,$cpt->id)?></span>
                                    <?php } ?>
                                </td>
                                <td><?php echo ($this->startsWith($cpt->transaction_number, '#'))?$cpt->transaction_number:'#'.ltrim($cpt->transaction_number,0)?></td>
                                <td><?php echo $cpt->transaction_type?></td>
                                <td><?php echo 'Rs. '.number_format($cpt->amount)?></td>
                                <?php if(in_array(strtolower($cpt->plotPaymentMode->mode),$agentComArray)){
                                    $agentComTotal = $agentComTotal + $cpt->amount;
                                }?>
                                <td><?php echo ($cpt->bank != '')? $cpt->bank.' - '.$cpt->branch:'-'?></td>
                                <td><?php echo $cpt->reference_number?></td>
                                <td><?php echo $cpt->comment?></td>
                                <td><?php echo date('d M,Y',strtotime($cpt->createdOn))?></td>
                                <td><?php echo ($cpt->status==1)?'<span class="label label-success">Paid</span>':('<span class="label label-danger">Cancelled</span><br/>'.$cpt->reason)?><!-- &nbsp;<a href=""><span class="label label-info">Update</span></a> --></td>
                                <td><?php echo $cpt->createdBy?></td>
                                <td><?php echo $cpt->updatedBy?></td>
                                <td>
                                    <?php if($cpt->status==1){?>
                                    <a target="_blank" href="<?php echo Yii::app()->baseUrl?>/booking/dublicateinvoice/plot/<?php echo $booking->id?>/transaction/<?php echo str_replace('#', '', $cpt->transaction_number)?>"><span class="label label-success">Print</span></a>&nbsp;
                                    <?php } ?>
                                    <?php if(empty($booking->customerPlotCancelled)){?>
                                    <?php if($userModel['user_type']['id'] == 1 && $cpt->status==1){?>
                                    <a class="hide performTask" href="<?php echo Yii::app()->baseUrl?>/api/messages/type/transaction/id/<?php echo str_replace('#', '', $cpt->transaction_number)?>"><span title="<?php echo $this->getTransactionMessage(str_replace('#', '', $cpt->transaction_number))?>" class="label label-primary">Transaction Message</span></a>
                                    <?php }}?>
                                </td>
                            </tr>
                       <?php endforeach;}?>
                       
                       <?php $cpttTotal = 0;foreach($booking->customerPlotExtraTransactionCustomLogic as $cptt):?>
                            <tr>
                                <td style="width: 20%;"><?php echo @$cptt->plot_payment_mode?></td>
                                <td><?php echo ($this->startsWith($cptt->transaction_number, '#'))?$cptt->transaction_number:'#'.ltrim($cptt->transaction_number,0)?></td>
                                <td><?php echo $cptt->transaction_type?></td>
                                <td><?php echo 'Rs. '.number_format($cptt->amount)?></td>
                                <?php $cpttTotal = $cpttTotal + $cptt->amount;?>
                                <td><?php echo ($cptt->bank != '')? $cptt->bank.' - '.$cptt->branch:'-'?></td>
                                <td><?php echo $cptt->reference_number?></td>
                                <td><?php echo $cptt->comment?></td>
                                <td><?php echo date('d M,Y',strtotime($cptt->createdOn))?></td>
                                <td><?php echo ($cptt->status==1)?'<span class="label label-success">Paid</span>':('<span class="label label-danger">Cancelled</span><br/>'.$cpt->reason)?><!-- &nbsp;<a href=""><span class="label label-info">Update</span></a> --></td>
                                <td><?php echo $cptt->createdBy?></td>
                                <td><?php echo $cptt->updatedBy?></td>
                                <td>
                                    <?php if($cptt->status==1){?>
                                    <a target="_blank" href="<?php echo Yii::app()->baseUrl?>/booking/dublicateinvoice/plot/<?php echo $booking->id?>/transaction/<?php echo str_replace('#', '', $cptt->transaction_number)?>"><span class="label label-success">Print</span></a>&nbsp;
                                    <?php } ?>
                                    <?php if(empty($booking->customerPlotCancelled)){?>
                                    <?php if($userModel['user_type']['id'] == 1 && $cptt->status==1){?>
                                    <a class="hide performTask" href="<?php echo Yii::app()->baseUrl?>/api/messages/type/transaction/id/<?php echo str_replace('#', '', $cptt->transaction_number)?>"><span title="<?php echo $this->getTransactionMessage(str_replace('#', '', $cptt->transaction_number))?>" class="label label-primary">Transaction Message</span></a>
                                    <?php }}?>
                                </td>
                            </tr>
                       <?php endforeach;*/?>
                       
                        <?php
                            $allTransactions = [];
                            
                            /* keep source info */
                            if (!empty($booking->customerPlotTransactions)) {
                                foreach ($booking->customerPlotTransactions as $t) {
                                    $t->_source = 'normal';
                                    $allTransactions[] = $t;
                                }
                            }
                            
                            if (!empty($booking->customerPlotExtraTransactionCustomLogic)) {
                                foreach ($booking->customerPlotExtraTransactionCustomLogic as $t) {
                                    $t->_source = 'extra';
                                    $allTransactions[] = $t;
                                }
                            }
                            
                            /* sort globally */
                            usort($allTransactions, function ($a, $b) {
                                return strnatcmp(
                                    ltrim($a->transaction_number, '#0'),
                                    ltrim($b->transaction_number, '#0')
                                );
                            });
                        ?>
                        
                        <?php
                            $agentComArray = ['booking','confirmation','allocation'];
                            $agentComTotal = 0;
                            $cpttTotal = 0;
                            
                            foreach ($allTransactions as $txn):
                            ?>
                            <tr>
                                <td style="width: 10%;">
                                    <?php
                                    if ($txn->_source === 'normal') {
                                        //echo ucfirst(@$txn->plotPaymentMode->mode.''.(($txn->monthlyDate!='')?' ('.$txn->monthlyDate.')':''));
                                        echo ucfirst(@$txn->plotPaymentMode->mode);
                                    } else {
                                        echo ucfirst(@$txn->plot_payment_mode);
                                    }
                                    ?>
                            
                                    <?php /*if ($txn->_source === 'normal' && $txn->plotPaymentMode->mode=='monthly'){ ?>
                                        <br><span style="font-size:10px;">
                                            <?php echo $this->getPlotLedgerDetailSingle(@$booking->id,'monthly',false,$txn->id)?>
                                        </span>
                                    <?php } ?>
                            
                                    <?php if ($txn->_source === 'normal' && $txn->plotPaymentMode->mode=='yearly'){ ?>
                                        <br><span style="font-size:10px;">
                                            <?php echo $this->getPlotLedgerDetailSingle(@$booking->id,'yearly',false,$txn->id)?>
                                        </span>
                                    <?php }*/ ?>
                                </td>
                            
                                <td><?php echo ($this->startsWith($txn->transaction_number, '#')) ? $txn->transaction_number : '#'.ltrim($txn->transaction_number,0) ?></td>
                                <td><?php echo $txn->transaction_type ?></td>
                                <td><?php echo 'Rs. '.number_format($txn->amount) ?></td>
                            
                                <?php
                                if ($txn->_source === 'normal' && in_array(strtolower($txn->plotPaymentMode->mode), $agentComArray)) {
                                    $agentComTotal += $txn->amount;
                                }
                            
                                if ($txn->_source === 'extra') {
                                    $cpttTotal += $txn->amount;
                                }
                                ?>
                            
                                <td><?php echo ($txn->bank!='') ? $txn->bank.' - '.$txn->branch : '-' ?></td>
                                <td><?php echo $txn->reference_number ?></td>
                                <td><?php echo $txn->comment ?></td>
                                <td><?php echo date('d M,Y',strtotime($txn->createdOn)) ?></td>
                                <td>
                                    <?php
                                    $monthlyDate = @$txn->monthlyDate;
                                    
                                    $beforeStar = strpos($monthlyDate, '*') !== false 
                                        ? trim(explode('*', $monthlyDate)[0]) 
                                        : null;
                                    
                                    if ($beforeStar) {
                                        echo htmlspecialchars($beforeStar);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php echo ($txn->status==1)
                                        ? '<span class="label label-success">Paid</span>'
                                        : '<span class="label label-danger">Cancelled</span><br/>'.@$txn->reason; ?>
                                </td>
                                <td><?php echo $txn->createdBy ?></td>
                                <td><?php echo $txn->updatedBy ?></td>
                            
                                <td>
                                    <?php if($txn->status==1){ ?>
                                        <a target="_blank"
                                           href="<?php echo Yii::app()->baseUrl ?>/booking/dublicateinvoice/plot/<?php echo $booking->id ?>/transaction/<?php echo str_replace('#','',$txn->transaction_number) ?>">
                                            <span class="label label-success">Print</span>
                                        </a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>



                       <?php /*?>



                       
                       <tr><td colspan="3"><b>Plot Total</b></td><td colspan="9"><b><?php echo 'Rs. '.$this->plotTotal($booking->plot->id);?>
                       <?php if($booking->plot->discount != 0){?>
                        <tr><td colspan="3"><b>Plot Discount</b> ( <?php echo $booking->plot->discount ?>)</td><td colspan="9"><b><?php echo 'Rs. '.$this->plotDiscount($booking->plot->id);?>
                        <?php }?>
                        
                        <tr><td colspan="3"><b>Extra Charges</b></td><td colspan="9"><b><?php echo 'Rs. '.number_format($this->plotExtra($booking->plot->id,false,true,true))?></b></td></tr>
                       <tr><td colspan="3"><b>Paid Total</b></td><td colspan="9"><b><?php echo 'Rs. '.number_format($booking->customerPlotTransactionSum)?></b></td></tr>
                       <?php $netTotal = $this->plotDiscount($booking->plot->id,false) - intval($booking->customerPlotTransactionSum)?>
                       <tr><td colspan="3"><b>Balance</b></td><td colspan="9"><b><?php echo 'Rs. '.number_format($netTotal)?></b></td></tr>

                       <tr><td colspan="3"><b>------</b></td><td colspan="9"><b>------</b></td></tr>
                       <?php */?>
                       <?php $plotTotalText = 0;?>
                       <tr><td colspan="3"><b>Total</b></td><td colspan="10"><b><?php echo 'Rs. '.number_format($tpp)?></b></td></tr>
                       <?php $plotTotalText = $booking->plot->total;?>
                       <?php if($booking->plot->discount > 0){?>
                       <!--<tr><td colspan="3"><b>Discount</b></td><td colspan="9"><b><?php //echo 'Rs. '.number_format($booking->plot->discount)?></b></td></tr>-->
                       <!--<tr><td colspan="3"><b>Discounted Total Cost of Land</b></td><td colspan="9"><b><?php //echo 'Rs. '.number_format(($booking->plot->total+$tpp)-$booking->plot->discount)?></b></td></tr>-->
                       <?php //$plotTotalText = $booking->plot->total-$booking->plot->discount;?>
                        <?php }?>

                       
                       
                       <?php if($booking->status==3){?>
                        <tr><td colspan="3"><b>Paid Total</b></td><td colspan="10"><b><?php echo 'Rs. '.number_format($booking->customerPlotTransactionSumTransfer+$cpttTotal)?></b></td></tr>
                        <tr><td colspan="3"><b>Balance</b></td><td colspan="10"><b><?php echo 'Rs. '.number_format($plotTotalText-$booking->customerPlotTransactionSumTransfer)?></b></td></tr>
                       <?php } else {?>
                        <tr><td colspan="3"><b>Paid Total</b></td><td colspan="10"><b><?php echo 'Rs. '.number_format($booking->customerPlotTransactionSum+$cpttTotal)?></b></td></tr>
                       <?php }?>
                       
                       <tr><td colspan="3"><b>Remaining</b></td><td colspan="10"><b><?php echo 'Rs. '.number_format($tpp-$booking->customerPlotTransactionSum-$cpttTotal)?></b></td></tr>
                       
                    </tbody>
                </table>

                
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>

    <?php if($booking->customerPlotExtraTransactionCustomLogicNot){?>
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <b style="text-transform: UPPERCASE;font-weight: bold;">View Other Charges</b>
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <table width="100%" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Payment Mode</th>
                            <th>Transaction #</th>
                            <th>Transaction Type</th>
                            <th>Amount</th>
                            <th>Bank/Branch</th>
                            <th>Reference Number</th>
                            <th>Comment</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Cr By</th>
                            <th>Up By</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                       <?php $dTotal = 0;   if($booking->customerPlotExtraTransactionCustomLogicNot){ foreach($booking->customerPlotExtraTransactionCustomLogicNot as $cpt):?>
                            <tr>
                                <td><?php echo ucfirst(@$cpt->plot_payment_mode)?></td>
                                <td><?php echo ($this->startsWith($cpt->transaction_number, '#'))?$cpt->transaction_number:'#'.ltrim($cpt->transaction_number,0)?></td>
                                <td><?php echo $cpt->transaction_type?></td>
                                <td><?php echo 'Rs. '.number_format($cpt->amount)?></td>
                                <?php $dTotal = $dTotal + $cpt->amount;?>
                                <td><?php echo ($cpt->bank != '')? $cpt->bank.' - '.$cpt->branch:'-'?></td>
                                <td><?php echo $cpt->reference_number?></td>
                                <td><?php echo $cpt->comment?></td>
                                <td><?php echo date('d M,Y',strtotime($cpt->createdOn))?></td>
                                <td><?php echo ($cpt->status==1)?'<span class="label label-success">Paid</span>':('<span class="label label-danger">Cancelled</span><br/>'.$cpt->monthlyDate)?><!-- &nbsp;<a href=""><span class="label label-info">Update</span></a> --></td>
                                <td><?php echo $cpt->createdBy?></td>
                                <td><?php echo $cpt->updatedBy?></td>
                                <td>
                                    <?php if($cpt->status==1){?>
                                    <!-- <a target="_blank" href="<?php echo Yii::app()->baseUrl?>/booking/dublicateextrainvoice/type/development/plot/<?php echo $booking->id?>/transaction/<?php echo str_replace('#', '', $cpt->transaction_number)?>"><span class="label label-success">Print</span></a> -->
                                    <a target="_blank" href="<?php echo Yii::app()->baseUrl?>/booking/dublicateinvoice/plot/<?php echo $booking->id?>/transaction/<?php echo str_replace('#', '', ltrim($cpt->transaction_number,0))?>"><span class="label label-success">Print</span></a>&nbsp;
                                    <?php }?>
                                    <?php if(empty($booking->customerPlotCancelled)){?>
                                    <?php if($userModel['user_type']['id'] == 1 && $cpt->status==1){?>
                                    <a class="performTask" href="<?php echo Yii::app()->baseUrl?>/api/messages/type/other/id/<?php echo str_replace('#', '', $cpt->transaction_number)?>"><span title="<?php echo $this->getTransactionMessage(str_replace('#', '', $cpt->transaction_number),'other')?>" class="label label-primary">Transaction Message</span></a>
                                    <?php }}?>
                                </td>

                            </tr>
                       <?php endforeach;}?>
                        
                        <tr><td colspan="3"><b>Paid Total</b></td><td colspan="9"><b><?php echo 'Rs. '.number_format($dTotal)?></b></td></tr>
                    </tbody>
                </table>

                
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <?php }?>


    <div class="col-lg-12 hide">
        <div class="panel panel-default">
            <div class="panel-heading">
                <b style="text-transform: UPPERCASE;font-weight: bold;">View Penalty Charges</b>
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <table width="100%" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Payment Mode</th>
                            <th>Transaction #</th>
                            <th>Transaction Type</th>
                            <th>Amount</th>
                            <th>Bank/Branch</th>
                            <th>Reference Number</th>
                            <th>Comment</th>
                            <th>Date</th>
                            <th>Inst. Month</th>
                            <th>Cr By</th>
                            <th>Up By</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                       <?php $dTotal = 0;   if($booking->customerPlotPlanTransactionsPenalty){ foreach($booking->customerPlotPlanTransactionsPenalty as $cpt):?>
                            <tr>
                                <td><?php echo ucfirst(@$cpt->plot_payment_mode)?></td>
                                <td><?php echo ($this->startsWith($cpt->transaction_number, '#'))?$cpt->transaction_number:'#'.$cpt->transaction_number?></td>
                                <td><?php echo $cpt->transaction_type?></td>
                                <td><?php echo 'Rs. '.number_format($cpt->amount)?></td>
                                <?php $dTotal = $dTotal + $cpt->amount;?>
                                <td><?php echo ($cpt->bank != '')? $cpt->bank.' - '.$cpt->branch:'-'?></td>
                                <td><?php echo $cpt->reference_number?></td>
                                <td><?php echo $cpt->comment?></td>
                                <td><?php echo date('d M,Y',strtotime($cpt->createdOn))?></td>
                                <td><?php echo ($cpt->monthlyDate)?></td>
                                <td><?php echo $cpt->createdBy?></td>
                                <td><?php echo $cpt->updatedBy?></td>
                                <td><a target="_blank" href="<?php echo Yii::app()->baseUrl?>/booking/dublicateextrainvoice/type/penalty/plot/<?php echo $booking->id?>/transaction/<?php echo str_replace('#', '', $cpt->transaction_number)?>"><span class="label label-success">Print</span></a></td>
                            </tr>
                       <?php endforeach;}?>
                    </tbody>
                </table>

                
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->

    </div>
    <!-- /.col-lg-12 -->
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <b style="text-transform: UPPERCASE;font-weight: bold;">Dealer Information</b>
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <?php
                $isSubDealer = ($dealerCommission && !empty($dealerCommission['is_sub_agent']) && $dealerCommission['parent'] && (int)$dealerCommission['agent']->parent_id > 0);
                $agentPayout = ($booking->agent_id && $booking->agent) ? $booking->agent->getCommissionPayoutPlan($booking, $plotTotalAmount, 'agent') : null;
                $parentPayout = ($isSubDealer && $booking->agent) ? $booking->agent->getCommissionPayoutPlan($booking, $plotTotalAmount, 'parent') : null;
                $agentPaid = function ($agentId, $agentName) use ($expenses, $isSubDealer, $booking) {
                    $paid = 0;
                    if (empty($expenses)) {
                        return 0;
                    }
                    foreach ($expenses as $ex) {
                        if ((int)$ex->status !== 1) {
                            continue;
                        }
                        $eid = isset($ex->agent_id) ? (int)$ex->agent_id : 0;
                        if ($eid > 0) {
                            if ($eid === (int)$agentId) {
                                $paid += (float)$ex->amount;
                            }
                            continue;
                        }
                        if (!$isSubDealer) {
                            $paid += (float)$ex->amount;
                        } elseif ($agentName !== '' && strcasecmp(trim((string)$ex->paid_to), trim($agentName)) === 0) {
                            $paid += (float)$ex->amount;
                        }
                    }
                    return $paid;
                };
                $addCommissionBtn = function ($agentId, $agentName, $dueAmount, $role = 'agent') use ($booking, $buttonClass, $agentPaid, $plotTotalAmount) {
                    $paid = $agentPaid($agentId, $agentName);
                    if ((float)$dueAmount > 0 && round($paid, 2) >= round((float)$dueAmount, 2)) {
                        return '';
                    }
                    $next = 0;
                    if ($booking->agent) {
                        $next = $booking->agent->getAddCommissionButtonAmount($booking, $plotTotalAmount, $paid, $role);
                    }
                    if ($next <= 0) {
                        return '';
                    }
                    $commHash = Agents::encodeCommissionAmount($booking->id, $agentId, $next, $role);
                    $url = Yii::app()->baseUrl.'/expenses/add?booking_id='.(int)$booking->id.'&agent_id='.(int)$agentId.'&comm_hash='.urlencode($commHash);
                    return '<a href="'.CHtml::encode($url).'"><button type="button" class="btn btn-success btn-xs '.$buttonClass.'">Add Commision</button></a>';
                };
                $remainingAmount = function ($agentId, $agentName, $dueAmount) use ($agentPaid) {
                    $paid = $agentPaid($agentId, $agentName);
                    return max(0, round((float)$dueAmount - $paid, 2));
                };
                $paidAmount = function ($agentId, $agentName) use ($agentPaid) {
                    return round($agentPaid($agentId, $agentName), 2);
                };
                $formatPayout = function ($plan) {
                    //echo '<pre>';print_r($plan);
                    if (!$plan) {
                        return array('monthly' => '-', 'comm' => '-', 'months' => '-');
                    }
                    $monthly = (float)$plan['monthly_installment'];
                    $comm = (float)$plan['monthly_commission'];
                    $months = (int)$plan['months_total'];
                    $percent = rtrim(rtrim(number_format((float)$plan['payout_percent'], 2, '.', ''), '0'), '.');
                    return array(
                        'monthly' => number_format($monthly),
                        'comm' => number_format($comm).' ('.$percent.'%)',
                        'months' => $months ? $months : '0',
                    );
                };
                $agentPayoutCells = $formatPayout($agentPayout);
                $parentPayoutCells = $formatPayout($parentPayout);
                ?>
                <p class="help-block" style="margin-top:0;">
                    Commission is paid from the payment-schedule monthly installment:
                    agent <b><?php echo (int)Agents::$agentMonthlyPayoutPercent?>%</b><?php if($isSubDealer){ ?>, parent <b><?php echo (int)Agents::$parentMonthlyPayoutPercent?>%</b><?php } ?>.
                    Monthly installment, monthly commission and months come from the payment schedule (no monthly transaction required to display).
                </p>
                <div class="col-lg-12" style="padding-left: 0px;">
                    <table width="100%" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Dealer Name</th>
                                <th>Dealer Booking #</th>
                                <th>Commission Slab</th>
                                <th>%</th>
                                <th>Total Amount</th>
                                <th>Agent Commission (PKR)</th>
                                <th>Monthly Inst.</th>
                                <th>Monthly Comm.</th>
                                <th>Months</th>
                                <th>Paid Commission</th>
                                <th>Remaining Commission</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($isSubDealer){ ?>
                            <tr>
                                <td><?php echo CHtml::encode($dealerCommission['parent']->name)?></td>
                                <td>-</td>
                                <td>-</td>
                                <td><?php echo number_format($dealerCommission['parent_percent'], 2, '.', ',')?>%</td>
                                <td><?php echo number_format($plotTotalAmount, 2, '.', ',')?></td>
                                <td><?php echo number_format($dealerCommission['parent_amount'])?></td>
                                <td><?php echo $parentPayoutCells['monthly']?></td>
                                <td><?php echo $parentPayoutCells['comm']?></td>
                                <td><?php echo $parentPayoutCells['months']?></td>
                                <td><?php echo number_format($paidAmount($dealerCommission['parent']->id, $dealerCommission['parent']->name))?></td>
                                <td><?php echo number_format($remainingAmount($dealerCommission['parent']->id, $dealerCommission['parent']->name, $dealerCommission['parent_amount']))?></td>
                                <td><?php echo $addCommissionBtn($dealerCommission['parent']->id, $dealerCommission['parent']->name, $dealerCommission['parent_amount'], 'parent')?></td>
                            </tr>
                            <tr>
                                <td><?php echo CHtml::encode($dealerCommission['agent']->name)?></td>
                                <td><?php echo (int)$dealerCommission['sequence']?></td>
                                <td><?php echo CHtml::encode($dealerCommission['tier_label'])?></td>
                                <td><?php echo number_format($dealerCommission['agent_percent'], 2, '.', ',')?>%</td>
                                <td><?php echo number_format($plotTotalAmount, 2, '.', ',')?></td>
                                <td><?php echo number_format($dealerCommission['agent_amount'])?></td>
                                <td><?php echo $agentPayoutCells['monthly']?></td>
                                <td><?php echo $agentPayoutCells['comm']?></td>
                                <td><?php echo $agentPayoutCells['months']?></td>
                                <td><?php echo number_format($paidAmount($dealerCommission['agent']->id, $dealerCommission['agent']->name))?></td>
                                <td><?php echo number_format($remainingAmount($dealerCommission['agent']->id, $dealerCommission['agent']->name, $dealerCommission['agent_amount']))?></td>
                                <td><?php echo $addCommissionBtn($dealerCommission['agent']->id, $dealerCommission['agent']->name, $dealerCommission['agent_amount'], 'agent')?></td>
                            </tr>
                        <?php } elseif($dealerCommission){ ?>
                            <tr>
                                <td><?php echo CHtml::encode($dealerCommission['agent']->name)?></td>
                                <td><?php echo (int)$dealerCommission['sequence']?></td>
                                <td><?php echo CHtml::encode($dealerCommission['tier_label'])?></td>
                                <td><?php echo number_format($dealerCommission['agent_percent'], 2, '.', ',')?>%</td>
                                <td><?php echo number_format($plotTotalAmount, 2, '.', ',')?></td>
                                <td><?php echo number_format($dealerCommission['agent_amount'])?></td>
                                <td><?php echo $agentPayoutCells['monthly']?></td>
                                <td><?php echo $agentPayoutCells['comm']?></td>
                                <td><?php echo $agentPayoutCells['months']?></td>
                                <td><?php echo number_format($paidAmount($dealerCommission['agent']->id, $dealerCommission['agent']->name))?></td>
                                <td><?php echo number_format($remainingAmount($dealerCommission['agent']->id, $dealerCommission['agent']->name, $dealerCommission['agent_amount']))?></td>
                                <td><?php echo $addCommissionBtn($dealerCommission['agent']->id, $dealerCommission['agent']->name, $dealerCommission['agent_amount'], 'agent')?></td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td><?php echo @$booking->agent->name?></td>
                                <td>-</td>
                                <td>-</td>
                                <td><?php echo number_format(@$booking->agent_percentage, 2, '.', ',')?>%</td>
                                <td><?php echo number_format(@$agentComTotal, 2, '.', ',')?></td>
                                <td><?php echo $this->Percentage(@$agentComTotal, @$booking->agent_percentage)?></td>
                                <td><?php echo $agentPayoutCells['monthly']?></td>
                                <td><?php echo $agentPayoutCells['comm']?></td>
                                <td><?php echo $agentPayoutCells['months']?></td>
                                <td><?php echo $booking->agent_id ? number_format($paidAmount($booking->agent_id, @$booking->agent->name)) : '0'?></td>
                                <td><?php echo $booking->agent_id ? number_format($remainingAmount($booking->agent_id, @$booking->agent->name, $this->Percentage(@$agentComTotal, @$booking->agent_percentage, 0))) : '0'?></td>
                                <td><?php echo $booking->agent_id ? $addCommissionBtn($booking->agent_id, @$booking->agent->name, $this->Percentage(@$agentComTotal, @$booking->agent_percentage, 0), 'agent') : ''?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php if($booking->agent_id && $expenses){?>
                <div class="col-lg-12" style="padding-left: 0px;">
                    <table width="100%" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Expense Ref No.</th>
                                <th>Dealer</th>
                                <th>Desc.</th>
                                <th>Paid Amount</th>
                                <th>Payment Mode</th>
                                <th>Bank</th>
                                <th>Reference Number</th>
                                <th>Transaction Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>   
                        <tbody>
                            <?php $tct = 0;foreach($expenses as $expense):$btn = '';?>
                            <tr style="background-color: lightgray;">
                                <td><?php echo $this->getExpenseRegNo($expense->id,'expense')?></td>
                                <td><b><?php echo @$expense->agent->name ? CHtml::encode($expense->agent->name) : CHtml::encode($expense->paid_to)?></b></td>
                                <td><b><?php echo 'Rs. '.number_format($expense->amount)?></b></td>
                               <td><b><?php echo $expense->description?></b></td>
                                <td><b><?php echo $expense->payment_mode?></b></td>
                                <td><b><?php echo $expense->bank?></b></td>
                                <td><b><?php echo $expense->number?></b></td>
                                <td><b><?php echo date('d M,Y',strtotime($expense->createdOn))?></b></td>
                                <td>
                                    <?php
                                        if($expense->status==0){
                                            $btn ='<span class="label label-danger">Rejected</span>&nbsp;';
                                        }
                                        if($expense->status==2){
                                            $btn ='<span class="label label-warning">Pending</span>&nbsp;';
                                        }
                                        if($expense->status==1){
                                            $tct = $tct + $expense->amount;
                                            $btn ='<span class="label label-success">Approved</span>&nbsp;';
                                        }
                                        echo $btn;
                                    ?>

                                </td>
                                <td>
                                    <a target="_blank" href="<?php echo Yii::app()->baseUrl?>/expenses/expenseinvoice/<?php echo $expense->id?>">
                                        <span class="label label-success">Print</span>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach;?>
                            <tr>
                                <td colspan="2"><b>Total</b></td>
                                <td colspan="9"><b><?php echo 'PKR '.number_format(@$tct);?></b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php }?>
            </div>
        </div>
        </div>
</div>




<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Reminder Letter</h4>
      </div>
      <form role="form" method="POST" action="<?php echo Yii::app()->baseUrl?>/letter/reminder">
      <div class="modal-body">
        <div class="row">
            <!-- <div class="col-lg-12"> -->
                <div class="form-group col-lg-4" >
                    <label>Amount</label>
                    <input type="number" class="form-control" name="amount" id="name" placeholder="Amount" required="">
                    <input type="hidden" name="booking_id" value="<?php echo $booking->id?>">
                </div>
                <div class="form-group col-lg-4" >
                    <label>Penalty</label>
                    <input type="number" class="form-control" name="penalty" id="Penalty" placeholder="Penalty">
                </div>
                <div class="form-group col-lg-4" >
                    <label>Days</label>
                    <input type="number" class="form-control" name="days" id="days" placeholder="Days" required="">
                </div>
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Reminder</label>
                        <label class="checkbox-inline">
                            <input type="radio" name="reminder" required value="1" checked>First
                        </label>
                        <label class="checkbox-inline">
                            <input type="radio" name="reminder"  required value="2">Second
                        </label>
                        <label class="checkbox-inline">
                            <input type="radio" name="reminder"  required value="3">Third
                        </label>
                        
                    </div>
                </div>
            
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Submit</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
      </form>
    </div>

  </div>
</div>

<!-- Modal -->
<div id="myModalLetter" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Development Letter</h4>
      </div>
      <form role="form" target="_blank" method="POST" action="<?php echo Yii::app()->baseUrl?>/letter/developmentsingle">
      <div class="modal-body">
        <div class="row">
            <!-- <div class="col-lg-12"> -->
                <div class="form-group col-lg-6" >
                    <label>Amount</label>
                    <input type="number" class="form-control" name="amount" id="name" placeholder="Amount" required="">
                    <input type="hidden" name="booking_id" value="<?php echo $booking->id?>">
                </div>
            
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success" >Submit</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
      </form>
    </div>

  </div>
</div>


<!-- Generate Modal -->
<div id="generateCert" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Generate Certificate</h4>
      </div>
      <form role="form" target="_blank" method="POST" action="<?php echo Yii::app()->baseUrl?>/certificate/generate">
      <div class="modal-body">
        <div class="row">
            <!-- <div class="col-lg-12"> -->
                <div class="form-group col-lg-6" >
                    <label>Certificate</label>
                    <select class="form-control" name="certificate">
                            <option value="confirm">Confirmation Letter</option>
                            <option value="allo-let">Allocation Letter</option>
                            <option value="confirm-2">Confirmation Letter 2</option>
                            <option value="confirm-2-back">Confirmation Letter t&c</option>
                            <option value="allo-let-2">Allocation Letter 2</option>
                            <option value="allo-let-2-back">Allocation Letter t&c</option>
                            <option value="allo-cer">Allocation Certificate</option>
                            <option value="poss">Possession Order</option>
                    </select>
                    <input type="hidden" name="booking_id" value="<?php echo $booking->id?>">
                </div>

                <div class="form-group col-lg-12" >
                    <label>Duplicate</label>
                    <input name="duplicate"  value="1" type="checkbox">
                </div>
            
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success" >Submit</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
      </form>
    </div>

  </div>
</div>


<!-- Booking Reason Modal -->
<div id="bookingReason" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Block Booking</h4>
      </div>
      <form role="form" method="POST" action="<?php echo Yii::app()->baseUrl?>/booking/saveBlocked">
      <div class="modal-body">
        <div class="row">
            <!-- <div class="col-lg-12"> -->
            <div class="form-group col-lg-12" >
                <label>Reason</label>
                <textarea class="form-control" rows="3" name="reason" placeholder="Reason" required></textarea>
                <input type="hidden" name="booking_id" value="<?php echo $booking->id?>">
            </div>            
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success" >Submit</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
      </form>
    </div>

  </div>
</div>