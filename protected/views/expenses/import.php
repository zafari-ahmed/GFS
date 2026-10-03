<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Upload Expense</h1>
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
                Upload Expense
                <span class="pull-right">
                    <a href="<?php echo Yii::app()->baseUrl?>/expenses/sample">
                        <span class="label label-info">Download Sample CSV</span>
                    </a>
                    &nbsp;
                    <a href="<?php echo Yii::app()->baseUrl?>/expenses">
                        <span class="label label-default">Back to Expenses</span>
                    </a>
                </span>
            </div>
            <div class="panel-body">
                <div class="row">
                    <form role="form" method="POST" action="<?php echo Yii::app()->baseUrl?>/expenses/expensedata" enctype="multipart/form-data">
                        <div class="form-group col-lg-6">
                            <label>CSV File</label>
                            <input type="file" class="form-control" name="expense" id="expense" accept=".csv" required>
                            <p class="help-block">Use the sample CSV. HEAD must match an expense head exactly.</p>
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
                <p>Keep these headers: <strong>DATE, DESCRIPTION, REFRENCE, DEBIT, HEAD</strong></p>
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
                                <td>DATE</td>
                                <td>Yes</td>
                                <td>03-Oct-26</td>
                                <td>Also accepts 03-10-2026 or 2026-10-03</td>
                            </tr>
                            <tr>
                                <td>DESCRIPTION</td>
                                <td>Yes</td>
                                <td>Fuel for site visit</td>
                                <td>Expense particulars</td>
                            </tr>
                            <tr>
                                <td>REFRENCE</td>
                                <td>No</td>
                                <td>PET-001</td>
                                <td>Cheque / voucher / reference number</td>
                            </tr>
                            <tr>
                                <td>DEBIT</td>
                                <td>Yes</td>
                                <td>8500</td>
                                <td>Amount without currency symbol</td>
                            </tr>
                            <tr>
                                <td>HEAD</td>
                                <td>Yes</td>
                                <td>Feul Expense</td>
                                <td>Must match one of the heads below</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p><strong>Valid expense heads</strong></p>
                <ul>
                    <?php foreach ($expenseTypes as $label): ?>
                        <li><?php echo CHtml::encode($label); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
