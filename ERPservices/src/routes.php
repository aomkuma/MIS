<?php
// Routes

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//$app->get('/user/{id}', 'UserController:getUser');
$app->post('/login/', 'LoginController:authenticate');
$app->post('/login/check-permission/', 'LoginController:checkPermission');

$app->post('/pages/', 'PageController:getPage');
$app->post('/pages/update/', 'PageController:updatePage');

$app->post('/menu/list/', 'MenuController:getMenuList');
$app->post('/menu/list/manage/', 'MenuController:getMenuListManage');
$app->post('/menu/get/', 'MenuController:getMenu');
$app->post('/menu/update/', 'MenuController:updateMenu');
$app->post('/menu/page/get/', 'MenuController:GetMenuPage');
$app->post('/menu/get/parent/', 'MenuController:GetMenuParent');

$app->post('/account-permission/get/', 'AccountPermissionController:getData');
$app->post('/account-permission/update/', 'AccountPermissionController:updateData');

$app->post('/master-goal/list/', 'MasterGoalController:getList');
$app->post('/master-goal/get/', 'MasterGoalController:getData');
$app->post('/master-goal/update/', 'MasterGoalController:updateData');

$app->post('/goal-mission/list/', 'GoalMissionController:getList');
$app->post('/goal-mission/get/', 'GoalMissionController:getData');
$app->post('/goal-mission/update/', 'GoalMissionController:updateData');
$app->post('/goal-mission/update/editable/', 'GoalMissionController:updateDataEditable');
$app->post('/goal-mission/update/approve/', 'GoalMissionController:updateDataApprove');

$app->post('/mou/list/', 'MouController:getList');
$app->post('/mou/get/', 'MouController:getData');
$app->post('/mou/update/', 'MouController:updateData');

$app->post('/cooperative/list/', 'CooperativeController:getList');
$app->post('/cooperative/list/region/', 'CooperativeController:getRegionList');
$app->post('/cooperative/get/', 'CooperativeController:getData');
$app->post('/cooperative/update/', 'CooperativeController:updateData');
$app->post('/cooperative/delete/', 'CooperativeController:deleteData');

$app->post('/dairy-farming/list/', 'DairyFarmingController:getList');
$app->post('/dairy-farming/list/parent/', 'DairyFarmingController:getParentList');
$app->post('/dairy-farming/list/veterinary/', 'DairyFarmingController:getListForVeterinary');
$app->post('/dairy-farming/get/', 'DairyFarmingController:getData');
$app->post('/dairy-farming/update/', 'DairyFarmingController:updateData');

$app->post('/veterinary/list/main/', 'VeterinaryController:getMainList');
$app->post('/veterinary/list/detail/', 'VeterinaryController:getDetailList');
$app->post('/veterinary/list/subdetail/', 'VeterinaryController:getSubDetailList');
$app->post('/veterinary/get/', 'VeterinaryController:getData');
$app->post('/veterinary/update/', 'VeterinaryController:updateData');
$app->post('/veterinary/delete/', 'VeterinaryController:removeData');
$app->post('/veterinary/delete/detail/', 'VeterinaryController:removeDetailData');
$app->post('/veterinary/delete/item/', 'VeterinaryController:removeItemData');
$app->post('/veterinary/report/', 'ReportController:exportVeterinaryExcel');
$app->post('/veterinary/update/approve/', 'VeterinaryController:updateDataApprove');
$app->post('/veterinary/list/approve/', 'VeterinaryController:loadDataApprove');

$app->post('/production-factor/list/', 'ProductionFactorController:getList');
$app->post('/production-factor/get/', 'ProductionFactorController:getData');
$app->post('/production-factor/update/', 'ProductionFactorController:updateData');
$app->post('/production-factor/delete/', 'ProductionFactorController:removeData');
$app->post('/production-factor/report/', 'ReportController:exportProductFactorReport');

$app->post('/food/list/', 'FoodController:getList');
$app->post('/food/get/', 'FoodController:getData');
$app->post('/food/update/', 'FoodController:updateData');
$app->post('/food/delete/', 'FoodController:removeData');

$app->post('/cow-food/list/', 'CowFoodController:getList');
$app->post('/cow-food/get/', 'CowFoodController:getData');
$app->post('/cow-food/update/', 'CowFoodController:updateData');
$app->post('/cow-food/delete/', 'CowFoodController:removeData');

$app->post('/training/list/', 'TrainingController:getList');
$app->post('/training/get/', 'TrainingController:getData');
$app->post('/training/update/', 'TrainingController:updateData');
$app->post('/training/delete/', 'TrainingController:removeData');


$app->post('/insemination/list/main/', 'InseminationController:getMainList'); 
$app->post('/insemination/get/', 'InseminationController:getData');
$app->post('/insemination/update/', 'InseminationController:updateData');
$app->post('/insemination/delete/detail/', 'InseminationController:removeDetailData');
$app->post('/insemination/report/', 'ReportController:exportInseminationExcel');
$app->post('/insemination/update/approve/', 'InseminationController:updateDataApprove');
$app->post('/insemination/list/approve/', 'InseminationController:loadDataApprove');

