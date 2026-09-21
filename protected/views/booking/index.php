<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Bookings</h1>
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

/**
 * Generate booking filter URL while preserving other filters
 */
function bookingFilterUrl($key, $value = null)
{
    $params = $_GET;

    // Remove Yii route/controller parameter if present
    unset($params['r']);

    if ($value === null || $value === '') {
        unset($params[$key]);
    } else {
        $params[$key] = $value;
    }

    $query = http_build_query($params);

    return Yii::app()->baseUrl . '/booking' . ($query ? '?' . $query : '');
}
?>
<!-- /.row -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                All Bookings<br/>
                <?php /*?>
                <span style="margin-top: -5px;"><a href="<?php echo Yii::app()->baseUrl?>/booking"><span class="label label-info btn-sm">All</span></a></span>
                <?php foreach($paymentSchedules as $ps): $active = (@$_GET['block']==$ps->block_number)?'success':'info'?>
                    <span style="margin-top: -5px;"><a href="<?php echo Yii::app()->baseUrl?>/booking?block=<?php echo $ps->block_number?>"><span class="label label-<?php echo $active?> btn-sm"><?php echo $ps->block_number?></span></a></span>
                <?php endforeach;?>
                <br/>
                <?php foreach($paymentSchedulesType as $ps): $active = (@$_GET['type']==$ps->plot_type)?'success':'info'?>
                    <span style="margin-top: -5px;"><a href="<?php echo Yii::app()->baseUrl?>/booking?block=<?php echo $ps->plot_type?>"><span class="label label-<?php echo $active?> btn-sm"><?php echo $ps->plot_type?></span></a></span>
                <?php endforeach;?>
                <?php */?>
                <!-- ALL -->
                <span style="margin-top: -5px;">
                    <a href="<?php echo Yii::app()->baseUrl; ?>/booking">
                        <span class="label label-<?php echo empty($_GET['block']) && empty($_GET['type']) ? 'success' : 'info'; ?> btn-sm">
                            All
                        </span>
                    </a>
                </span>


                <!-- BLOCK FILTER -->
                <?php foreach($paymentSchedules as $ps): ?>

                    <?php
                        $active = (
                            isset($_GET['block']) &&
                            $_GET['block'] == $ps->block_number
                        ) ? 'success' : 'info';

                        $url = bookingFilterUrl('block', $ps->block_number);
                    ?>

                    <span style="margin-top: -5px;">
                        <a href="<?php echo $url; ?>">
                            <span class="label label-<?php echo $active; ?> btn-sm">
                                <?php echo $ps->block_number; ?>
                            </span>
                        </a>
                    </span>

                <?php endforeach; ?>


                <br/>


                <!-- TYPE FILTER -->
                <?php foreach($paymentSchedulesType as $ps): ?>

                    <?php
                        $active = (
                            isset($_GET['type']) &&
                            $_GET['type'] == $ps->plot_type
                        ) ? 'success' : 'info';

                        $url = bookingFilterUrl('type', $ps->plot_type);
                    ?>

                    <span style="margin-top: -5px;">
                        <a href="<?php echo $url; ?>">
                            <span class="label label-<?php echo $active; ?> btn-sm">
                                <?php echo $ps->plot_type; ?>
                            </span>
                        </a>
                    </span>

                <?php endforeach; ?>
                
                <?php if($userModel['user_type']['id'] == 1 || $userModel['user_type']['id'] == 5){?>
                <span class="pull-right" style="margin-top: -25px;"><a href="<?php echo Yii::app()->baseUrl?>/booking/reportall" target="_blank"><button type="button" class="btn btn-success btn-sm">Report</button></a></span>
                <?php }?>
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <table width="100%" class="table table-striped table-bordered table-hover" id="dataTablesServer">
                    <thead>
                        <tr>
                            <th>Plot #</th>
                            <th>Reg. No.</th>
                            <th>Name</th>
                            <th>CNIC</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Action</th>
                            <th>Payments</th>
                            <th>Discount</th>
                            <th>Dealer</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-12 -->
</div>