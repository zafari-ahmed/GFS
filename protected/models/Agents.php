<?php

/**
 * This is the model class for table "agents".
 *
 * The followings are the available columns in table 'agents':
 * @property integer $id
 * @property string $name
 * @property integer $parent_id
 * @property integer $percentage
 * @property double $percentage_value
 * @property string $number
 * @property integer $status
 * @property integer $phase_id
 */
class Agents extends CActiveRecord
{
	/**
	 * Extra commission % for the parent dealer when a sub-agent books.
	 * Change this value to update parent share on view booking and new bookings.
	 */
	public static $parentCommissionPercent = 5;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'agents';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, percentage, percentage_value, number, status', 'required'),
			array('parent_id, percentage, status, phase_id', 'numerical', 'integerOnly'=>true),
			array('percentage_value', 'numerical'),
			array('name, number', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, parent_id, percentage, percentage_value, number, status, phase_id', 'safe', 'on'=>'search'),
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
			'agentPlots' => array(self::STAT, 'CustomerPlots', 'agent_id','condition' => 'status = 1'),
			'agentPlotsActive' => array(self::HAS_MANY, 'CustomerPlots', 'agent_id','condition' => 'status = 1'),
			'agentPlotsInAct' => array(self::STAT, 'CustomerPlots', 'agent_id','condition' => 'status != 1'),
			//'agentPlotsActiveSum' => array(self::STAT, 'CustomerPlots', 'agent_id','condition' => 'status = 1'),
			//'agentPlots' => array(self::STAT, 'CustomerPlots', 'agent_id'),
			'agentReserves' => array(self::HAS_MANY, 'AgentPlots', 'agent_id'),
			'agentReservesCount' => array(self::STAT, 'AgentPlots', 'agent_id'),
			'agentParent' => array(self::BELONGS_TO, 'Agents', 'parent_id'),
			'commissionTiers' => array(self::HAS_MANY, 'AgentCommissionTiers', 'agent_id', 'order' => 'commissionTiers.min_bookings ASC, commissionTiers.sort_order ASC'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
			'parent_id' => 'Parent',
			'percentage' => 'Percentage',
			'percentage_value' => 'Percentage Value',
			'number' => 'Number',
			'status' => 'Status',
			'phase_id' => 'Phase',
		);
	}
	
	
	public static function getParentCommissionPercent()
	{
		return (float)self::$parentCommissionPercent;
	}

	public function getCommissionPercentByBookingCount($count)
	{
		$tier = $this->getCommissionTierByBookingCount($count);
		if ($tier) {
			return (float)$tier->percentage;
		}
		if ($this->percentage_value !== null && $this->percentage_value !== '') {
			return (float)$this->percentage_value;
		}
		return (float)$this->percentage;
	}

	public function getCommissionTierByBookingCount($count)
	{
		$count = (int)$count;
		$tiers = $this->commissionTiers ? $this->commissionTiers : array();
		foreach ($tiers as $tier) {
			$min = (int)$tier->min_bookings;
			$max = ($tier->max_bookings === null || $tier->max_bookings === '') ? null : (int)$tier->max_bookings;
			if ($count >= $min && ($max === null || $count <= $max)) {
				return $tier;
			}
		}
		return null;
	}

	public function getBookingSequenceNumber($booking = null)
	{
		$criteria = new CDbCriteria();
		$criteria->addCondition('t.agent_id = :aid');
		$criteria->addCondition('t.status = 1');
		$criteria->params[':aid'] = $this->id;

		$belongsToThisAgent = ($booking && !empty($booking->id) && (int)$booking->agent_id === (int)$this->id);
		if ($belongsToThisAgent) {
			$createdOn = $booking->createdOn ? $booking->createdOn : date('Y-m-d H:i:s');
			$criteria->addCondition('(t.createdOn < :createdOn) OR (t.createdOn = :createdOn AND t.id <= :id)');
			$criteria->params[':createdOn'] = $createdOn;
			$criteria->params[':id'] = $booking->id;
			$count = (int)CustomerPlots::model()->count($criteria);
			return max($count, 1);
		}

		$count = (int)CustomerPlots::model()->count($criteria);
		return $count + 1;
	}

	public function getSlabPercentForBooking($booking = null)
	{
		$sequence = $this->getBookingSequenceNumber($booking);
		return $this->getCommissionPercentByBookingCount($sequence);
	}

	public function getBookingCommissionBreakdown($booking, $totalAmount)
	{
		$totalAmount = (float)$totalAmount;
		$sequence = $this->getBookingSequenceNumber($booking);
		$tier = $this->getCommissionTierByBookingCount($sequence);
		$percent = $this->getCommissionPercentByBookingCount($sequence);
		$agentAmount = ($percent / 100) * $totalAmount;

		$result = array(
			'sequence' => $sequence,
			'tier' => $tier,
			'tier_label' => $this->formatCommissionTierLabel($tier, $percent),
			'agent' => $this,
			'agent_percent' => $percent,
			'agent_amount' => $agentAmount,
			'parent' => null,
			'parent_percent' => 0,
			'parent_amount' => 0,
			'total_percent' => $percent,
			'total_commission' => $agentAmount,
			'is_sub_agent' => false,
		);

		if ((int)$this->parent_id > 0 && $this->agentParent) {
			$parentPercent = self::getParentCommissionPercent();
			$parentAmount = ($parentPercent / 100) * $totalAmount;
			$result['is_sub_agent'] = true;
			$result['parent'] = $this->agentParent;
			$result['parent_percent'] = $parentPercent;
			$result['parent_amount'] = $parentAmount;
			$result['total_percent'] = $percent + $parentPercent;
			$result['total_commission'] = $agentAmount + $parentAmount;
		}

		return $result;
	}

	public function formatCommissionTierLabel($tier, $percent)
	{
		if ($tier) {
			$max = ($tier->max_bookings === null || $tier->max_bookings === '') ? 'onwards' : $tier->max_bookings;
			return $tier->min_bookings.' - '.$max.' @ '.$percent.'%';
		}
		return $percent.'%';
	}

	public function getAgentPlotsActiveSum($startDate = null, $endDate = null)
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('agent_id = :agent_id');
        $criteria->addCondition('status = :status');
        $criteria->params = array(
            ':agent_id' => $this->id,
            ':status' => 1,
        );
        
        // if ($startDate) {
        //     $criteria->addCondition('DATE(createdOn) >= :start_date');
        //     $criteria->params[':start_date'] = $startDate;
        // }
        
        if ($endDate) {
            $criteria->addCondition('DATE(createdOn) <= :end_date');
            $criteria->params[':end_date'] = $endDate;
        }
        
        return CustomerPlots::model()->count($criteria);
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('parent_id',$this->parent_id);
		$criteria->compare('percentage',$this->percentage);
		$criteria->compare('percentage_value',$this->percentage_value);
		$criteria->compare('number',$this->number,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('phase_id',$this->phase_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Agents the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