$app->post('/mineral/list/main/', 'MineralController:getMainList');
$app->post('/mineral/list/detail/', 'MineralController:getDetailList');
$app->post('/mineral/get/', 'MineralController:getData');
$app->post('/mineral/update/', 'MineralController:updateData');
$app->post('/mineral/delete/detail/', 'MineralController:removeDetailData');
$app->post('/mineral/report/', 'ReportController:exportMineralExcel');
$app->post('/mineral/update/approve/', 'MineralController:updateDataApprove');
$app->post('/mineral/list/approve/', 'MineralController:loadDataApprove');

$app->post('/sperm-sale/list/main/', 'SpermSaleController:getMainList');
$app->post('/sperm-sale/get/', 'SpermSaleController:getData');
$app->post('/sperm-sale/update/', 'SpermSaleController:updateData');
$app->post('/sperm-sale/delete/detail/', 'SpermSaleController:removeDetailData');
$app->post('/sperm-sale/update/approve/', 'SpermSaleController:updateDataApprove');
$app->post('/sperm-sale/list/approve/', 'SpermSaleController:loadDataApprove');

$app->post('/material/list/main/', 'MaterialController:getMainList');
$app->post('/material/get/', 'MaterialController:getData');
$app->post('/material/update/', 'MaterialController:updateData');
$app->post('/material/delete/detail/', 'MaterialController:removeDetailData');
$app->post('/material/update/approve/', 'MaterialController:updateDataApprove');
$app->post('/material/list/approve/', 'MaterialController:loadDataApprove');

$app->post('/cow-breed/list/main/', 'CowBreedController:getMainList');
$app->post('/cow-breed/get/', 'CowBreedController:getData');
$app->post('/cow-breed/update/', 'CowBreedController:updateData');
$app->post('/cow-breed/delete/detail/', 'CowBreedController:removeDetailData');
$app->post('/cow-breed/report/', 'ReportController:exportCowbreedExcel');
$app->post('/cow-breed/update/approve/', 'CowBreedController:updateDataApprove');
$app->post('/cow-breed/list/approve/', 'CowBreedController:loadDataApprove');

$app->post('/training-cowbreed/list/main/', 'TrainingCowBreedController:getMainList');
$app->post('/training-cowbreed/get/', 'TrainingCowBreedController:getData');
$app->post('/training-cowbreed/update/', 'TrainingCowBreedController:updateData');
$app->post('/training-cowbreed/delete/detail/','TrainingCowBreedController:removeDetailData');
$app->post('/training-cowbreed/report/', 'ReportController:exportTrainingcowbreedExcel');
$app->post('/training-cowbreed/update/approve/', 'TrainingCowBreedController:updateDataApprove');
$app->post('/training-cowbreed/list/approve/', 'TrainingCowBreedController:loadDataApprove');

$app->post('/sperm/list/main/', 'SpermController:getMainList');
$app->post('/sperm/list/detail/', 'SpermController:getDetailList');
$app->post('/sperm/get/', 'SpermController:getData');
$app->post('/sperm/update/', 'SpermController:updateData');
$app->post('/sperm/delete/detail/', 'SpermController:removeDetailData');
$app->post('/sperm/report/', 'ReportController:exportSpermExcel');
$app->post('/sperm/update/approve/', 'SpermController:updateDataApprove');
$app->post('/sperm/list/approve/', 'SpermController:loadDataApprove');

$app->post('/travel/list/main/', 'TravelController:getMainList');
$app->post('/travel/get/', 'TravelController:getData');
$app->post('/travel/update/', 'TravelController:updateData');
$app->post('/travel/delete/detail/', 'TravelController:removeDetailData');
$app->post('/travel/report/', 'ReportController:exportTravelExcel');
$app->post('/travel/update/approve/', 'TravelController:updateDataApprove');
$app->post('/travel/list/approve/', 'TravelController:loadDataApprove');

$app->post('/cooperative-milk/list/main/', 'CooperativeMilkController:getMainList');
$app->post('/cooperative-milk/get/', 'CooperativeMilkController:getData');
$app->post('/cooperative-milk/update/', 'CooperativeMilkController:updateData');
$app->post('/cooperative-milk/delete/detail/', 'CooperativeMilkController:removeDetailData');
$app->post('/cooperative-milk/update/approve/', 'CooperativeMilkController:updateDataApprove');
$app->post('/cooperative-milk/list/approve/', 'CooperativeMilkController:loadDataApprove');

$app->post('/cow-group/list/main/', 'CowGroupController:getMainList');
$app->post('/cow-group/get/', 'CowGroupController:getData');
$app->post('/cow-group/update/', 'CowGroupController:updateData');
$app->post('/cow-group/delete/detail/', 'CowGroupController:removeDetailData');
$app->post('/cow-group/report/', 'ReportController:exportCowgroupExcel');
$app->post('/cow-group/update/approve/', 'CowGroupController:updateDataApprove');
$app->post('/cow-group/list/approve/', 'CowGroupController:loadDataApprove');

$app->post('/monthreport/report/', 'MonthReportController:exportmonthreportExcel');
$app->post('/quarterreport/report/', 'QuarterReportController:exportquarterreportExcel');
$app->post('/annuallyreport/report/', 'AnnualReportController:exportannuallyreportExcel');

$app->post('/import-personal/', 'ImportPersonalController:import');
$app->post('/import-personal/list/main/', 'ImportPersonalController:getMainList');

$app->post('/personal/list/main/', 'PersonalController:getMainList');

$app->post('/chart/main/dbi/', 'ChartController:getDataDBI');
$app->post('/chart/main/ii/', 'ChartController:getDataII');
// Default action
$app->get('/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});
