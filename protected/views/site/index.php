<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <img src="<?php echo Yii::app()->baseUrl?>/images/logo.png" style="width: 100%;left: -30%;position: static;margin-bottom: -10%;margin-top: 20%;">
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">SIGN IN</h3>
                </div>
                <div class="panel-body">
                    <?php
                        foreach(Yii::app()->user->getFlashes() as $key => $message) {
                            echo '<div class="alert alert-'.$key.'">'.$message.'</div>';
                        }
                    ?>
                    <form role="form" method="post" action="#" autocomplete="off" onsubmit="return false;">
                        <fieldset>
                            <div class="form-group">
                                <input class="form-control" placeholder="E-mail" name="email" type="text" id="email" maxlength="100" autocomplete="username" spellcheck="false">
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Password" name="password" type="password" value="" id="password" maxlength="128" autocomplete="current-password">
                            </div>
                            <!-- <div class="checkbox">
                                <label>
                                    <input name="remember" type="checkbox" value="Remember Me">Remember Me
                                </label>
                            </div> -->
                            <!-- Change this to a button or input when using this as a form -->
                            <a href="javascript:void(0)" class="btn btn-lg btn-success btn-block" id="loginBtn">Login</a>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>