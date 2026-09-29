<?php

class PaymentscheduleController extends Controller
{
	public function init()
	{
	    if(!isset(Yii::app()->session['userModel']))
	    {
	        $this->redirect(Yii::app()->baseUrl.'/');
	    }
	}

	protected function getDistinctBlocks()
	{
		$phaseId = Yii::app()->session->get('userModel')['phase_id'];
		return Plots::model()->findAll(array(
			'select' => 't.block_number',
			'distinct' => true,
			'condition' => "phase_id=$phaseId",
			'order' => 't.block_number ASC',
		));
	}

	public function actionAdd()
	{
		$data['types'] = $this->getDistinctBlocks();
		$data['total'] = PaymentSchedules::model()->count();
		$this->render('add',$data);
	}


	public function actionCustomschedule($id)
	{
		$phaseId = Yii::app()->session->get('userModel')['phase_id'];
		$data['types'] = Plots::model()->findAll(array(
                    'select'=>'t.plot_type',
                    'distinct'=>true,
                    'condition'=>"phase_id=$phaseId",
                ));
		$data['total'] = PaymentSchedules::model()->count();
		$data['booking'] = $booking =  CustomerPlots::model()->findByPk($id);
		$paymentmodes = PaymentSchedulePaymentModes::model()->findAll('payment_schedule_id = :id AND plot_type = :type',array(':id'=>$booking->paymentSchedule->id,':type'=>strtolower($booking->plot->block_number)));
		if($paymentmodes){
			foreach($paymentmodes as $pmodes){
				$data['paymentmodes'][$pmodes->mode] = $pmodes->attributes; 
			}
		}
		if($booking->customerpaymentSchedule){
		    $modesCustom = CustomPaymentSchedulePaymentModes::model()->findAll('booking_id = :id',array(':id'=>$booking->id));
		    foreach($modesCustom as $pmodes){
		    	$data['paymentmodesNew'][$pmodes->mode] = $pmodes->attributes; 
		    }
		}
		$this->render('customAdd',$data);
	}

	public function actionEdit($id)
	{
		$data['types'] = $this->getDistinctBlocks();
		$data['total'] = PaymentSchedules::model()->count();
		$data['PaymentSchedule'] = PaymentSchedules::model()->findByPk($id);
		$this->render('edit',$data);
	}

	public function actionUpdate()
	{
		$paymentSchedule = PaymentSchedules::model()->findByPk($_POST['id']);
		if($paymentSchedule){
			$paymentSchedule->name = $_POST['name'];
			$paymentSchedule->save();
			foreach($_POST['payment'] as $mode=>$plotTypes):
				foreach($plotTypes as $type=>$amount):
					$amountValue = is_array($amount) ? $amount['amount'] : $amount;
					$recordId = is_array($amount) ? @$amount['id'] : null;
					$psm = null;
					if(!empty($recordId)){
						$psm = PaymentSchedulePaymentModes::model()->findByPk($recordId);
					}
					if(!$psm){
						$psm = PaymentSchedulePaymentModes::model()->find(
							'payment_schedule_id = :id AND mode = :mode AND plot_type = :type',
							array(':id'=>$paymentSchedule->id, ':mode'=>$mode, ':type'=>$type)
						);
					}
					if(!$psm){
						$psm = new PaymentSchedulePaymentModes;
					}
					$psm->payment_schedule_id = $paymentSchedule->id;
					$psm->plot_type = $type;
					$psm->mode = $mode;
					$psm->amount = $amountValue;
					$psm->save();
				endforeach;
			endforeach;
		}
		Yii::app()->user->setFlash('success','Payment Schedule updated successfully.');
        $this->redirect(Yii::app()->baseUrl.'/paymentschedule');
	}

	public function actionIndex()
	{
		$data['types'] = $this->getDistinctBlocks();
		$data['payments'] = PaymentSchedules::model()->findAll();
		$this->render('index',$data);		
	}

	public function actionExport()
	{
		$types = $this->getDistinctBlocks();
		$payments = PaymentSchedules::model()->with('paymentSchedulePaymentModes')->findAll();

		$header = array('Name');
		foreach ($types as $type) {
			$header[] = $type->block_number;
		}

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=payment_schedules_'.date('Ymd_His').'.csv');

		$output = fopen('php://output', 'w');
		fputcsv($output, $header);

		foreach ($payments as $payment) {
			$paymentDetail = array();
			foreach ($payment->paymentSchedulePaymentModes as $pspD) {
				$paymentDetail[strtolower($pspD->mode)][strtolower($pspD->plot_type)] = $pspD->amount;
			}

			$nameRow = array($payment->name);
			foreach ($types as $type) {
				$nameRow[] = '';
			}
			fputcsv($output, $nameRow);

			foreach ($this->paymentScheduleModes() as $modes) {
				$row = array(ucfirst($modes));
				foreach ($types as $type) {
					$row[] = isset($paymentDetail[strtolower($modes)][strtolower($type->block_number)])
						? $paymentDetail[strtolower($modes)][strtolower($type->block_number)]
						: '-';
				}
				fputcsv($output, $row);
			}

			$totalRow = array('Total');
			foreach ($types as $type) {
				$totalRow[] = $this->getPaymentScheduleTotal($type->block_number, $payment->id);
			}
			fputcsv($output, $totalRow);
			fputcsv($output, array());
		}

		fclose($output);
		Yii::app()->end();
	}

	public function actionSave()
	{
		if($_POST['name']){
			$ps = new PaymentSchedules;
			$ps->name = $_POST['name'];
			$ps->createdOn = date('Y-m-d H:i:s');
			if($ps->save()){
				foreach($_POST['payment'] as $mode=>$plotTypes):
					foreach($plotTypes as $type=>$amount):
						$psm = new PaymentSchedulePaymentModes;
						$psm->payment_schedule_id = $ps->id;
						$psm->plot_type = $type;
						$psm->mode = $mode;
						$psm->amount = $amount;
						$psm->save();
					endforeach;
				endforeach;
			}
			Yii::app()->user->setFlash('success','Payment Schedule add successfully.');
            $this->redirect(Yii::app()->baseUrl.'/paymentschedule');
		}		
	}

	public function actionSavecustom()
	{
		if($_POST['booking_id']){
			$cp = CustomerPlots::model()->findByPk($_POST['booking_id']);
			if($cp){
				$cp->monthlyMonths = $_POST['monthlyMonths'];
				$cp->monthlyYearlies = $_POST['monthlyYearlies'];
				$cp->save(false);
				CustomPaymentSchedulePaymentModes::model()->deleteAll('booking_id = :id',array(':id'=>$_POST['booking_id']));
				if($_POST['payment']['booking'] > 0){
					foreach($_POST['payment'] as $mode=>$amount):
						$psm = new CustomPaymentSchedulePaymentModes;	
						$psm->booking_id = $_POST['booking_id'];
						$psm->mode = $mode;
						$psm->amount = $amount;
						$psm->save();
					endforeach;
				}	
			}
			
			Yii::app()->user->setFlash('success','Custom Payment Schedule updated successfully.');
            $this->redirect(Yii::app()->baseUrl.'/booking/viewbooking/'.$_POST['booking_id']);
		}		
	}

}