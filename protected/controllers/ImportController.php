<?php

class ImportController extends Controller
{
	public function actionPlotData()
	{
		if (empty($_FILES['plot']['tmp_name'])) {
			Yii::app()->user->setFlash('error', 'Please upload a CSV file.');
			$this->redirect(Yii::app()->baseUrl.'/import/upload');
			return;
		}

		$uploadFolder = getcwd() . '/imports/plots/';
		if (!is_dir($uploadFolder)) {
			mkdir($uploadFolder, 0777, true);
		}

		$fileName = 'report.csv';
		move_uploaded_file($_FILES['plot']['tmp_name'], $uploadFolder.$fileName);

		$header = array();
		$result = array();
		if (($handle = fopen($uploadFolder.$fileName, 'r')) !== FALSE) {
			while (($row = fgetcsv($handle, 100000, ',')) !== FALSE) {
				if (empty($header)) {
					if (isset($row[0])) {
						$row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
					}
					$header = $row;
				} else {
					$result[] = $row;
				}
			}
			fclose($handle);
		}

		if (empty($result)) {
			Yii::app()->user->setFlash('error', 'The CSV file is empty or invalid.');
			$this->redirect(Yii::app()->baseUrl.'/import/upload');
			return;
		}

		$columnMap = $this->mapPlotImportColumns($header);
		$userModel = Yii::app()->session->get('userModel');
		$phaseId = !empty($userModel['phase_id']) ? $userModel['phase_id'] : 1;

		$created = 0;
		$updated = 0;
		$skipped = array();

		foreach ($result as $index => $row) {
			$rowNumber = $index + 2;
			$data = $this->extractPlotImportRow($row, $columnMap);

			if ($data['block_number'] === '' && $data['plot_number'] === '') {
				continue;
			}

			if ($data['block_number'] === '' || $data['plot_number'] === '') {
				$skipped[] = 'Row '.$rowNumber.': Block # and Plot # are required.';
				continue;
			}

			if ($data['category'] === '') {
				$skipped[] = 'Row '.$rowNumber.': Category is required (Residential or Commercial).';
				continue;
			}

			$categoryId = $this->resolveCategoryId($data['category']);
			if (!$categoryId) {
				$skipped[] = 'Row '.$rowNumber.': Category "'.$data['category'].'" not found.';
				continue;
			}

			if ($data['size'] === '') {
				$skipped[] = 'Row '.$rowNumber.': Size is required.';
				continue;
			}

			$sizeId = $this->resolveSizeId($data['size']);
			if (!$sizeId) {
				$skipped[] = 'Row '.$rowNumber.': Size "'.$data['size'].'" could not be saved.';
				continue;
			}

			$criteria = new CDbCriteria();
			$criteria->compare('block_number', $data['block_number']);
			$criteria->compare('plot_number', $data['plot_number']);
			$criteria->compare('phase_id', $phaseId);
			if ($data['plot_type'] !== '') {
				$criteria->compare('plot_type', $data['plot_type']);
			}

			$plot = Plots::model()->find($criteria);
			$isNew = false;
			if (!$plot) {
				$plot = new Plots;
				$plot->phase_id = $phaseId;
				$plot->is_road_facing = 0;
				$plot->is_road_facing_amount = 10;
				$plot->is_corner = 0;
				$plot->is_corner_amount = 10;
				$plot->is_park_facing = 0;
				$plot->is_park_facing_amount = 10;
				$plot->is_west_open = 0;
				$plot->is_west_open_amount = 10;
				$plot->total = 0;
				$plot->discount = 0;
				$plot->status = 0;
				$plot->length = '';
				$plot->width = '';
				$plot->description = '';
				$isNew = true;
			}

			$plot->block_number = $data['block_number'];
			$plot->plot_number = $data['plot_number'];
			$plot->plot_type = $data['plot_type'];
			$plot->category_id = $categoryId;
			$plot->size_id = $sizeId;

			if ($data['has']['description']) {
				$plot->description = $data['description'];
			}
			if ($data['has']['length']) {
				$plot->length = $data['length'];
			}
			if ($data['has']['width']) {
				$plot->width = $data['width'];
			}
			if ($data['has']['is_road_facing']) {
				$plot->is_road_facing = $this->parseFlagValue($data['is_road_facing']);
			}
			if ($data['has']['is_corner']) {
				$plot->is_corner = $this->parseFlagValue($data['is_corner']);
			}
			if ($data['has']['is_park_facing']) {
				$plot->is_park_facing = $this->parseFlagValue($data['is_park_facing']);
			}
			if ($data['has']['is_west_open']) {
				$plot->is_west_open = $this->parseFlagValue($data['is_west_open']);
			}
			if ($data['has']['is_road_facing_amount']) {
				$plot->is_road_facing_amount = $this->parseNumberValue($data['is_road_facing_amount'], 10);
			}
			if ($data['has']['is_corner_amount']) {
				$plot->is_corner_amount = $this->parseNumberValue($data['is_corner_amount'], 10);
			}
			if ($data['has']['is_park_facing_amount']) {
				$plot->is_park_facing_amount = $this->parseNumberValue($data['is_park_facing_amount'], 10);
			}
			if ($data['has']['is_west_open_amount']) {
				$plot->is_west_open_amount = $this->parseNumberValue($data['is_west_open_amount'], 10);
			}
			if ($data['has']['total']) {
				$plot->total = $this->parseNumberValue($data['total'], 0);
			}
			if ($data['has']['discount']) {
				$plot->discount = $this->parseNumberValue($data['discount'], 0);
			}
			if ($data['has']['status'] && $data['status'] !== '') {
				$plot->status = $this->resolvePlotStatus($data['status']);
			}

			$plot->save(false);
			if ($isNew) {
				$created++;
			} else {
				$updated++;
			}
		}

		$message = 'Import completed. Created: '.$created.', Updated: '.$updated.'.';
		if (!empty($skipped)) {
			$message .= ' Skipped '.count($skipped).' row(s): '.implode(' ', $skipped);
			Yii::app()->user->setFlash('error', $message);
		} else {
			Yii::app()->user->setFlash('success', $message);
		}

		$this->redirect(Yii::app()->baseUrl.'/import/upload');
	}

