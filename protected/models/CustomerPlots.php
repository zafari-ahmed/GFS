<?php

/**
 * This is the model class for table "customer_plots".
 *
 * The followings are the available columns in table 'customer_plots':
 * @property integer $id
 * @property integer $customer_id
 * @property integer $plot_id
 * @property string $createdOn
 * @property integer $status
 * @property string $updatedBy
 * @property integer $is_agent
 * @property string $agent_name
 * @property string $agent_cnic
 * @property integer $agent_percentage
 * @property integer $agent_id
 * @property integer $is_special
 * @property integer $blocked
 * @property integer $user_id
 * @property integer $monthlyMonths
 * @property integer $monthlyYearlies
 * @property integer $charge_id
 * @property string $total_penalty
 * @property integer $phase_id
 * @property integer $flag_status
 * @property string $reason
 * @property string $monthly_start_date
 * @property string $payment_schedule_json
 * @property string $payment_schedule_software_json
 */
class CustomerPlots extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'customer_plots';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('customer_id, plot_id, createdOn, status, updatedBy, is_agent, agent_name, agent_cnic, agent_percentage, is_special, blocked, charge_id', 'required'),
			array('customer_id, plot_id, status, is_agent, agent_percentage, agent_id, is_special, blocked, user_id, monthlyMonths, monthlyYearlies, charge_id, phase_id, flag_status', 'numerical', 'integerOnly'=>true),
			array('updatedBy, agent_name, agent_cnic, total_penalty, reason', 'length', 'max'=>255),
			array('monthly_start_date', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, customer_id, plot_id, createdOn, status, updatedBy, is_agent, agent_name, agent_cnic, agent_percentage, agent_id, is_special, blocked, user_id, monthlyMonths, monthlyYearlies, charge_id, total_penalty, phase_id, flag_status, reason, monthly_start_date', 'safe', 'on'=>'search'),
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
			'customerPlotTransactionslast' => array(self::HAS_MANY, 'CustomerPlotTransactions', 'plot_id', 'order'=>'id DESC','limit'=>1),
			'customerPlotTransactionsMonthly' => array(self::STAT, 'CustomerPlotTransactions', 'plot_id', 'select'=>'COUNT(*)','condition'=>'plot_payment_mode_id = 34'),
			'customerPlotTransactions' => array(self::HAS_MANY, 'CustomerPlotTransactions', 'plot_id', 'order'=>'createdOn ASC'),
			'customerPlotPlanTransactions' => array(self::HAS_MANY, 'CustomerPlotExtraTransactions', 'plot_id', 'order'=>'createdOn ASC'),
			

			'customerPlotPlanTransactionsDevlopment' => array(self::HAS_MANY, 'CustomerPlotExtraTransactions', 'plot_id','condition'=>'plot_payment_mode= "development" ', 'order'=>'createdOn ASC'),
			'customerPlotPlanTransactionsDevlopmentSum' => array(self::STAT, 'CustomerPlotExtraTransactions', 'plot_id','select'=>'SUM(amount)','condition'=>'plot_payment_mode= "development" ', 'order'=>'createdOn ASC'),

			'customerPlotPlanTransactionsPenalty' => array(self::HAS_MANY, 'CustomerPlotExtraTransactions', 'plot_id','condition'=>'plot_payment_mode = "penalty"', 'order'=>'createdOn ASC'),
			'customerPlotPlanTransactionsPenaltySum' => array(self::STAT, 'CustomerPlotExtraTransactions', 'plot_id','select'=>'SUM(amount)','condition'=>'plot_payment_mode = "penalty"', 'order'=>'createdOn ASC'),

			'customerPlotPlanTransactionsOthers' => array(self::HAS_MANY, 'CustomerPlotExtraTransactions', 'plot_id','condition'=>'plot_payment_mode = "others"', 'order'=>'createdOn ASC'),
			'customerPlotPlanTransactionsOthersSum' => array(self::STAT, 'CustomerPlotExtraTransactions', 'plot_id','select'=>'SUM(amount)','condition'=>'plot_payment_mode = "others"', 'order'=>'createdOn ASC'),

			'customerPlotPlanTransactionsTransfersSum' => array(self::STAT, 'CustomerPlotExtraTransactions', 'plot_id','select'=>'SUM(amount)','condition'=>'plot_payment_mode = "transfer_fee"', 'order'=>'createdOn ASC'),

			'customerPlotPlanTransactionsAll' => array(self::HAS_MANY, 'CustomerPlotExtraTransactions', 'plot_id', 'order'=>'createdOn ASC'),

			'customerPlotPlanTransactionslast' => array(self::HAS_MANY, 'CustomerPlotExtraTransactions', 'plot_id', 'order'=>'id DESC','limit'=>1),
			'customerPlotTransactionsToday' => array(self::HAS_MANY, 'CustomerPlotTransactions', 'plot_id', 'condition'=>"createdOn LIKE '%".(date('Y-m-d'))."%'",'order'=>'createdOn ASC'),
			'customerPlotTransactionsTotal' => array(self::HAS_MANY, 'CustomerPlotTransactions', 'plot_id', 'condition'=>'plot_payment_mode_id = 37'),
			'customerPlotTransactionSum' => array(self::STAT, 'CustomerPlotTransactions', 'plot_id', 'select'=>'SUM(amount)','condition'=>'status = 1'),
			'customerPlotTransactionSumTransfer' => array(self::STAT, 'CustomerPlotTransactions', 'plot_id', 'select'=>'SUM(amount)','condition'=>'status = 0'),
			'customerPlotExtraTransaction' => array(self::HAS_MANY, 'CustomerPlotExtraTransactions', 'plot_id'),
			'customerPlotExtraTransactionSum' => array(self::STAT, 'CustomerPlotExtraTransactions', 'plot_id', 'select'=>'SUM(amount)'),
			'customerPlotTransactionSumWithout3' => array(self::STAT, 'CustomerPlotTransactions', 'plot_id', 'select'=>'SUM(amount)','condition'=>'plot_payment_mode_id != 31 AND plot_payment_mode_id != 32 AND plot_payment_mode_id != 33'),
			'reminderLetters' => array(self::HAS_MANY, 'ReminderLetters', 'booking_id'),
			'reminderLettersCount' => array(self::STAT, 'ReminderLetters', 'booking_id'),
			'customer' => array(self::BELONGS_TO, 'Customers', 'customer_id'),
			'plot' => array(self::BELONGS_TO, 'Plots', 'plot_id'),
			'agent' => array(self::BELONGS_TO, 'Agents', 'agent_id'),
			
			'special' => array(self::BELONGS_TO, 'PaymentSchedules', 'is_special'),
			'paymentSchedule' => array(self::BELONGS_TO, 'PaymentSchedules', 'is_special'),
			
			'customerpaymentSchedule' => array(self::HAS_MANY, 'CustomPaymentSchedulePaymentModes', 'booking_id'),
			'customerSpecialpaymentSchedule' => array(self::HAS_MANY, 'CustomPaymentSchedulePaymentModes', 'booking_id'),
			
			'user' => array(self::BELONGS_TO, 'Users', 'user_id'),
			'charge' => array(self::BELONGS_TO, 'DevelopmentCharges', 'charge_id'),
			'customerPlotCancelled' => array(self::HAS_MANY, 'CustomerPlotCancelled', 'booking_id'),
			'customerPlotDocuments' => array(self::HAS_MANY, 'CustomerPlotDocuments', 'customer_plot_id'),
			'CPDCount' => array(self::STAT, 'CustomerPlotDocuments', 'customer_plot_id'),

			'agent_commision' => array(self::HAS_MANY, 'Expenses', 'booking_id'),
			'agent_commision_sum' => array(self::STAT, 'Expenses', 'booking_id','select'=>'SUM(amount)'),

			'warningLetters' => array(self::HAS_MANY, 'CustomerPlotsWarningLetters', 'booking_id'),
			
			
			
			'customerPlotExtraTransactionCustomLogic' => array(self::HAS_MANY,'CustomerPlotExtraTransactions','plot_id','condition' =>'plot_payment_mode IN ("west_open", "road_facing", "corner", "park_facing","extra_land")', 'order' => 'transaction_number ASC'),
			
			'customerPlotExtraTransactionCustomLogicNot' => array(self::HAS_MANY,'CustomerPlotExtraTransactions','plot_id','condition' =>'plot_payment_mode NOT IN ("west_open", "road_facing", "corner", "park_facing","extra_land")', 'order' => 'transaction_number ASC'),
            
            
            'customerPlotTransactionslastCalling' => array(self::HAS_MANY,'CustomerPlotTransactions','plot_id','order' => 'createdOn DESC','group'  => 'transaction_number'),
            'customerPlotExtraTransactionslastCalling' => array(self::HAS_MANY,'CustomerPlotExtraTransactions','plot_id','order' => 'createdOn DESC','group'  => 'transaction_number'),
            
            'transactionSumsByNumber' => array(self::STAT,'CustomerPlotTransactions','plot_id','select' => 'SUM(amount)','group'  => 'transaction_number'),
            
            'customerPlotTransactionSumBooking' => array(self::STAT, 'CustomerPlotTransactions', 'plot_id', 'select'=>'SUM(amount)','condition'=>'plot_payment_mode_id = 1'),
            'customerPlotTransactionSumAllocation' => array(self::STAT, 'CustomerPlotTransactions', 'plot_id', 'select'=>'SUM(amount)','condition'=>'plot_payment_mode_id = 2'),
            'customerPlotTransactionSumConfirmation' => array(self::STAT, 'CustomerPlotTransactions', 'plot_id', 'select'=>'SUM(amount)','condition'=>'plot_payment_mode_id = 3'),
            'customerPlotTransactionSumMonthly' => array(self::STAT, 'CustomerPlotTransactions', 'plot_id', 'select'=>'SUM(amount)','condition'=>'plot_payment_mode_id = 4'),
            'customerPlotTransactionSumYearly' => array(self::STAT, 'CustomerPlotTransactions', 'plot_id', 'select'=>'SUM(amount)','condition'=>'plot_payment_mode_id = 8'),
            'customerPlotTransactionSumDemarcation' => array(self::STAT, 'CustomerPlotTransactions', 'plot_id', 'select'=>'SUM(amount)','condition'=>'plot_payment_mode_id = 5'),
            'customerPlotTransactionSumPossession' => array(self::STAT, 'CustomerPlotTransactions', 'plot_id', 'select'=>'SUM(amount)','condition'=>'plot_payment_mode_id = 6'),
			
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'customer_id' => 'Customer',
			'plot_id' => 'Plot',
			'createdOn' => 'Created On',
			'status' => 'Status',
			'updatedBy' => 'Updated By',
			'is_agent' => 'Is Agent',
			'agent_name' => 'Agent Name',
			'agent_cnic' => 'Agent Cnic',
			'agent_percentage' => 'Agent Percentage',
			'agent_id' => 'Agent',
			'is_special' => 'Is Special',
			'blocked' => 'Blocked',
			'user_id' => 'User',
			'monthlyMonths' => 'Monthly Months',
			'monthlyYearlies' => 'Monthly Yearlies',
			'charge_id' => 'Charge',
			'total_penalty' => 'Total Penalty',
			'phase_id' => 'Phase',
			'flag_status' => 'Flag Status',
			'reason' => 'Reason',
			'payment_schedule_json' => 'Payment Schedule Json',
			'monthly_start_date' => 'Monthly Start Date',
		);
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
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('plot_id',$this->plot_id);
		$criteria->compare('createdOn',$this->createdOn,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('updatedBy',$this->updatedBy,true);
		$criteria->compare('is_agent',$this->is_agent);
		$criteria->compare('agent_name',$this->agent_name,true);
		$criteria->compare('agent_cnic',$this->agent_cnic,true);
		$criteria->compare('agent_percentage',$this->agent_percentage);
		$criteria->compare('agent_id',$this->agent_id);
		$criteria->compare('is_special',$this->is_special);
		$criteria->compare('blocked',$this->blocked);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('monthlyMonths',$this->monthlyMonths);
		$criteria->compare('monthlyYearlies',$this->monthlyYearlies);
		$criteria->compare('charge_id',$this->charge_id);
		$criteria->compare('total_penalty',$this->total_penalty,true);
		$criteria->compare('phase_id',$this->phase_id);
		$criteria->compare('flag_status',$this->flag_status);
		$criteria->compare('reason',$this->reason,true);
		$criteria->compare('monthly_start_date',$this->monthly_start_date,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerPlots the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	
	/*
	 * Old monthly-from-first-transaction dues calculator.
	 * Replaced by calculateScheduleDues() which uses payment_schedule_json dates.
	 *
	public static function calculateDues($bookingId)
	{
		$result = array(
			'booking_id' => (int)$bookingId,
			'monthly_amount' => 0,
			'elapsed_months' => 0,
			'months_paid' => 0,
			'due_months' => 0,
			'expected_amount' => 0,
			'total_paid' => 0,
			'booking_total' => 0,
			'remaining_balance' => 0,
			'due_amount' => 0,
			'start_date' => null,
			'current_date' => date('Y-m-d'),
			'first_transaction_date' => null,
			'last_transaction_date' => null,
			'status' => 'ok',
			'message' => '',
		);

		$booking = self::model()->with('plot')->findByPk($bookingId);
		if (!$booking) {
			$result['status'] = 'error';
			$result['message'] = 'Booking not found';
			return $result;
		}

		$transactions = CustomerPlotTransactions::model()->findAll(array(
			'condition' => 'plot_id = :plot_id AND status = 1',
			'params' => array(':plot_id' => $booking->id),
			'order' => 'createdOn ASC, id ASC',
		));
		
		$transactionsExtra = CustomerPlotExtraTransactions::model()->findAll(array(
			'condition' => 'plot_id = :plot_id AND status = 1',
			'params' => array(':plot_id' => $booking->id),
			'order' => 'createdOn ASC, id ASC',
		));

		if (empty($transactions)) {
			$result['status'] = 'error';
			$result['message'] = 'No active transactions found';
			return $result;
		}

		$firstTxn = $transactions[0];
		$lastTxn = $transactions[count($transactions) - 1];
		$monthlyAmount = (float)$firstTxn->amount;

		if ($monthlyAmount <= 0) {
			$result['status'] = 'error';
			$result['message'] = 'First transaction amount is invalid';
			return $result;
		}

		$totalPaid = 0;
		foreach ($transactions as $txn) {
			$totalPaid += (float)$txn->amount;
		}
		
		if($transactionsExtra){
		    foreach ($transactionsExtra as $txn) {
    			$totalPaid += (float)$txn->amount;
    		}    
		}

        $startDateRaw = $booking->createdOn;
		$start = new DateTime(date('Y-m-01', strtotime($startDateRaw)));
		$now = new DateTime(date('Y-m-01'));
		$diff = $start->diff($now);
		$elapsedMonths = ($diff->invert) ? 0 : (($diff->y * 12) + $diff->m + 1);

		$expectedAmount = $elapsedMonths * $monthlyAmount;
		$monthsPaid = $totalPaid / $monthlyAmount;

		$bookingTotal = self::resolveBookingTotal($booking);
		$remainingBalance = max(0, $bookingTotal - $totalPaid);

		$rawDue = max(0, $expectedAmount - $totalPaid);
		$dueAmount = min($rawDue, $remainingBalance);
		$dueMonths = ($monthlyAmount > 0) ? round($dueAmount / $monthlyAmount, 2) : 0;

		$result['monthly_amount'] = round($monthlyAmount, 2);
		$result['elapsed_months'] = (int)$elapsedMonths;
		$result['months_paid'] = round($monthsPaid, 2);
		$result['due_months'] = $dueMonths;
		$result['expected_amount'] = round($expectedAmount, 2);
		$result['total_paid'] = round($totalPaid, 2);
		$result['booking_total'] = round($bookingTotal, 2);
		$result['remaining_balance'] = round($remainingBalance, 2);
		$result['due_amount'] = round($dueAmount, 2);
		$result['start_date'] = date('Y-m-d', strtotime($startDateRaw));
		$result['first_transaction_date'] = date('Y-m-d', strtotime($firstTxn->createdOn));
		$result['last_transaction_date'] = date('Y-m-d', strtotime($lastTxn->createdOn));
		$result['message'] = ($dueAmount > 0)
			? $dueMonths . ' month(s) due'
			: 'No dues';

		return $result;
	}
	*/

	/**
	 * Calculate dues from payment_schedule_json dates.
	 *
	 * Each schedule row becomes due on/after its date.
	 * Due amount = scheduled (or expected-to-date for monthly) minus paid for that mode.
	 * Monthly / yearly / half-yearly use elapsed periods from the row date
	 * (monthly falls back to monthly_start_date when the row date is empty).
	 *
	 * @param integer $bookingId customer_plots.id
	 * @return array
	 */
	public static function calculateScheduleDues($bookingId)
	{
		$today = date('Y-m-d');
		$result = array(
			'booking_id' => (int)$bookingId,
			'monthly_amount' => 0,
			'elapsed_months' => 0,
			'months_paid' => 0,
			'due_months' => 0,
			'expected_amount' => 0,
			'total_paid' => 0,
			'booking_total' => 0,
			'remaining_balance' => 0,
			'due_amount' => 0,
			'start_date' => null,
			'current_date' => $today,
			'first_transaction_date' => null,
			'last_transaction_date' => null,
			'items' => array(),
			'due_items' => array(),
			'status' => 'ok',
			'message' => '',
		);

		$booking = self::model()->with('plot')->findByPk($bookingId);
		if (!$booking) {
			$result['status'] = 'error';
			$result['message'] = 'Booking not found';
			return $result;
		}

		$paidByMode = self::loadPaidAmountsByMode($booking->id);
		$totalPaid = 0;
		foreach ($paidByMode as $paidAmount) {
			$totalPaid += (float)$paidAmount;
		}

		$bookingTotal = self::resolveBookingTotal($booking);
		$remainingBalance = max(0, $bookingTotal - $totalPaid);

		$result['total_paid'] = round($totalPaid, 2);
		$result['booking_total'] = round($bookingTotal, 2);
		$result['remaining_balance'] = round($remainingBalance, 2);

		$schedule = array();
		if (!empty($booking->payment_schedule_json)) {
			$decoded = json_decode($booking->payment_schedule_json, true);
			if (is_array($decoded) && !empty($decoded['rows']) && is_array($decoded['rows'])) {
				$schedule = $decoded['rows'];
			}
		}

		if (empty($schedule)) {
			$result['status'] = 'error';
			$result['message'] = 'Payment schedule not found';
			return $result;
		}

		$paidRemaining = $paidByMode;
		$items = array();
		$expectedTotal = 0;
		$monthlyAmount = 0;
		$elapsedMonths = 0;
		$monthsPaid = 0;
		$monthlyDueMonths = 0;
		$monthlyStartDate = null;

		foreach ($schedule as $row) {
			if (!is_array($row)) {
				continue;
			}

			$heading1 = trim((string)(isset($row['heading1']) ? $row['heading1'] : ''));
			$heading2 = trim((string)(isset($row['heading2']) ? $row['heading2'] : ''));
			$valueRaw = trim((string)(isset($row['value']) ? $row['value'] : ''));
			$rowDate = self::normalizeScheduleDate(isset($row['date']) ? $row['date'] : '');

			if ($valueRaw === '' || $valueRaw === '0') {
				continue;
			}

			$modeKey = self::resolveScheduleModeKey($heading1, $heading2);
			if ($modeKey === '') {
				continue;
			}

			$parsed = self::parseScheduleValue($valueRaw);
			$unitAmount = $parsed['amount'];
			$duration = $parsed['duration'];
			$scheduledTotal = $parsed['total'];
			if ($scheduledTotal <= 0) {
				continue;
			}

			$isMonthly = ($modeKey === 'monthly');
			$isYearly = ($modeKey === 'yearly');
			$isHalfYearly = ($modeKey === 'half_yearly');
			$isRecurring = ($isMonthly || $isYearly || $isHalfYearly);

			$dueDate = $rowDate;
			if ($dueDate === '' && $isMonthly && !empty($booking->monthly_start_date)) {
				$dueDate = self::normalizeScheduleDate($booking->monthly_start_date);
			}

			if ($dueDate === '') {
				continue;
			}

			if ($monthlyStartDate === null && $isMonthly) {
				$monthlyStartDate = $dueDate;
			}

			$isDateDue = (strtotime($dueDate) <= strtotime($today));
			$periodMonths = $isMonthly ? 1 : ($isHalfYearly ? 6 : ($isYearly ? 12 : 0));
			$elapsedPeriods = $isDateDue
				? ($isRecurring ? self::countElapsedPeriods($dueDate, $today, $periodMonths, $duration) : 1)
				: 0;

			$expectedAmount = $isRecurring
				? min($elapsedPeriods * $unitAmount, $scheduledTotal)
				: ($isDateDue ? $scheduledTotal : 0);

			$paidPoolKey = self::paidPoolKey($modeKey);
			$availablePaid = isset($paidRemaining[$paidPoolKey]) ? (float)$paidRemaining[$paidPoolKey] : 0;
			$allocatedPaid = min($availablePaid, $scheduledTotal);
			$paidRemaining[$paidPoolKey] = max(0, $availablePaid - $allocatedPaid);

			$itemDue = $isDateDue ? max(0, $expectedAmount - $allocatedPaid) : 0;
			$itemDueMonths = ($isMonthly && $unitAmount > 0) ? round($itemDue / $unitAmount, 2) : 0;

			if ($isMonthly) {
				$monthlyAmount = $unitAmount;
				$elapsedMonths = $elapsedPeriods;
				$monthsPaid = ($unitAmount > 0) ? round($allocatedPaid / $unitAmount, 2) : 0;
				$monthlyDueMonths = $itemDueMonths;
			}

			$expectedTotal += $expectedAmount;

			$label = self::scheduleItemLabel($heading1, $heading2, $modeKey);
			$item = array(
				'label' => $label,
				'heading1' => $heading1,
				'heading2' => $heading2,
				'mode' => $modeKey,
				'due_date' => $dueDate,
				'scheduled_amount' => round($scheduledTotal, 2),
				'unit_amount' => round($unitAmount, 2),
				'duration' => $duration,
				'expected_amount' => round($expectedAmount, 2),
				'paid_amount' => round($allocatedPaid, 2),
				'due_amount' => round($itemDue, 2),
				'is_due' => ($isDateDue && $itemDue > 0),
				'is_recurring' => $isRecurring,
				'is_monthly' => $isMonthly,
				'elapsed_periods' => $elapsedPeriods,
				'due_months' => $itemDueMonths,
			);
			$items[] = $item;
		}

		$remainingCap = $remainingBalance;
		$dueItems = array();
		$totalDue = 0;
		foreach ($items as $index => $item) {
			$cappedDue = min($item['due_amount'], $remainingCap);
			$items[$index]['due_amount'] = round($cappedDue, 2);
			$items[$index]['is_due'] = ($cappedDue > 0);
			if ($item['is_monthly'] && $item['unit_amount'] > 0) {
				$items[$index]['due_months'] = round($cappedDue / $item['unit_amount'], 2);
				$monthlyDueMonths = $items[$index]['due_months'];
			}
			$remainingCap = max(0, $remainingCap - $cappedDue);
			if ($cappedDue > 0) {
				$dueItems[] = $items[$index];
				$totalDue += $cappedDue;
			}
		}

		$result['items'] = $items;
		$result['due_items'] = $dueItems;
		$result['monthly_amount'] = round($monthlyAmount, 2);
		$result['elapsed_months'] = (int)$elapsedMonths;
		$result['months_paid'] = $monthsPaid;
		$result['due_months'] = $monthlyDueMonths;
		$result['expected_amount'] = round($expectedTotal, 2);
		$result['due_amount'] = round($totalDue, 2);
		$result['start_date'] = $monthlyStartDate;
		$result['message'] = ($totalDue > 0) ? 'Dues pending' : 'No dues';

		return $result;
	}

	/**
	 * Paid totals keyed by normalized payment mode.
	 * @param integer $bookingId
	 * @return array
	 */
	protected static function loadPaidAmountsByMode($bookingId)
	{
		$paid = array();

		$rows = Yii::app()->db->createCommand()
			->select('LOWER(p.mode) AS mode, SUM(t.amount) AS paid')
			->from('customer_plot_transactions t')
			->leftJoin('payment_schedule_payment_modes p', 'p.id = t.plot_payment_mode_id')
			->where('t.plot_id = :plot_id AND t.status = 1', array(':plot_id' => $bookingId))
			->group('LOWER(p.mode)')
			->queryAll();

		foreach ($rows as $row) {
			$key = self::normalizePaidModeName($row['mode']);
			if ($key === '') {
				continue;
			}
			if (!isset($paid[$key])) {
				$paid[$key] = 0;
			}
			$paid[$key] += (float)$row['paid'];
		}

		$extraRows = Yii::app()->db->createCommand()
			->select('LOWER(plot_payment_mode) AS mode, SUM(amount) AS paid')
			->from('customer_plot_extra_transactions')
			->where('plot_id = :plot_id AND status = 1', array(':plot_id' => $bookingId))
			->group('plot_payment_mode')
			->queryAll();

		foreach ($extraRows as $row) {
			$key = 'extra:' . str_replace(' ', '_', trim((string)$row['mode']));
			if (!isset($paid[$key])) {
				$paid[$key] = 0;
			}
			$paid[$key] += (float)$row['paid'];
		}

		return $paid;
	}

	/**
	 * @param string $heading1
	 * @param string $heading2
	 * @return string
	 */
	protected static function resolveScheduleModeKey($heading1, $heading2)
	{
		$h1 = strtolower(trim((string)$heading1));
		$h2 = strtolower(trim((string)$heading2));
		$empty = array('', 'empty box');

		$extraKeys = array(
			'corner' => 'extra:corner',
			'road facing' => 'extra:road_facing',
			'west open' => 'extra:west_open',
			'extra land' => 'extra:extra_land',
			'park facing' => 'extra:park_facing',
		);

		if (isset($extraKeys[$h2])) {
			return $extraKeys[$h2];
		}
		if (isset($extraKeys[$h1])) {
			return $extraKeys[$h1];
		}
		if ($h1 === 'extra' && $h2 !== '' && !in_array($h2, $empty, true)) {
			return 'extra:' . str_replace(' ', '_', $h2);
		}

		$source = (!in_array($h1, $empty, true)) ? $h1 : $h2;
		return self::normalizePaidModeName($source);
	}

	/**
	 * @param string $name
	 * @return string
	 */
	protected static function normalizePaidModeName($name)
	{
		$key = strtolower(trim((string)$name));
		$key = str_replace(array('-', '.'), ' ', $key);
		$key = preg_replace('/\s+/', ' ', $key);

		$map = array(
			'booking' => 'booking',
			'allocation' => 'allocation',
			'confirmation' => 'confirmation',
			'monthly' => 'monthly',
			'monthly installment' => 'monthly',
			'm installment' => 'monthly',
			'yearly' => 'yearly',
			'half yearly' => 'half_yearly',
			'demarcation' => 'possession',
			'possession' => 'possession',
			'2nd last payment' => 'possession',
			'last payment' => 'possession',
			'development' => 'extra:development',
			'documentation' => 'extra:documentation',
		);

		return isset($map[$key]) ? $map[$key] : $key;
	}

	/**
	 * Recurring extras share the same paid bucket as their base mode.
	 * @param string $modeKey
	 * @return string
	 */
	protected static function paidPoolKey($modeKey)
	{
		if ($modeKey === 'half_yearly') {
			return 'yearly';
		}
		return $modeKey;
	}

	/**
	 * Parse schedule value: 5000, 5000 x 36, 5000 x 36 = 180000, 5000 = 180000.
	 * @param string $value
	 * @return array
	 */
	protected static function parseScheduleValue($value)
	{
		$value = str_replace(',', '', trim((string)$value));
		$amount = 0;
		$duration = 0;
		$total = 0;

		if (preg_match('/([\d.]+)\s*[\*xX]\s*([\d.]+)(?:\s*=\s*([\d.]+))?/', $value, $matches)) {
			$amount = (float)$matches[1];
			$duration = (float)$matches[2];
			$total = (isset($matches[3]) && $matches[3] !== '') ? (float)$matches[3] : ($amount * $duration);
		} elseif (preg_match('/([\d.]+)\s*=\s*([\d.]+)/', $value, $matches)) {
			$amount = (float)$matches[1];
			$total = (float)$matches[2];
			$duration = ($amount > 0) ? (int)round($total / $amount) : 0;
		} elseif (preg_match('/([\d.]+)/', $value, $matches)) {
			$amount = (float)$matches[1];
			$total = $amount;
		}

		return array(
			'amount' => $amount,
			'duration' => $duration,
			'total' => $total,
		);
	}

	/**
	 * @param string $date
	 * @return string Y-m-d or empty
	 */
	protected static function normalizeScheduleDate($date)
	{
		$date = trim((string)$date);
		if ($date === '' || $date === '0000-00-00') {
			return '';
		}
		$ts = strtotime($date);
		return $ts ? date('Y-m-d', $ts) : '';
	}

	/**
	 * Periods from start date through today, inclusive of the start period.
	 * @param string $startDate
	 * @param string $today
	 * @param integer $periodMonths
	 * @param integer $maxPeriods
	 * @return integer
	 */
	protected static function countElapsedPeriods($startDate, $today, $periodMonths, $maxPeriods = 0)
	{
		if ($periodMonths < 1) {
			return 0;
		}

		$start = new DateTime(date('Y-m-01', strtotime($startDate)));
		$now = new DateTime(date('Y-m-01', strtotime($today)));
		$diff = $start->diff($now);
		if ($diff->invert) {
			return 0;
		}

		$elapsedMonths = ($diff->y * 12) + $diff->m + 1;
		$periods = (int)ceil($elapsedMonths / $periodMonths);
		if ($maxPeriods > 0) {
			$periods = min($periods, (int)$maxPeriods);
		}
		return max(0, $periods);
	}

	/**
	 * @param string $heading1
	 * @param string $heading2
	 * @param string $modeKey
	 * @return string
	 */
	protected static function scheduleItemLabel($heading1, $heading2, $modeKey)
	{
		$h1 = trim((string)$heading1);
		$h2 = trim((string)$heading2);
		$empty = array('', 'Empty Box');

		if (!in_array($h1, $empty, true)) {
			return $h1;
		}
		if (!in_array($h2, $empty, true)) {
			return $h2;
		}

		$label = str_replace(array('extra:', '_'), array('', ' '), $modeKey);
		return ucwords($label);
	}

	/**
	 * Booking total = plot total - discount + side extras (corner/road/park/west).
	 * @param CustomerPlots $booking
	 * @return float
	 */
	protected static function resolveBookingTotal($booking)
	{
		$plot = $booking->plot;
		if (!$plot) {
			return 0;
		}

		$baseTotal = (float)$plot->total - (float)$plot->discount;
		$extraTotal = 0;

		if ((int)$plot->is_corner === 1) {
			$extraTotal += ((float)$plot->is_corner_amount / 100) * (float)$plot->total;
		}
		if ((int)$plot->is_road_facing === 1) {
			$extraTotal += ((float)$plot->is_road_facing_amount / 100) * (float)$plot->total;
		}
		if ((int)$plot->is_park_facing === 1) {
			$extraTotal += ((float)$plot->is_park_facing_amount / 100) * (float)$plot->total;
		}
		if ((int)$plot->is_west_open === 1) {
			$extraTotal += ((float)$plot->is_west_open_amount / 100) * (float)$plot->total;
		}

		return $baseTotal + $extraTotal;
	}


	public function customPaymentScheduleTotal(){
        $total = 0;
        $discount = 0;
        if($this->customerpaymentSchedule){
        	foreach($this->customerpaymentSchedule as $customPayment){
        		$total += $customPayment->amount;
        	}
        }
        return $total;
    }
    
    /*public function getLastTransactionDetails()
    {
        $sql = "
            (SELECT transaction_number, createdOn,amount, 'main' as source
             FROM customer_plot_transactions
             WHERE plot_id = :plot_id
             ORDER BY createdOn DESC
             LIMIT 1)
    
            UNION ALL
    
            (SELECT transaction_number, createdOn,amount, 'extra' as source
             FROM customer_plot_extra_transactions
             WHERE plot_id = :plot_id
             ORDER BY createdOn DESC
             LIMIT 1)
    
            ORDER BY createdOn DESC
            LIMIT 1
        ";
    
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':plot_id', $this->id);
    
        return $command->queryRow();
    }*/
    public function getLastTransactionDetails()
    {
        $sql = "
    
        (SELECT 
            t.transaction_number, 
            MAX(t.createdOn) as createdOn,
            SUM(t.amount) as amount,
            'main' as source
         FROM customer_plot_transactions t
         WHERE t.plot_id = :plot_id
           AND t.transaction_number = (
                SELECT transaction_number 
                FROM customer_plot_transactions 
                WHERE plot_id = :plot_id 
                ORDER BY createdOn DESC 
                LIMIT 1
           )
        )
    
        UNION ALL
    
        (SELECT 
            e.transaction_number, 
            MAX(e.createdOn) as createdOn,
            SUM(e.amount) as amount,
            'extra' as source
         FROM customer_plot_extra_transactions e
         WHERE e.plot_id = :plot_id
           AND e.transaction_number = (
                SELECT transaction_number 
                FROM customer_plot_extra_transactions 
                WHERE plot_id = :plot_id 
                ORDER BY createdOn DESC 
                LIMIT 1
           )
        )
    
        ORDER BY createdOn DESC
        LIMIT 1
        ";
    
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':plot_id', $this->id);
    
        return $command->queryRow();
    }
    
    
    /**
     * Get the latest transaction group (all transactions with the same transaction number)
     * from both customer_plot_transactions and customer_plot_extra_transactions
     * 
     * @return array Array containing all transactions with the latest transaction number
     */
    public function getLastTransactionGroup()
    {
        $result = array(
            'main_transactions' => array(),
            'extra_transactions' => array(),
            'transaction_number' => null,
            'latest_date' => null,
            'total_amount' => 0
        );
        
        // Get the latest transaction number from main transactions
        $latestMain = Yii::app()->db->createCommand()
            ->select('transaction_number, createdOn')
            ->from('customer_plot_transactions')
            ->where('plot_id = :plot_id', array(':plot_id' => $this->id))
            ->order('createdOn DESC, id DESC')
            ->limit(1)
            ->queryRow();
        
        // Get the latest transaction number from extra transactions
        $latestExtra = Yii::app()->db->createCommand()
            ->select('transaction_number, createdOn')
            ->from('customer_plot_extra_transactions')
            ->where('plot_id = :plot_id', array(':plot_id' => $this->id))
            ->order('createdOn DESC, id DESC')
            ->limit(1)
            ->queryRow();
        
        // Determine which is the latest
        $latestTxnNumber = null;
        $latestDate = null;
        
        if ($latestMain && $latestExtra) {
            if (strtotime($latestMain['createdOn']) >= strtotime($latestExtra['createdOn'])) {
                $latestTxnNumber = $latestMain['transaction_number'];
                $latestDate = $latestMain['createdOn'];
            } else {
                $latestTxnNumber = $latestExtra['transaction_number'];
                $latestDate = $latestExtra['createdOn'];
            }
        } elseif ($latestMain) {
            $latestTxnNumber = $latestMain['transaction_number'];
            $latestDate = $latestMain['createdOn'];
        } elseif ($latestExtra) {
            $latestTxnNumber = $latestExtra['transaction_number'];
            $latestDate = $latestExtra['createdOn'];
        }
        
        if ($latestTxnNumber === null) {
            return $result;
        }
        
        $result['transaction_number'] = $latestTxnNumber;
        $result['latest_date'] = $latestDate;
        
        // Get all main transactions with this transaction number
        $mainTransactions = CustomerPlotTransactions::model()->findAll(array(
            'condition' => 'plot_id = :plot_id AND transaction_number = :txn_number',
            'params' => array(
                ':plot_id' => $this->id,
                ':txn_number' => $latestTxnNumber
            ),
            'order' => 'createdOn ASC, id ASC'
        ));
        
        // Get all extra transactions with this transaction number
        $extraTransactions = CustomerPlotExtraTransactions::model()->findAll(array(
            'condition' => 'plot_id = :plot_id AND transaction_number = :txn_number',
            'params' => array(
                ':plot_id' => $this->id,
                ':txn_number' => $latestTxnNumber
            ),
            'order' => 'createdOn ASC, id ASC'
        ));
        
        $result['main_transactions'] = $mainTransactions;
        $result['extra_transactions'] = $extraTransactions;
        
        // Calculate total amount
        $totalAmount = 0;
        foreach ($mainTransactions as $txn) {
            $totalAmount += (float)$txn->amount;
        }
        foreach ($extraTransactions as $txn) {
            $totalAmount += (float)$txn->amount;
        }
        $result['total_amount'] = $totalAmount;
        
        return $result;
    }
    
    /**
     * Alternative: Get all transaction groups with their transactions
     * 
     * @return array Array of transaction groups with their transactions
     */
    public function getAllTransactionGroups()
    {
        $result = array();
        
        // Get all unique transaction numbers from main transactions
        $mainTxnNumbers = Yii::app()->db->createCommand()
            ->selectDistinct('transaction_number')
            ->from('customer_plot_transactions')
            ->where('plot_id = :plot_id', array(':plot_id' => $this->id))
            ->queryColumn();
        
        // Get all unique transaction numbers from extra transactions
        $extraTxnNumbers = Yii::app()->db->createCommand()
            ->selectDistinct('transaction_number')
            ->from('customer_plot_extra_transactions')
            ->where('plot_id = :plot_id', array(':plot_id' => $this->id))
            ->queryColumn();
        
        // Merge and get unique transaction numbers
        $allTxnNumbers = array_unique(array_merge($mainTxnNumbers, $extraTxnNumbers));
        
        foreach ($allTxnNumbers as $txnNumber) {
            $group = array(
                'transaction_number' => $txnNumber,
                'main_transactions' => array(),
                'extra_transactions' => array(),
                'total_amount' => 0,
                'latest_date' => null
            );
            
            // Get main transactions for this transaction number
            $mainTransactions = CustomerPlotTransactions::model()->findAll(array(
                'condition' => 'plot_id = :plot_id AND transaction_number = :txn_number',
                'params' => array(
                    ':plot_id' => $this->id,
                    ':txn_number' => $txnNumber
                ),
                'order' => 'createdOn ASC, id ASC'
            ));
            
            // Get extra transactions for this transaction number
            $extraTransactions = CustomerPlotExtraTransactions::model()->findAll(array(
                'condition' => 'plot_id = :plot_id AND transaction_number = :txn_number',
                'params' => array(
                    ':plot_id' => $this->id,
                    ':txn_number' => $txnNumber
                ),
                'order' => 'createdOn ASC, id ASC'
            ));
            
            $group['main_transactions'] = $mainTransactions;
            $group['extra_transactions'] = $extraTransactions;
            
            // Calculate total and latest date
            $totalAmount = 0;
            $latestDate = null;
            
            foreach ($mainTransactions as $txn) {
                $totalAmount += (float)$txn->amount;
                if ($latestDate === null || strtotime($txn->createdOn) > strtotime($latestDate)) {
                    $latestDate = $txn->createdOn;
                }
            }
            foreach ($extraTransactions as $txn) {
                $totalAmount += (float)$txn->amount;
                if ($latestDate === null || strtotime($txn->createdOn) > strtotime($latestDate)) {
                    $latestDate = $txn->createdOn;
                }
            }
            
            $group['total_amount'] = $totalAmount;
            $group['latest_date'] = $latestDate;
            
            $result[] = $group;
        }
        
        // Sort by latest date descending
        usort($result, function($a, $b) {
            if ($a['latest_date'] === null) return 1;
            if ($b['latest_date'] === null) return -1;
            return strtotime($b['latest_date']) - strtotime($a['latest_date']);
        });
        
        return $result;
    }
    
    /**
     * Simple function to get the latest transaction group summary
     * 
     * @return array|null Returns array with transaction details or null if none found
     */
    public function getLatestTransactionGroupSummary()
    {
        $group = $this->getLastTransactionGroup();
        
        if (empty($group['main_transactions']) && empty($group['extra_transactions'])) {
            return null;
        }
        
        return array(
            'transaction_number' => $group['transaction_number'],
            'latest_date' => $group['latest_date'],
            'total_amount' => $group['total_amount'],
            'main_count' => count($group['main_transactions']),
            'extra_count' => count($group['extra_transactions']),
            'all_transactions' => array_merge($group['main_transactions'], $group['extra_transactions'])
        );
    }
    
    /**
     * Get formatted transaction data for display
     * Returns structured data ready for view rendering
     * 
     * @param string $type 'latest' or 'all'
     * @return array Formatted transaction data
     */
    public function getFormattedTransactions($type = 'latest')
    {
        $result = array(
            'groups' => array(),
            'grand_total' => 0,
            'has_transactions' => false
        );
        
        if ($type == 'latest') {
            $groups = array($this->getLastTransactionGroup());
        } else {
            $groups = $this->getAllTransactionGroups();
        }
        
        if (empty($groups) || (empty($groups[0]['main_transactions']) && empty($groups[0]['extra_transactions']))) {
            return $result;
        }
        
        foreach ($groups as $group) {
            if (empty($group['main_transactions']) && empty($group['extra_transactions'])) {
                continue;
            }
            
            $formattedGroup = array(
                'transaction_number' => $group['transaction_number'],
                'latest_date' => $group['latest_date'],
                'total_amount' => $group['total_amount'],
                'payment_modes' => array(),
                'transactions' => array(),
                'main_count' => count($group['main_transactions']),
                'extra_count' => count($group['extra_transactions'])
            );
            
            // Get payment modes from main transactions
            foreach ($group['main_transactions'] as $txn) {
                $modeName = isset($txn->plotPaymentMode) ? $txn->plotPaymentMode->mode : 'N/A';
                $formattedGroup['payment_modes'][] = $modeName;
                
                $formattedGroup['transactions'][] = array(
                    'id' => $txn->id,
                    'transaction_number' => $txn->transaction_number,
                    'payment_mode' => $modeName,
                    'amount' => (float)$txn->amount,
                    'createdOn' => $txn->createdOn,
                    'type' => 'Main',
                    'status' => $txn->status == 1 ? 'Active' : 'Inactive',
                    'status_class' => $txn->status == 1 ? 'label-success' : 'label-danger',
                    'type_class' => 'label-info',
                    'is_main' => true
                );
            }
            
            // Get payment modes from extra transactions
            foreach ($group['extra_transactions'] as $txn) {
                $modeName = !empty($txn->plot_payment_mode) ? ucfirst(str_replace('_', ' ', $txn->plot_payment_mode)) : 'N/A';
                $formattedGroup['payment_modes'][] = $modeName;
                
                $formattedGroup['transactions'][] = array(
                    'id' => $txn->id,
                    'transaction_number' => $txn->transaction_number,
                    'payment_mode' => $modeName,
                    'amount' => (float)$txn->amount,
                    'createdOn' => $txn->createdOn,
                    'type' => 'Extra',
                    'status' => $txn->status == 1 ? 'Active' : 'Inactive',
                    'status_class' => $txn->status == 1 ? 'label-success' : 'label-danger',
                    'type_class' => 'label-success',
                    'is_main' => false
                );
            }
            
            // Remove duplicate payment modes and sort
            $formattedGroup['payment_modes'] = array_unique($formattedGroup['payment_modes']);
            $formattedGroup['payment_modes_string'] = implode(', ', $formattedGroup['payment_modes']);
            
            // Sort transactions by createdOn
            usort($formattedGroup['transactions'], function($a, $b) {
                return strtotime($a['createdOn']) - strtotime($b['createdOn']);
            });
            
            $result['groups'][] = $formattedGroup;
            $result['grand_total'] += $formattedGroup['total_amount'];
        }
        
        $result['has_transactions'] = !empty($result['groups']);
        return $result;
    }
    
    /**
     * Get formatted latest transaction group
     */
    public function getFormattedLatestTransaction()
    {
        return $this->getFormattedTransactions('latest');
    }
    
    /**
     * Get formatted all transaction groups
     */
    public function getFormattedAllTransactions()
    {
        return $this->getFormattedTransactions('all');
    }
}
