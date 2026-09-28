<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Import Plots</h1>
        <?php
            foreach(Yii::app()->user->getFlashes() as $key => $message) {
                $alertClass = ($key == 'error') ? 'danger' : (($key == 'success') ? 'success' : 'info');
                echo '<div class="alert alert-'.$alertClass.' alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'.$message.'</div>';
            }
        ?>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Import Plot Data
                <span class="pull-right">
                    <a href="<?php echo Yii::app()->baseUrl?>/import/plotsample">
                        <span class="label label-info">Download Sample CSV</span>
                    </a>
                    &nbsp;
                    <a href="<?php echo Yii::app()->baseUrl?>/plot">
                        <span class="label label-default">Back to Plots</span>
                    </a>
                </span>
            </div>
            <div class="panel-body">
                <div class="row">
                    <form role="form" method="POST" action="<?php echo Yii::app()->baseUrl?>/import/plotdata" enctype="multipart/form-data">
                        <div class="form-group col-lg-6">
                            <label>CSV File</label>
                            <input type="file" class="form-control" name="plot" id="plot" accept=".csv" required>
                            <p class="help-block">Use the sample CSV format. Category and Size must be text values, not IDs.</p>
                        </div>
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">Sample CSV column details</div>
            <div class="panel-body">
                <p>Download the sample file and keep these headers. <strong>Category</strong> and <strong>Size</strong> are saved as IDs after matching the text below.</p>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Column</th>
                                <th>Required</th>
                                <th>Example</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Block #</td>
                                <td>Yes</td>
                                <td>JB</td>
                                <td>Block number</td>
                            </tr>
                            <tr>
                                <td>Plot Type</td>
                                <td>No</td>
                                <td>L / R / B</td>
                                <td>Plot type text</td>
                            </tr>
                            <tr>
                                <td>Plot #</td>
                                <td>Yes</td>
                                <td>01</td>
                                <td>Plot number</td>
                            </tr>
                            <tr>
                                <td>Category</td>
                                <td>Yes</td>
                                <td>Residential / Commercial</td>
                                <td>Text is converted to category ID. Current values:
                                    <?php if(!empty($categories)){ foreach($categories as $category): ?>
                                        <span class="label label-info"><?php echo CHtml::encode($category->name); ?></span>
                                    <?php endforeach; } else { echo 'Residential, Commercial'; } ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Size</td>
                                <td>Yes</td>
                                <td>80 SQ.YD</td>
                                <td>Text is converted to size ID. Current values:
                                    <?php if(!empty($sizes)){ foreach($sizes as $size): ?>
                                        <span class="label label-info"><?php echo CHtml::encode($size->size); ?></span>
                                    <?php endforeach; } else { echo '80 SQ.YD, 120 SQ.YD'; } ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Description</td>
                                <td>No</td>
                                <td></td>
                                <td>Optional plot description</td>
                            </tr>
                            <tr>
                                <td>Length</td>
                                <td>No</td>
                                <td>50</td>
                                <td>Optional</td>
                            </tr>
                            <tr>
                                <td>Width</td>
                                <td>No</td>
                                <td>25</td>
                                <td>Optional</td>
                            </tr>
                            <tr>
                                <td>Road Facing</td>
                                <td>No</td>
                                <td>0 or 1</td>
                                <td>Use 1/0 or Yes/No</td>
                            </tr>
                            <tr>
                                <td>Corner</td>
                                <td>No</td>
                                <td>0 or 1</td>
                                <td>Use 1/0 or Yes/No</td>
                            </tr>
                            <tr>
                                <td>Park Facing</td>
                                <td>No</td>
                                <td>0 or 1</td>
                                <td>Use 1/0 or Yes/No</td>
                            </tr>
                            <tr>
                                <td>West Open</td>
                                <td>No</td>
                                <td>0 or 1</td>
                                <td>Use 1/0 or Yes/No</td>
                            </tr>
                            <tr>
                                <td>Road Facing Amount</td>
                                <td>No</td>
                                <td>10</td>
                                <td>Charge percentage/amount</td>
                            </tr>
                            <tr>
                                <td>Corner Amount</td>
                                <td>No</td>
                                <td>10</td>
                                <td>Charge percentage/amount</td>
                            </tr>
                            <tr>
                                <td>Park Facing Amount</td>
                                <td>No</td>
                                <td>10</td>
                                <td>Charge percentage/amount</td>
                            </tr>
                            <tr>
                                <td>West Open Amount</td>
                                <td>No</td>
                                <td>10</td>
                                <td>Charge percentage/amount</td>
                            </tr>
                            <tr>
                                <td>Total</td>
                                <td>No</td>
                                <td>1800000</td>
                                <td>Plot total amount</td>
                            </tr>
                            <tr>
                                <td>Discount</td>
                                <td>No</td>
                                <td>0</td>
                                <td>Discount value</td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>No</td>
                                <td>Available / Booked</td>
                                <td>Available = 0, Booked = 1. Defaults to Available for new plots.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