	public function actionUpload()
	{
		$data['categories'] = PlotCategories::model()->findAll();
		$data['sizes'] = PlotSizes::model()->findAll();
		$this->render('upload', $data);
	}

	public function actionPlotsample()
	{
		$headers = $this->plotImportHeaders();
		$categories = PlotCategories::model()->findAll();
		$sizes = PlotSizes::model()->findAll();

		$residential = 'Residential';
		$commercial = 'Commercial';
		foreach ($categories as $category) {
			if (strcasecmp($category->name, 'Residential') === 0) {
				$residential = $category->name;
			}
			if (strcasecmp($category->name, 'Commercial') === 0) {
				$commercial = $category->name;
			}
		}

		$sizeA = $sizes ? $sizes[0]->size : '80 SQ.YD';
		$sizeB = (count($sizes) > 1) ? $sizes[1]->size : '120 SQ.YD';

		$rows = array(
			$headers,
			array('JB', 'L', '01', $residential, $sizeA, '', '', '', '0', '0', '0', '0', '10', '10', '10', '10', '1800000', '0', 'Available'),
			array('JB', 'R', '02', $commercial, $sizeB, '', '', '', '0', '1', '0', '0', '10', '10', '10', '10', '2600000', '0', 'Booked'),
		);

		$fileName = 'plot-import-sample.csv';
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="'.$fileName.'"');
		$fp = fopen('php://output', 'w');
		foreach ($rows as $row) {
			fputcsv($fp, $row);
		}
		fclose($fp);
		Yii::app()->end();
	}

	public function actionAgentupdate()
	{

		$data['action'] = Yii::app()->baseUrl.'/import/dealerdataupdated';
		$this->render('others',$data);
	}

	public function actionPlotupdate()
	{
		$data['action'] = Yii::app()->baseUrl.'/import/plotupdated';
		$this->render('others',$data);
	}

	public function actionUploaddealer()
	{

		$this->render('uploadDealer');
	}


