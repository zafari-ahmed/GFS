<?php

/**
 * @property integer $id
 * @property integer $agent_id
 * @property integer $min_bookings
 * @property integer $max_bookings
 * @property string $percentage
 * @property integer $sort_order
 * @property string $createdOn
 */
class AgentCommissionTiers extends CActiveRecord
{
	public function tableName()
	{
		return 'agent_commission_tiers';
	}

	public function rules()
	{
		return array(
			array('agent_id, min_bookings', 'required'),
			array('agent_id, min_bookings, max_bookings, sort_order', 'numerical', 'integerOnly'=>true),
			array('percentage', 'numerical'),
			array('max_bookings, createdOn', 'safe'),
			array('id, agent_id, min_bookings, max_bookings, percentage, sort_order, createdOn', 'safe', 'on'=>'search'),
		);
	}

	public function relations()
	{
		return array(
			'agent' => array(self::BELONGS_TO, 'Agents', 'agent_id'),
		);
	}

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'agent_id' => 'Agent',
			'min_bookings' => 'From Bookings',
			'max_bookings' => 'To Bookings',
			'percentage' => 'Commission %',
			'sort_order' => 'Sort Order',
			'createdOn' => 'Created On',
		);
	}

	public static function ensureTable()
	{
		Yii::app()->db->createCommand("
			CREATE TABLE IF NOT EXISTS `agent_commission_tiers` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`agent_id` int(11) NOT NULL,
				`min_bookings` int(11) NOT NULL DEFAULT 1,
				`max_bookings` int(11) DEFAULT NULL,
				`percentage` decimal(10,2) NOT NULL DEFAULT 0.00,
				`sort_order` int(11) NOT NULL DEFAULT 0,
				`createdOn` datetime DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `agent_id` (`agent_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8
		")->execute();
	}

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
