<?php

class UserController extends Controller
{
	public function actionAdd()
	{
		$data['types'] = UserTypes::model()->findAll('id > 2');
		$this->render('add',$data);
	}

	public function actionEdit($id)
	{
		$data['user'] = Users::model()->findByPk($id);
		$data['types'] = UserTypes::model()->findAll('id > 2');
		$this->render('edit',$data);
	}

	public function actionIndex()
	{
		$data['users'] = Users::model()->findAll('user_type_id != 1');
		$this->render('index',$data);
	}



	public function actionChangepassword()
	{
		$userModel = Yii::app()->session->get('userModel');
		$data['users'] = Users::model()->findByPk($userModel['id']);
		$this->render('changepassword',$data);
	}

	public function actionProfile()
	{
		$this->checkSession();
		Users::ensureProfileImageColumn();
		$userModel = Yii::app()->session->get('userModel');
		$data['user'] = Users::model()->findByPk($userModel['id']);
		$this->render('profile', $data);
	}

	public function actionUpdateprofile()
	{
		$this->checkSession();
		Users::ensureProfileImageColumn();
		$userModel = Yii::app()->session->get('userModel');
		$user = Users::model()->findByPk($userModel['id']);
		if (!$user) {
			Yii::app()->user->setFlash('danger', 'User not found.');
			$this->redirect(Yii::app()->baseUrl.'/user/profile');
			return;
		}

		if (!empty($_POST['first_name'])) {
			$user->first_name = $_POST['first_name'];
			$user->last_name = isset($_POST['last_name']) ? $_POST['last_name'] : $user->last_name;
			$user->email_address = isset($_POST['email_address']) ? $_POST['email_address'] : $user->email_address;
			$user->username = isset($_POST['username']) ? $_POST['username'] : $user->username;
		}

		if (!empty($_FILES['profile_image']['name']) && (int)$_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
			$ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
			$allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
			if (!in_array($ext, $allowed, true)) {
				Yii::app()->user->setFlash('danger', 'Profile image must be JPG, PNG, GIF or WEBP.');
				$this->redirect(Yii::app()->baseUrl.'/user/profile');
				return;
			}
			if ((int)$_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
				Yii::app()->user->setFlash('danger', 'Profile image must be 2MB or smaller.');
				$this->redirect(Yii::app()->baseUrl.'/user/profile');
				return;
			}
			$folder = Yii::getPathOfAlias('webroot').'/uploads/users/';
			if (!is_dir($folder)) {
				mkdir($folder, 0777, true);
			}
			$fileName = 'user_'.$user->id.'_'.time().'.'.$ext;
			if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $folder.$fileName)) {
				if (!empty($user->profile_image) && is_file($folder.$user->profile_image)) {
					@unlink($folder.$user->profile_image);
				}
				$user->profile_image = $fileName;
			}
		}

		$user->save(false);

		$userModel['first_name'] = $user->first_name;
		$userModel['last_name'] = $user->last_name;
		$userModel['email_address'] = $user->email_address;
		$userModel['username'] = $user->username;
		$userModel['profile_image'] = $user->profile_image;
		Yii::app()->session->add('userModel', $userModel);

		Yii::app()->user->setFlash('success', 'Profile updated successfully.');
		$this->redirect(Yii::app()->baseUrl.'/user/profile');
	}


	public function actionSavepassword()
	{
		$userModel = Yii::app()->session->get('userModel');
		$user = Users::model()->findByPk($userModel['id']);
		if($_POST['username'] == $user->username){
			$user->password = md5($_POST['password']);
			$user->save(false);
			Yii::app()->user->setFlash('success','User update successfully.');
            $this->redirect(Yii::app()->baseUrl.'/user/changepassword');
		} else{
			Yii::app()->user->setFlash('danger','Username not matched.');
            $this->redirect(Yii::app()->baseUrl.'/user/changepassword');
		}
	}

	public function actionUpdate()
	{
		if($_POST['first_name']){
			$Users = Users::model()->findByPk($_POST['id']);
			$Users->attributes = $_POST;
			$Users->status = 1;
			$Users->save(false);
			Yii::app()->user->setFlash('success','User update successfully.');
            $this->redirect(Yii::app()->baseUrl.'/user');
		}
	}

	public function actionSave(){
		if($_POST['first_name']){
			$Users = new Users;
			$Users->attributes = $_POST;
			$Users->password = md5($_POST['password']);
			$Users->status = 1;
			$Users->save(false);
			Yii::app()->user->setFlash('success','User add successfully.');
            $this->redirect(Yii::app()->baseUrl.'/user');
		}
	}

	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
}