	public function actionDealerData()
	{
		$uploadFolder = getcwd() . '/imports/dealers/';
        $fileName = 'report.csv';
        $orig_fileName = $_FILES['plot']['name'];
        move_uploaded_file($_FILES['plot']['tmp_name'], $uploadFolder.$fileName);
        $result = [];
        if (($handle = fopen($uploadFolder.$fileName, 'r')) !== FALSE) {
            $index = 0;
            while (($row = fgetcsv($handle, 100000, ',')) !== FALSE) {

                if (empty($header)) {
                    $header = $row;
                } else {
                    $result[] = $row;
                }
                $index++;
            }
            fclose($handle);
        }
        //echo '<pre>';print_r($result);exit;
        //$sql = 'SET FOREIGN_KEY_CHECKS = 0;TRUNCATE `agents`;TRUNCATE `agent_plots`;SET FOREIGN_KEY_CHECKS = 1;';
        //Yii::app()->db->createCommand($sql)->execute();
        //unset($result[0]);
            foreach ($result as $ind => $value) {
          		if(!empty($result)){
		            //
            	/*if(!empty($value[0])){
            		
            		$agentParent = Agents::model()->find('name = :name AND parent_id IS NULL',array(':name'=>$value[9]));

					//$ps = PaymentSchedules::model()->find('name = :name',array(':name'=>$value[7]));

            		$plot = Plots::model()->find('plot_number = :plt AND block_number = :blk AND plot_type = :pt',array(
            			':blk'=>$value[1],
            			':pt'=>$value[2],
            			':plt'=> $value[3],//sprintf('%03d', $value[3]),
            		));
            		
            		//$discount = str_replace('PKR ','',str_replace(',', '' , $value[10]));

            		
            		// echo '<pre>';
            		// print_r($value);
            		// print_r(@$agentParent->attribute);
            		// print_r($value);
            		// exit;
            		if($plot){
            			//agent
						if($agentParent){
							if($value[10]){
								$parentIDD = $agentParent->id;
								$subAgent = Agents::model()->find('name = :name AND parent_id = :parent',array(':parent'=>$parentIDD,':name'=>$value[10]));
								if(!$subAgent){
									$subAgent = new Agents;
									$subAgent->name = $value[9];
									$subAgent->parent_id = $agentParent->id;
									$subAgent->percentage = 0;//$value[11];//str_replace('%','',$value[12]);
									$subAgent->percentage_value = 0;//$value[12];
									$subAgent->number = '0';
									$subAgent->phase_id = 1;
									$subAgent->status = 1;
									$subAgent->save(false);
								}
							} else{
								$parentIDD = $agentParent->id;
								$subAgent = Agents::model()->find('name = :name AND parent_id = :parent',array(':parent'=>$parentIDD,':name'=>$value[10]));
								//$subAgent = (object)$subAgent->attributes;	
							}


							$agentPlot = AgentPlots::model()->find('plot_id = :id',array(':id'=>$plot->id));
							if($agentPlot){
								$agentPlot->delete();
							}
							$ap = new AgentPlots;
							$ap->agent_id = @$subAgent->id;
							$ap->plot_id = $plot->id;
							// if($ps){
							$ap->payment_schedule_id = 0;//$ps->id;	
							// }
							
							$ap->discount = 0;//$discount;
							//if($value[12]){
							$ap->commission = 0;//$value[12];
							//}
							$ap->createdOn = date('Y-m-d');
							$ap->save(false);


							if($plot->customerPlots){
								foreach($plot->customerPlots as $cp){
									$cp->is_agent = 1;
									$cp->agent_id = $subAgent->id;
									$cp->agent_percentage = 0;//@$value[12];
									$cp->save(false);

									//Booking Expense
									/*if($value[18]>0){
										$expModel = new Expenses;
										$expModel->expense_type = 10;
										$expModel->account_id = 5;
										$expModel->description = 'Booking agent commission for Plot *'.($cp->plot->block_number.'-'.$cp->plot->plot_type.'-'.$cp->plot->plot_number).'*';
										$expModel->amount = $value[18];
										$expModel->user_id = $a->id;
										$expModel->status = 1;
										$expModel->reason = NULL;
										$expModel->number = $value[15];
										$ddate = explode('-', $value[16]);
										$expModel->createdOn = '20'.$ddate[2].'-'.$ddate[1].'-'.$ddate[0];
										$expModel->phase_id = 1;
										$expModel->booking_id = $cp->id;
										$expModel->payment_mode = $value[13];
										$expModel->paid_to = 'Agent '.$a->name;
										$expModel->bank = $value[14];
										$expModel->cnic = $value[15];
										$expModel->save(false);
									}
									
								}
							}
							
						} else{

							//Agent
							$agentParent = Agents::model()->find('name = :name AND parent_id IS NULL',array(':name'=>$value[9]));
							if($value[10]){
								//parent
								$pa = new Agents;
								$pa->name = $value[9];
								$pa->parent_id = NULL;
								$pa->percentage = 0;//$value[11];//str_replace('%','',$value[12]);
								$pa->percentage_value = 0;
								$pa->number = '0';
								$pa->phase_id = 1;
								$pa->status = 1;
								$pa->save(false);	
								//subdealer
								$a = new Agents;
								$a->name = $value[10];
								$a->parent_id = $pa->id;
								$a->percentage = 0;//str_replace('%','',$value[12]);
								$a->percentage_value = 0;//$value[12];
								$a->number = '0';
								$a->phase_id = 1;
								$a->status = 1;
								$a->save(false);
							}
							

							$agentPlot = AgentPlots::model()->find('plot_id = :id',array(':id'=>$plot->id));
							if($agentPlot){
								$agentPlot->delete();
							}
							//agent Plot
							$ap = new AgentPlots;
							$ap->agent_id = $a->id;
							$ap->plot_id = $plot->id;
							// if($ps){
							$ap->payment_schedule_id = 0;//$ps->id;	
							// }
							//if($value[12]){
							$ap->commission = 0;//$value[12];
							//}
							$ap->discount = 0;//$discount;
							$ap->createdOn = date('Y-m-d');
							$ap->save(false);

							if($plot->customerPlots){
								foreach($plot->customerPlots as $cp){
									$cp->is_agent = 1;
									$cp->agent_id = $a->id;
									$cp->agent_percentage = 0;//@$value[12];
									$cp->save(false);

									//Booking Expense
									/*if($value[18]>0){
										$expModel = new Expenses;
										$expModel->expense_type = 10;
										$expModel->account_id = 5;
										$expModel->description = 'Booking agent commission for Plot *'.($cp->plot->block_number.'-'.$cp->plot->plot_type.'-'.$cp->plot->plot_number).'*';
										$expModel->amount = $value[18];
										$expModel->user_id = $a->id;
										$expModel->status = 1;
										$expModel->reason = NULL;
										$expModel->number = $value[15];
										$ddate = explode('-', $value[16]);
										$expModel->createdOn = $ddate[2].'-'.$ddate[1].'-'.$ddate[0];
										$expModel->phase_id = 1;
										$expModel->booking_id = $cp->id;
										$expModel->payment_mode = $value[13];
										$expModel->paid_to = 'Agent '.$a->name;
										$expModel->bank = $value[14];
										$expModel->cnic = $value[15];
										$expModel->save(false);
									}
								}
							}
						}

						//$plot->discount = $discount;
						// if($ps){
						// 	$plot->total = $this->getPaymentScheduleTotal($plot->plot_type,$ps->id);	
						// }
						
						//$plot->save(false);
            		}

            	}*/

            	//Plot
            	$plot = Plots::model()->find('plot_number = :plt AND block_number = :blk AND plot_type = :pt',array(
        			':blk'=>$value[1],
        			':pt'=>$value[2],
        			':plt'=> $value[3],//sprintf('%03d', $value[3]),
        		));
            	//Agent
				$agentParent = Agents::model()->find('name = :name AND parent_id IS NULL',array(':name'=>$value[9]));
				if($agentParent){
					$parentIDD = $agentParent->id;
					$subAgent = Agents::model()->find('name = :name AND parent_id = :parent',array(':parent'=>$parentIDD,':name'=>$value[10]));
					if(!$subAgent){
						$subAgent = new Agents;
						$subAgent->name = $value[10];
						$subAgent->parent_id = $agentParent->id;
						$subAgent->percentage = 0;//$value[11];//str_replace('%','',$value[12]);
						$subAgent->percentage_value = 0;//$value[12];
						$subAgent->number = '0';
						$subAgent->phase_id = 1;
						$subAgent->status = 1;
						$subAgent->save(false);
						$agent = $subAgent->id;
					} else{
						$agent = $subAgent->id;
					}
					

					$agentPlot = AgentPlots::model()->find('plot_id = :id',array(':id'=>$plot->id));
					if($agentPlot){
						$agentPlot->delete();
					}
					//agent Plot
					$ap = new AgentPlots;
					$ap->agent_id = $agent;
					$ap->plot_id = $plot->id;
					$ap->payment_schedule_id = 0;//$ps->id;	
					$ap->commission = 0;//$value[12];
					$ap->discount = 0;//$discount;
					$ap->createdOn = date('Y-m-d');
					$ap->save(false);

					if($plot->customerPlotsRand){
						foreach($plot->customerPlotsRand as $cp){
							$cp->is_agent = 1;
							$cp->agent_id = $agent;
							$cp->agent_percentage = 0;//@$value[12];
							$cp->save(false);
						}
					}
				} else{
					//parent
					$pa = new Agents;
					$pa->name = $value[9];
					$pa->parent_id = NULL;
					$pa->percentage = 0;//$value[11];//str_replace('%','',$value[12]);
					$pa->percentage_value = 0;
					$pa->number = '0';
					$pa->phase_id = 1;
					$pa->status = 1;
					$pa->save(false);	
					//subdealer
					$a = new Agents;
					$a->name = $value[10];
					$a->parent_id = $pa->id;
					$a->percentage = 0;//str_replace('%','',$value[12]);
					$a->percentage_value = 0;//$value[12];
					$a->number = '0';
					$a->phase_id = 1;
					$a->status = 1;
					$a->save(false);

					//add agent plot
					$agentPlot = AgentPlots::model()->find('plot_id = :id',array(':id'=>$plot->id));
					if($agentPlot){
						$agentPlot->delete();
					}
					//agent Plot
					$ap = new AgentPlots;
					$ap->agent_id = $a->id;
					$ap->plot_id = $plot->id;
					$ap->payment_schedule_id = 0;//$ps->id;	
					$ap->commission = 0;//$value[12];
					$ap->discount = 0;//$discount;
					$ap->createdOn = date('Y-m-d');
					$ap->save(false);

					if($plot->customerPlotsRand){
						foreach($plot->customerPlotsRand as $cp){
							$cp->is_agent = 1;
							$cp->agent_id = $a->id;
							$cp->agent_percentage = 0;//@$value[12];
							$cp->save(false);
						}
					}
				}
            }
        }

        echo 'Done';
	}


