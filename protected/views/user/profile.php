<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Edit Profile</h1>
        <?php
            foreach(Yii::app()->user->getFlashes() as $key => $message) {
                echo '<div class="alert alert-'.$key.' alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'.$message.'</div>';
            }
        ?>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Update Profile
            </div>
            <div class="panel-body">
                <div class="row">
                    <form role="form" method="POST" action="<?php echo Yii::app()->baseUrl?>/user/updateprofile" enctype="multipart/form-data">
                        <div class="form-group col-lg-12">
                            <?php $photoUrl = Users::profileImageUrl($user); ?>
                            <?php if($photoUrl){ ?>
                                <img id="profilePreview" src="<?php echo CHtml::encode($photoUrl)?>" alt="Profile" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:1px solid #ddd;">
                            <?php } else { ?>
                                <img id="profilePreview" src="" alt="" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:1px solid #ddd;display:none;">
                                <span id="profilePlaceholder" class="fa fa-user" style="font-size:64px;color:#777;"></span>
                            <?php } ?>
                        </div>
                        <div class="form-group col-lg-6">
                            <label>First Name</label>
                            <input class="form-control" name="first_name" id="first_name" placeholder="First Name" required="" value="<?php echo CHtml::encode($user->first_name)?>">
                        </div>
                        <div class="form-group col-lg-6">
                            <label>Last Name</label>
                            <input class="form-control" name="last_name" id="last_name" placeholder="Last Name" required="" value="<?php echo CHtml::encode($user->last_name)?>">
                        </div>
                        <div class="form-group col-lg-4">
                            <label>Username</label>
                            <input class="form-control" name="username" id="username" placeholder="Username" required="" value="<?php echo CHtml::encode($user->username)?>">
                        </div>
                        <div class="form-group col-lg-4">
                            <label>Email Address</label>
                            <input class="form-control" type="email" name="email_address" id="email_address" placeholder="Email Address" required="" value="<?php echo CHtml::encode($user->email_address)?>">
                        </div>
                        <div class="form-group col-lg-4">
                            <label>Profile Image</label>
                            <input class="form-control" type="file" name="profile_image" id="profile_image" accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
                            <p class="help-block">JPG, PNG, GIF or WEBP. Max 2MB.</p>
                        </div>
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-success">Update Profile</button>
                            <a href="<?php echo Yii::app()->baseUrl?>/user/changepassword" class="btn btn-default">Change Password</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
(function () {
    var input = document.getElementById('profile_image');
    var preview = document.getElementById('profilePreview');
    var placeholder = document.getElementById('profilePlaceholder');
    if (!input || !preview) {
        return;
    }
    input.addEventListener('change', function () {
        if (!this.files || !this.files[0]) {
            return;
        }
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'inline-block';
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        };
        reader.readAsDataURL(this.files[0]);
    });
})();
</script>
