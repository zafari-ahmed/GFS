<?php

/**
 * This is the model class for table "users".
 *
 * The followings are the available columns in table 'users':
 * @property integer $id
 * @property string $email_address
 * @property string $username
 * @property string $first_name
 * @property string $last_name
 * @property string $password
 * @property integer $user_type_id
 * @property integer $status
 * @property string $createdOn
 * @property string $profile_image
 *
 * The followings are the available model relations:
 * @property UserTypes $userType
 */
class Users extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('email_address, username, first_name, last_name, password, user_type_id, status, createdOn', 'required'),
			array('user_type_id, status', 'numerical', 'integerOnly'=>true),
			array('email_address, username, first_name, last_name, password, profile_image', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, email_address, username, first_name, last_name, password, user_type_id, status, createdOn, profile_image', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'userType' => array(self::BELONGS_TO, 'UserTypes', 'user_type_id'),
			'bookings' => array(self::HAS_MANY, 'CustomerPlots', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'email_address' => 'Email Address',
			'username' => 'Username',
			'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'password' => 'Password',
			'user_type_id' => 'User Type',
			'status' => 'Status',
			'createdOn' => 'Created On',
			'profile_image' => 'Profile Image',
		);
	}

	public static function ensureProfileImageColumn()
	{
		$db = Yii::app()->db;
		$exists = $db->createCommand("
			SELECT COUNT(*) FROM information_schema.COLUMNS
			WHERE TABLE_SCHEMA = DATABASE()
			  AND TABLE_NAME = 'users'
			  AND COLUMN_NAME = 'profile_image'
		")->queryScalar();
		if (!$exists) {
			$db->createCommand("ALTER TABLE `users` ADD COLUMN `profile_image` VARCHAR(255) NULL DEFAULT NULL AFTER `createdOn`")->execute();
			Yii::app()->db->schema->getTable('users', true);
			Users::model()->refreshMetaData();
		}
	}

	public static function profileImageUrl($userModel)
	{
		if (empty($userModel)) {
			return '';
		}
		$file = '';
		if (is_array($userModel) && !empty($userModel['profile_image'])) {
			$file = $userModel['profile_image'];
		} elseif (is_object($userModel) && !empty($userModel->profile_image)) {
			$file = $userModel->profile_image;
		}
		if ($file === '') {
			return '';
		}
		$path = Yii::getPathOfAlias('webroot').'/uploads/users/'.$file;
		if (!is_file($path)) {
			return '';
		}
		return Yii::app()->baseUrl.'/uploads/users/'.$file;
	}

	public static function isSuperAdmin($userModel = null)
	{
		if ($userModel === null) {
			$userModel = Yii::app()->session->get('userModel');
		}
		return !empty($userModel['user_type']['id']) && (int)$userModel['user_type']['id'] === 1;
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('email_address',$this->email_address,true);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('first_name',$this->first_name,true);
		$criteria->compare('last_name',$this->last_name,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('user_type_id',$this->user_type_id);
		$criteria->compare('status',$this->status);
		$criteria->compare('createdOn',$this->createdOn,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Users the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
