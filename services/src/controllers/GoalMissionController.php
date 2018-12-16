<?php

    namespace App\Controller;
    
    use App\Service\GoalMissionService;

    class GoalMissionController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function getList($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $condition = $params['obj']['condition'];
                $_List = GoalMissionService::getList($condition);

                $this->data_result['DATA']['List'] = $_List;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getData($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $id = $params['obj']['id'];
                
                $_Data = GoalMissionService::getData($id);

                $this->data_result['DATA']['Data'] = $_Data;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function updateDataEditable($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $id = $params['obj']['id'];
                $editable = $params['obj']['editable'];
                $user_session = $params['user_session'];
                // print_r($user_session);
                $unlock_name = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                // exit;

                $change_date = date('Y-m-d H:i:s');
                $arr_history = ['goal_mission_id' => $id, 'unlock_name' => $unlock_name, 'change_date' => $change_date];

                $result = GoalMissionService::updateDataEditable($id, $editable);

                GoalMissionService::addHistory($arr_history);

                $this->data_result['DATA']['result'] = $result;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }   
        }

        public function updateData($request, $response, $args){
            
            try{
                // error_reporting(E_ERROR);
                // error_reporting(E_ALL);
                // ini_set('display_errors','On');
                $params = $request->getParsedBody();
                $_Data = $params['obj']['Data'];
                $_AvgData = $params['obj']['AvgList'];
                $user_session = $params['user_session'];
                $edit_name = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                unset($_Data['goal_mission_avg']);
                unset($_Data['goal_mission_history']);
                
                $_Data['update_by'] = $user_session['UserID'];
                $_Data['editable'] = 'N';
                $id = GoalMissionService::updateData($_Data);

                // check what update in avg data then save to history
                $AvgList = GoalMissionService::getAvgList($id);
                $cnt = 0;
                foreach ($AvgList as $key => $value) {
                    if($value['amount'] != $_AvgData[$cnt]['amount']){
                        // Save to history
                        $HistoryData = $_AvgData[$cnt];
                        unset($HistoryData['id']);
                        $HistoryData['goal_mission_id'] = $id;
                        $HistoryData['edit_name'] = $edit_name;
                        GoalMissionService::addHistory($HistoryData);
                    }
                    $cnt++;
                }

                // update avg data
                foreach ($_AvgData as $key => $value) {
                    $value['goal_mission_id'] = $id;
                    GoalMissionService::updateAvg($value);
                }

                $this->data_result['DATA']['id'] = $id;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }
    }