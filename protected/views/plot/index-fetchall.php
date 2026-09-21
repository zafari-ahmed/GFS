<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Plots</h1>
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

    return Yii::app()->baseUrl . '/plot' . ($query ? '?' . $query : '');
}
?>
<!-- /.row -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                All Plots<br/>
                <?php /*?>
                <span style="margin-top: -5px;"><a href="<?php echo Yii::app()->baseUrl?>/plot"><span class="label label-info btn-sm">All</span></a></span>
                <?php $activea = (@$_GET['status']=='active')?'success':'info'?>
                <?php $activeb = (@$_GET['status']=='booked')?'success':'info'?>
                    <span style="margin-top: -5px;"><a href="<?php echo Yii::app()->baseUrl?>/plot?status=active"><span class="label label-<?php echo @$activea?> btn-sm">Available</span></a></span>
                    <span style="margin-top: -5px;"><a href="<?php echo Yii::app()->baseUrl?>/plot?status=booked"><span class="label label-<?php echo @$activeb?> btn-sm">Booked</span></a></span><br/>
                <?php foreach($paymentSchedules as $ps): $active = (@$_GET['block']==$ps->block_number)?'success':'info'?>
                    <span style="margin-top: -5px;"><a href="<?php echo Yii::app()->baseUrl?>/plot?block=<?php echo $ps->block_number?>"><span class="label label-<?php echo $active?> btn-sm"><?php echo $ps->block_number?></span></a></span>
                <?php endforeach;?><br/>
                <?php foreach($paymentSchedulesType as $ps): $active = (@$_GET['block']==$ps->plot_type)?'success':'info'?>
                    <span style="margin-top: -5px;"><a href="<?php echo Yii::app()->baseUrl?>/plot?type=<?php echo $ps->plot_type?>"><span class="label label-<?php echo $active?> btn-sm"><?php echo $ps->plot_type?></span></a></span>
                <?php endforeach;?>
                <?php */?>
                <span style="margin-top: -5px;">
                    <a href="<?php echo Yii::app()->baseUrl; ?>/plot">
                        <span class="label label-<?php echo empty($_GET['block']) && empty($_GET['type']) ? 'success' : 'info'; ?> btn-sm">
                            All
                        </span>
                    </a>
                </span>
                <br/>
                <?php $activea = (@$_GET['status']=='active')?'success':'info'?>
                <?php $activeb = (@$_GET['status']=='booked')?'success':'info'?>
                <span style="margin-top: -5px;"><a href="<?php echo Yii::app()->baseUrl?>/plot?status=active"><span class="label label-<?php echo @$activea?> btn-sm">Available</span></a></span>
                    <span style="margin-top: -5px;"><a href="<?php echo Yii::app()->baseUrl?>/plot?status=booked"><span class="label label-<?php echo @$activeb?> btn-sm">Booked</span></a></span><br/>
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
                <span class="pull-right">
                    <a href="<?php echo Yii::app()->baseUrl?>/report/exportavailableplot"><span class="label label-success">Export</span></a>
                    &nbsp;
                    <a href="<?php echo Yii::app()->baseUrl?>/import/uploaddealer"><span class="label label-success">Import</span></a>
                </span>
                
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-plot">
                    <thead>
                        <tr>
                            <!-- <th>Block #</th>
                            <th>Plot Type</th> -->
                            <th>Client Name</th>
                            <th>Plot #</th>
                            <th>Category</th>
                            <th>Size</th>
                            <th>Total</th>
                            <th>Discount</th>
                            <th>Dealer</th>
                            <th>Status</th>
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