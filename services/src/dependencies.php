<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    $logger->pushHandler(new Monolog\Handler\RotatingFileHandler($settings['path'], $settings['maxFiles'], $settings['level']));
    return $logger;
};

$container['db'] = function ($c) {
    $settings = $c->get('settings')['db'];
    $capsule = new Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($settings);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    return $capsule;
};

$container['LoginController'] = function ($c) {
    return new \App\Controller\LoginController($c->get('logger'), $c->get('db'));
};

$container['PageController'] = function ($c) {
    return new \App\Controller\PageController($c->get('logger'), $c->get('db'));
};

$container['MenuController'] = function ($c) {
    return new \App\Controller\MenuController($c->get('logger'), $c->get('db'));
};

$container['AccountPermissionController'] = function ($c) {
    return new \App\Controller\AccountPermissionController($c->get('logger'), $c->get('db'));
};

$container['MasterGoalController'] = function ($c) {
    return new \App\Controller\MasterGoalController($c->get('logger'), $c->get('db'));
};

$container['GoalMissionController'] = function ($c) {
    return new \App\Controller\GoalMissionController($c->get('logger'), $c->get('db'));
};

$container['MouController'] = function ($c) {
    return new \App\Controller\MouController($c->get('logger'), $c->get('db'));
};

$container['CooperativeController'] = function ($c) {
    return new \App\Controller\CooperativeController($c->get('logger'), $c->get('db'));
};

$container['DairyFarmingController'] = function ($c) {
    return new \App\Controller\DairyFarmingController($c->get('logger'), $c->get('db'));
};

$container['VeterinaryController'] = function ($c) {
    return new \App\Controller\VeterinaryController($c->get('logger'), $c->get('db'));
};

$container['ProductionFactorController'] = function ($c) {
    return new \App\Controller\ProductionFactorController($c->get('logger'), $c->get('db'));
};

$container['FoodController'] = function ($c) {
    return new \App\Controller\FoodController($c->get('logger'), $c->get('db'));
};

$container['CowFoodController'] = function ($c) {
    return new \App\Controller\CowFoodController($c->get('logger'), $c->get('db'));
};

$container['TrainingController'] = function ($c) {
    return new \App\Controller\TrainingController($c->get('logger'), $c->get('db'));
};

$container['InseminationController'] = function ($c) {
    return new \App\Controller\InseminationController($c->get('logger'), $c->get('db'));
};

$container['MineralController'] = function ($c) {
    return new \App\Controller\MineralController($c->get('logger'), $c->get('db'));
};

$container['SpermSaleController'] = function ($c) {
    return new \App\Controller\SpermSaleController($c->get('logger'), $c->get('db'));
};

$container['MaterialController'] = function ($c) {
    return new \App\Controller\MaterialController($c->get('logger'), $c->get('db'));
};

$container['CowBreedController'] = function ($c) {
    return new \App\Controller\CowBreedController($c->get('logger'), $c->get('db'));
};

$container['TrainingCowBreedController'] = function ($c) {
    return new \App\Controller\TrainingCowBreedController($c->get('logger'), $c->get('db'));
};

$container['SpermController'] = function ($c) {
    return new \App\Controller\SpermController($c->get('logger'), $c->get('db'));
};

$container['TravelController'] = function ($c) {
    return new \App\Controller\TravelController($c->get('logger'), $c->get('db'));
};

$container['CooperativeMilkController'] = function ($c) {
    return new \App\Controller\CooperativeMilkController($c->get('logger'), $c->get('db'));
};

$container['CowGroupController'] = function ($c) {
    return new \App\Controller\CowGroupController($c->get('logger'), $c->get('db'));
};