	public function actionDealerDataUpdated()
	{
		$uploadFolder = getcwd() . '/imports/dealers/';
        $fileName = 'report.csv';
        $orig_fileName = $_FILES['plot']['name'];
        move_uploaded_file($_FILES['plot']['tmp_name'], $uploadFolder.$fileName);
        $result = [];
        if (($handle = fopen($uploadFolder.$fileName, 'r')) !== FALSE) {
            $index = 0;
            while (($row = fgetcsv($handle, 100000, ',')) !== FALSE) {

                if (empty($header)) {
                    $header = $row;
                } else {
                    $result[] = $row;
                }
                $index++;
            }
            fclose($handle);
        }

    //$sql = 'SET FOREIGN_KEY_CHECKS = 0;TRUNCATE `agent_plots`;SET FOREIGN_KEY_CHECKS = 1;';
    //Yii::app()->db->createCommand($sql)->execute();
    print_r($result);exit;
    	foreach($result as $index=>$row){
       		$commission = str_replace('PKR ','',str_replace(',','',$row[8]));
       		$discount = str_replace('PKR ','',str_replace(',','',$row[7]));
       		$plot = Plots::model()->find('block_number = :block AND plot_type = :type AND plot_number = :number',array(':block'=>$row[1],':type'=>$row[2],':number'=>$row[3]));
       		$ps = PaymentSchedules::model()->find('name = :name',array(':name'=>$row[4]));
       		if($plot){
       			$agent = Agents::model()->find('name = :name AND parent_id IS NULL',array(':name'=>$row[5]));
       			if(!$agent){
	       			$agent = new Agents;
					$agent->name = $row[5];
					$agent->parent_id = NULL;
					$agent->percentage = 0;//str_replace('%','',$value[12]);
					$agent->percentage_value = 0;
					$agent->number = '0';
					$agent->phase_id = 1;
					$agent->status = 1;
					$agent->save(false);
				}
       			$subAgent = Agents::model()->find('name = :name AND parent_id = :parent',array(':parent'=>$agent->id,':name'=>$row[6]));
       			if(!$subAgent){
					$subAgent = new Agents;
					$subAgent->name = $row[6];
					$subAgent->parent_id = $agent->id;
					$subAgent->percentage = 0;//$value[11];//str_replace('%','',$value[12]);
					$subAgent->percentage_value = 0;//$commission;
					$subAgent->number = '0';
					$subAgent->phase_id = 1;
					$subAgent->status = 1;
					$subAgent->save(false);
				}
       			if($plot->customerPlots){

       				$cp = CustomerPlots::model()->findByPk($plot->customerPlots[0]->id);
       				$cp->agent_id = $subAgent->id;
       				$cp->agent_percentage = $commission;
       				$cp->save(false);

       				//agent Plot
					$ap = new AgentPlots;
					$ap->agent_id = $subAgent->id;
					$ap->plot_id = $plot->id;
					if($ps){
						$ap->payment_schedule_id = $ps->id;	
					}
					if($commission){
						$ap->commission = $commission;
					}
					$ap->discount = $row[7];
					$ap->createdOn = date('Y-m-d');
					$ap->save(false);

       			} else{
       				//agent Plot
					$ap = new AgentPlots;
					$ap->agent_id = $subAgent->id;
					$ap->plot_id = $plot->id;
					if($ps){
						$ap->payment_schedule_id = $ps->id;	
					}
					if($commission){
						$ap->commission = $commission;
					}
					$ap->discount = $discount;
					$ap->createdOn = date('Y-m-d');
					$ap->save(false);
       			}
       		}
       }
       echo 'Done';
    }

