<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Payment Schedules</h1>
    </div>
    <!-- /.col-lg-12 -->
</div>
<!-- /.row -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Update Payment Schedule
            </div>
            <div class="panel-body">
                <div class="row">
                    <form role="form" method="POST" action="<?php echo Yii::app()->baseUrl?>/paymentschedule/update">

                        <div class="form-group col-lg-3" >
                            <label>Name</label>
                            <input class="form-control" name="name" id="name" placeholder="Name" required="" value="<?php echo $PaymentSchedule->name?>" >
                            <input type="hidden" name="id" value="<?php echo $PaymentSchedule->id?>" >
                        </div>                    
                        
                        <div class="form-group col-lg-12" >
                            <table width="100%" class="table table-striped table-bordered table-hover">
                                <tr>
                                    <th>Description</th>
                                    <?php foreach($types as $type):?>
                                        <th><?php echo ucwords($type->block_number)?></th>
                                    <?php endforeach;?>
                                </tr>
                                <tbody>
                                    <?php
                                    $modeDetailData = array();
                                    foreach($PaymentSchedule->paymentSchedulePaymentModes as $md){
                                        $modeDetailData[strtolower($md->mode)][strtolower($md->plot_type)] = array(
                                            'amount' => $md->amount,
                                            'id' => $md->id,
                                        );
                                    }
                                    foreach($this->paymentScheduleModes() as $modes):
                                        $modeKey = strtolower($modes);
                                    ?>
                                        <tr>
                                            <td><?php echo ucfirst($modes)?></td>
                                            <?php foreach($types as $type):
                                                $blockKey = strtolower($type->block_number);
                                                $existing = isset($modeDetailData[$modeKey][$blockKey]) ? $modeDetailData[$modeKey][$blockKey] : array('amount'=>'','id'=>'');
                                            ?>
                                                <td>
                                                    <input class="form-control col-md-3" name="payment[<?php echo $modeKey?>][<?php echo $blockKey?>][amount]" value="<?php echo $existing['amount']?>" required>
                                                    <input type="hidden" name="payment[<?php echo $modeKey?>][<?php echo $blockKey?>][id]" value="<?php echo $existing['id']?>">
                                                </td>
                                            <?php endforeach;?>
                                        </tr>
                                    <?php endforeach;?>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                    </form>
                    </div>
                    <!-- /.col-lg-6 (nested) -->
                </div>
                <!-- /.row (nested) -->
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-12 -->
</div>
