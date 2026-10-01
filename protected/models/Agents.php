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
	 * Share of each monthly installment paid out as agent commission.
	 */
	public static $agentMonthlyPayoutPercent = 45;

	/**
	 * Share of each monthly installment paid out as parent commission.
	 */
	public static $parentMonthlyPayoutPercent = 5;

	/**
	 * Add Commission button only:
	 * 0 = monthly payout (hide after that month's commission is paid)
	 * 1 = always show while remaining commission exists (not tied to month-wise earned)
	 */
	public static $customPayout = 1;

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

	public static function getAgentMonthlyPayoutPercent()
	{
		return (float)self::$agentMonthlyPayoutPercent;
	}

	public static function getParentMonthlyPayoutPercent()
	{
		return (float)self::$parentMonthlyPayoutPercent;
	}

	public static function isCustomPayout()
	{
		return (int)self::$customPayout === 1;
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

	public function getMonthlyInstallmentForBooking($booking)
	{
		if (!$booking) {
			return 0;
		}
		if (method_exists($booking, 'getScheduleMonthlyInstallment')) {
			$fromSchedule = (float)$booking->getScheduleMonthlyInstallment();
			if ($fromSchedule > 0) {
				return $fromSchedule;
			}
		}
		if (!$booking->plot) {
			return 0;
		}
		$months = (int)$booking->monthlyMonths;
		if ($months <= 0) {
			$months = 36;
		}

		$totalMonthly = 0;
		if ($booking->customerpaymentSchedule) {
			foreach ($booking->customerpaymentSchedule as $mode) {
				if (strtolower($mode->mode) === 'monthly' && (float)$mode->amount > 0) {
					$totalMonthly = (float)$mode->amount;
					break;
				}
			}
		}

		if ($totalMonthly <= 0) {
			$scheduleId = 0;
			if (!empty($booking->is_special)) {
				$scheduleId = $booking->is_special;
			} elseif ($booking->paymentSchedule) {
				$scheduleId = $booking->paymentSchedule->id;
			}

			if ($scheduleId) {
				$plotKeys = array();
				if ($booking->plot->block_number !== '' && $booking->plot->block_number !== null) {
					$plotKeys[] = strtolower($booking->plot->block_number);
				}
				if ($booking->plot->plot_type !== '' && $booking->plot->plot_type !== null) {
					$plotKeys[] = strtolower($booking->plot->plot_type);
				}

				$modes = PaymentSchedulePaymentModes::model()->findAll(
					'payment_schedule_id = :id',
					array(':id' => $scheduleId)
				);
				$monthlyModes = array();
				foreach ($modes as $mode) {
					if (strtolower($mode->mode) === 'monthly' && (float)$mode->amount > 0) {
						$monthlyModes[] = $mode;
					}
				}
				foreach ($monthlyModes as $mode) {
					if (in_array(strtolower($mode->plot_type), $plotKeys, true)) {
						$totalMonthly = (float)$mode->amount;
						break;
					}
				}
				if ($totalMonthly <= 0 && count($monthlyModes) === 1) {
					$totalMonthly = (float)$monthlyModes[0]->amount;
				}
			}
		}

		if ($totalMonthly <= 0 || $months <= 0) {
			return 0;
		}
		return $totalMonthly / $months;
	}

	public function getPaidMonthlyInstallmentCount($booking)
	{
		$installment = $this->getMonthlyInstallmentForBooking($booking);
		if ($installment <= 0 || !$booking || empty($booking->id)) {
			return 0;
		}
		$sum = Yii::app()->db->createCommand()
			->select('SUM(t.amount)')
			->from('customer_plot_transactions t')
			->join('payment_schedule_payment_modes p', 'p.id = t.plot_payment_mode_id')
			->where('t.status = 1 AND t.plot_id = :id AND LOWER(p.mode) = :mode', array(
				':id' => $booking->id,
				':mode' => 'monthly',
			))
			->queryScalar();
		return (int)floor(((float)$sum) / $installment);
	}

	public function getCommissionPayoutPlan($booking, $totalAmount, $role = 'agent')
	{
		$breakdown = $this->getBookingCommissionBreakdown($booking, $totalAmount);
		$installment = $this->getMonthlyInstallmentForBooking($booking);
		if ($role === 'parent') {
			$totalCommission = (float)$breakdown['parent_amount'];
			$payoutPercent = self::getParentMonthlyPayoutPercent();
		} else {
			$totalCommission = (float)$breakdown['agent_amount'];
			$payoutPercent = self::getAgentMonthlyPayoutPercent();
		}

		$monthlyCommission = ($payoutPercent / 100) * $installment;
		$monthsExact = ($monthlyCommission > 0) ? ($totalCommission / $monthlyCommission) : 0;
		$monthsTotal = ($monthsExact > 0) ? (int)ceil($monthsExact - 0.0000001) : 0;
		$paidMonthlyCount = $this->getPaidMonthlyInstallmentCount($booking);
		$earnedMonths = ($monthsTotal > 0) ? min($paidMonthlyCount, $monthsTotal) : 0;

		if ($monthsTotal > 0 && $paidMonthlyCount >= $monthsTotal) {
			$earnedAmount = $totalCommission;
		} else {
			$earnedAmount = $earnedMonths * $monthlyCommission;
			if ($earnedAmount > $totalCommission) {
				$earnedAmount = $totalCommission;
			}
		}

		return array(
			'role' => $role,
			'monthly_installment' => $installment,
			'payout_percent' => $payoutPercent,
			'monthly_commission' => $monthlyCommission,
			'months_exact' => $monthsExact,
			'months_total' => $monthsTotal,
			'paid_monthly_count' => $paidMonthlyCount,
			'earned_months' => $earnedMonths,
			'total_commission' => $totalCommission,
			'earned_amount' => $earnedAmount,
		);
	}

	public function getNextCommissionPaymentAmount($booking, $totalAmount, $alreadyPaid, $role = 'agent')
	{
		$plan = $this->getCommissionPayoutPlan($booking, $totalAmount, $role);
		$alreadyPaid = (float)$alreadyPaid;
		$remainingTotal = max(0, $plan['total_commission'] - $alreadyPaid);
		$earnedRemaining = max(0, $plan['earned_amount'] - $alreadyPaid);
		if ($remainingTotal <= 0 || $earnedRemaining <= 0 || $plan['monthly_commission'] <= 0) {
			return 0;
		}
		$next = min($plan['monthly_commission'], $earnedRemaining, $remainingTotal);
		return round($next, 2);
	}

	public function getAddCommissionButtonAmount($booking, $totalAmount, $alreadyPaid, $role = 'agent')
	{
		$alreadyPaid = (float)$alreadyPaid;
		if (!self::isCustomPayout()) {
			return $this->getNextCommissionPaymentAmount($booking, $totalAmount, $alreadyPaid, $role);
		}
		$plan = $this->getCommissionPayoutPlan($booking, $totalAmount, $role);
		$remainingTotal = max(0, $plan['total_commission'] - $alreadyPaid);
		if ($remainingTotal <= 0) {
			return 0;
		}
		$monthly = (float)$plan['monthly_commission'];
		if ($monthly <= 0) {
			return round($remainingTotal, 2);
		}
		return round(min($monthly, $remainingTotal), 2);
	}

	protected static function commissionHashSecret()
	{
		$sm = Yii::app()->getComponent('securityManager');
		if ($sm && !empty($sm->validationKey)) {
			return $sm->validationKey;
		}
		return 'gfs-commission-amount-'.Yii::app()->getId();
	}

	public static function encodeCommissionAmount($bookingId, $agentId, $amount, $role = 'agent')
	{
		$payload = json_encode(array(
			'b' => (int)$bookingId,
			'a' => (int)$agentId,
			'm' => round((float)$amount, 2),
			'r' => (string)$role,
		));
		$body = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
		$sig = hash_hmac('sha256', $body, self::commissionHashSecret());
		return $body.'.'.$sig;
	}

	public static function decodeCommissionAmount($hash, $bookingId, $agentId)
	{
		$hash = trim((string)$hash);
		if ($hash === '' || strpos($hash, '.') === false) {
			return null;
		}
		$parts = explode('.', $hash, 2);
		if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
			return null;
		}
		list($body, $sig) = $parts;
		$expected = hash_hmac('sha256', $body, self::commissionHashSecret());
		$valid = function_exists('hash_equals') ? hash_equals($expected, $sig) : ($expected === $sig);
		if (!$valid) {
			return null;
		}
		$pad = strlen($body) % 4;
		if ($pad) {
			$body .= str_repeat('=', 4 - $pad);
		}
		$json = base64_decode(strtr($body, '-_', '+/'), true);
		$payload = json_decode($json, true);
		if (!is_array($payload) || !isset($payload['b'], $payload['a'], $payload['m'])) {
			return null;
		}
		if ((int)$payload['b'] !== (int)$bookingId || (int)$payload['a'] !== (int)$agentId) {
			return null;
		}
		return round((float)$payload['m'], 2);
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