    public function actionPlotUpdated()
	{
		$uploadFolder = getcwd() . '/imports/dealers/';
        $fileName = 'report.csv';
        $orig_fileName = $_FILES['plot']['name'];
        move_uploaded_file($_FILES['plot']['tmp_name'], $uploadFolder.$fileName);
        $result = [];
        if (($handle = fopen($uploadFolder.$fileName, 'r')) !== FALSE) {
            $index = 0;
            while (($row = fgetcsv($handle, 100000, ',')) !== FALSE) {

                if (empty($header)) {
                    $header = $row;
                } else {
                    $result[] = $row;
                }
                $index++;
            }
            fclose($handle);
        }

    $sql = 'SET FOREIGN_KEY_CHECKS = 0;TRUNCATE `plot_boundries`;SET FOREIGN_KEY_CHECKS = 1;';
    Yii::app()->db->createCommand($sql)->execute();
    //echo '<pre>';
    	foreach($result as $index=>$row){
    		
       		$plot = Plots::model()->find('block_number = :block AND plot_type = :type AND plot_number = :number',array(':block'=>$row[0],':type'=>$row[1],':number'=>$row[2]));
       		if($plot){
       			PlotBoundries::model()->deleteAll("plot_id = $plot->id");
				if($row[11]){
					$pb = new PlotBoundries;
					$pb->plot_id = $plot->id;
					$pb->north = $row[11];
					$pb->west = $row[14];
					$pb->south = $row[12];
					$pb->east = $row[13];
					$pb->save(false);
				}
       		}
       }
       echo 'Done';
    }


