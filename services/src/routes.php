<?php
// Routes

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//$app->get('/user/{id}', 'UserController:getUser');
$app->post('/login/', 'LoginController:authenticate');

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

$app->post('/mou/list/', 'MouController:getList');
$app->post('/mou/get/', 'MouController:getData');
$app->post('/mou/update/', 'MouController:updateData');

$app->post('/cooperative/list/', 'CooperativeController:getList');
$app->post('/cooperative/get/', 'CooperativeController:getData');
$app->post('/cooperative/update/', 'CooperativeController:updateData');

$app->post('/dairy-farming/list/', 'DairyFarmingController:getList');
$app->post('/dairy-farming/list/parent/', 'DairyFarmingController:getParentList');
$app->post('/dairy-farming/list/veterinary/', 'DairyFarmingController:getListForVeterinary');
$app->post('/dairy-farming/get/', 'DairyFarmingController:getData');
$app->post('/dairy-farming/update/', 'DairyFarmingController:updateData');

$app->post('/veterinary/list/main/', 'VeterinaryController:getMainList');
$app->post('/veterinary/get/', 'VeterinaryController:getData');
$app->post('/veterinary/update/', 'VeterinaryController:updateData');
$app->post('/veterinary/delete/', 'VeterinaryController:removeData');
$app->post('/veterinary/delete/detail/', 'VeterinaryController:removeDetailData');
$app->post('/veterinary/delete/item/', 'VeterinaryController:removeItemData');

$app->post('/production-factor/list/', 'ProductionFactorController:getList');
$app->post('/production-factor/get/', 'ProductionFactorController:getData');
$app->post('/production-factor/update/', 'ProductionFactorController:updateData');
$app->post('/production-factor/delete/', 'ProductionFactorController:removeData');

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


// Default action
$app->get('/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});
