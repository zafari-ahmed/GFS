<?php

class SizesController extends Controller
{
	public function actionAdd()
	{
		$this->render('add');
	}

	public function actionEdit($id)
	{
		$data['sizes'] = PlotSizes::model()->findByPk($id);	
		$this->render('edit',$data);
	}

	public function actionIndex()
	{
		$data['sizes'] = PlotSizes::model()->findAll();
		$this->render('index',$data);
	}

	public function actionSave(){
		if($_POST['size']){
			//$sizes = PlotSizes::model()->findAll('size = :size',array(':size'=>$_POST['size']));
			//if(empty($sizes)){
				$plot = new PlotSizes;
				$plot->attributes = $_POST;
				$plot->save(false);	
				$modes = ['Booking','Allocation','Confirmation','Demarcation','Possession'];
				$totalAmount = $_POST['size_amount'];
				$remaingAmount = $totalAmount;
				foreach($modes as $modee){
					$mode = New PaymentModes();
					$mode->mode = $modee;
					$mode->plot_size_id = $plot->id;
					$totalAmountPercentage = $this->Percentage($totalAmount,10,0);
					$mode->amount = $totalAmountPercentage;
					$remaingAmount = $totalAmount - $totalAmountPercentage;
					$mode->save(false);
				}
				$mode = New PaymentModes();
				$mode->mode = 'Monthly';
				$mode->plot_size_id = $plot->id;
				$mode->amount = ($this->Percentage($totalAmount,50,0))/60;
				$mode->save(false);
				Yii::app()->user->setFlash('success','Plot Size has been saved.');
			// } else{
			// 	Yii::app()->user->setFlash('error','Same Plot Size.');
			// }
			
			
            $this->redirect(Yii::app()->baseUrl.'/sizes');
		}
	}

	public function actionUpdate()
	{
		if($_POST['id']){
			$plot = PlotSizes::model()->findByPk($_POST['id']);
			$plot->attributes = $_POST;
			$plot->save(false);
			Yii::app()->user->setFlash('success','Plot size has been Updated.');
            $this->redirect(Yii::app()->baseUrl.'/sizes');
		}
	}

	public function actionDelete($id)
	{
		$size = PlotSizes::model()->findByPk($id);
		if(!$size){
			Yii::app()->user->setFlash('error','Plot size not found.');
			$this->redirect(Yii::app()->baseUrl.'/sizes');
			return;
		}

		$plotExists = Plots::model()->exists('size_id = :size_id', array(':size_id' => $id));
		if($plotExists){
			Yii::app()->user->setFlash('error','This plot size cannot be deleted because it is already assigned to a plot.');
			$this->redirect(Yii::app()->baseUrl.'/sizes');
			return;
		}

		PaymentModes::model()->deleteAll('plot_size_id = :size_id', array(':size_id' => $id));
		$size->delete();
		Yii::app()->user->setFlash('success','Plot size has been deleted.');
		$this->redirect(Yii::app()->baseUrl.'/sizes');
	}

	public function Percentage($total,$percentage,$view = 1){
		if($view == 1){
			return number_format((@$percentage / 100) * @$total);
		} else{
			return (@$percentage / 100) * @$total;
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