	protected function plotImportHeaders()
	{
		return array(
			'Block #',
			'Plot Type',
			'Plot #',
			'Category',
			'Size',
			'Description',
			'Length',
			'Width',
			'Road Facing',
			'Corner',
			'Park Facing',
			'West Open',
			'Road Facing Amount',
			'Corner Amount',
			'Park Facing Amount',
			'West Open Amount',
			'Total',
			'Discount',
			'Status',
		);
	}

	protected function mapPlotImportColumns($header)
	{
		$aliases = array(
			'block' => 'block_number',
			'block#' => 'block_number',
			'blocknumber' => 'block_number',
			'plottype' => 'plot_type',
			'type' => 'plot_type',
			'plot' => 'plot_number',
			'plot#' => 'plot_number',
			'plotno' => 'plot_number',
			'plotnumber' => 'plot_number',
			'category' => 'category',
			'size' => 'size',
			'sqyds' => 'size',
			'sqyd' => 'size',
			'description' => 'description',
			'details' => 'description',
			'length' => 'length',
			'width' => 'width',
			'roadfacing' => 'is_road_facing',
			'corner' => 'is_corner',
			'parkfacing' => 'is_park_facing',
			'westopen' => 'is_west_open',
			'roadfacingamount' => 'is_road_facing_amount',
			'corneramount' => 'is_corner_amount',
			'parkfacingamount' => 'is_park_facing_amount',
			'westopenamount' => 'is_west_open_amount',
			'total' => 'total',
			'discount' => 'discount',
			'status' => 'status',
		);

		$map = array();
		foreach ($header as $index => $label) {
			$key = $this->normalizeImportHeader($label);
			if (isset($aliases[$key])) {
				$map[$aliases[$key]] = $index;
			}
		}

		if (isset($map['block_number']) && isset($map['plot_number'])) {
			return $map;
		}

		return array(
			'block_number' => 0,
			'plot_type' => 1,
			'plot_number' => 2,
			'category' => 3,
			'size' => 4,
			'description' => 5,
			'length' => 6,
			'width' => 7,
			'is_road_facing' => 8,
			'is_corner' => 9,
			'is_park_facing' => 10,
			'is_west_open' => 11,
			'is_road_facing_amount' => 12,
			'is_corner_amount' => 13,
			'is_park_facing_amount' => 14,
			'is_west_open_amount' => 15,
			'total' => 16,
			'discount' => 17,
			'status' => 18,
		);
	}

	protected function extractPlotImportRow($row, $columnMap)
	{
		$fields = array(
			'block_number', 'plot_type', 'plot_number', 'category', 'size',
			'description', 'length', 'width', 'is_road_facing', 'is_corner',
			'is_park_facing', 'is_west_open', 'is_road_facing_amount',
			'is_corner_amount', 'is_park_facing_amount', 'is_west_open_amount',
			'total', 'discount', 'status',
		);

		$data = array('has' => array());
		foreach ($fields as $field) {
			$data['has'][$field] = array_key_exists($field, $columnMap);
			$value = '';
			if ($data['has'][$field] && isset($row[$columnMap[$field]])) {
				$value = trim($row[$columnMap[$field]]);
			}
			$data[$field] = $value;
		}

		return $data;
	}

	protected function normalizeImportHeader($label)
	{
		$label = strtolower(trim($label));
		$label = str_replace(array('.', '_', '-', ' '), '', $label);
		return $label;
	}

	protected function resolveCategoryId($name)
	{
		$name = trim($name);
		if ($name === '') {
			return null;
		}

		$category = PlotCategories::model()->find('LOWER(name) = :name', array(
			':name' => strtolower($name),
		));
		if ($category) {
			return $category->id;
		}

		return null;
	}

	protected function resolveSizeId($sizeText)
	{
		$sizeText = trim($sizeText);
		if ($sizeText === '') {
			return null;
		}

		$size = PlotSizes::model()->find('size = :size', array(':size' => $sizeText));
		if ($size) {
			return $size->id;
		}

		$size = PlotSizes::model()->find('LOWER(size) = :size', array(
			':size' => strtolower($sizeText),
		));
		if ($size) {
			return $size->id;
		}

		$normalized = $this->normalizeSizeText($sizeText);
		$allSizes = PlotSizes::model()->findAll();
		foreach ($allSizes as $existing) {
			if ($this->normalizeSizeText($existing->size) === $normalized) {
				return $existing->id;
			}
		}

		$size = new PlotSizes;
		$size->size = $sizeText;
		$size->size_amount = 0;
		$size->save(false);

		return $size->id;
	}

	protected function normalizeSizeText($text)
	{
		$text = strtolower(trim($text));
		$text = str_replace(array('.', ',', '-'), '', $text);
		$text = preg_replace('/\s+/', '', $text);
		$text = str_replace(array('squareyards', 'sqyards', 'sqyrd', 'sqyds', 'sqyd', 'sqy', 'syds', 'syd', 'sy'), '', $text);
		return $text;
	}

	protected function resolvePlotStatus($status)
	{
		$status = strtolower(trim($status));
		if (in_array($status, array('booked', '1', 'sold'))) {
			return 1;
		}

		return 0;
	}

	protected function parseFlagValue($value)
	{
		$value = strtolower(trim($value));
		if (in_array($value, array('1', 'yes', 'y', 'true'))) {
			return 1;
		}

		return 0;
	}

	protected function parseNumberValue($value, $default = 0)
	{
		$value = trim($value);
		if ($value === '') {
			return $default;
		}

		$value = str_replace(array('PKR', ',', ' '), '', $value);
		return is_numeric($value) ? $value : $default;
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