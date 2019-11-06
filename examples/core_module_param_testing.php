<?php
require __DIR__ . "/../vendor/autoload.php";

use SnmpWrapper\Walker;
use SnmpWrapper\WrapperWorker;
use SwitcherCore\Config\Reader;

$handle = fopen ("php://stdin","r");

$proxyConfig = new \SwitcherCore\Config\ProxyConfiguration(__DIR__ . '/../configs/proxies.yml');
$proxyConfig->setSearchedIP($argv[1]);

//Switcher core initialization
$walker =  (new  Walker(
        new  WrapperWorker($proxyConfig->getSnmpConfiguration()['address']))
    )->useCache(false)
    ->setIp($argv[1])
    ->setCommunity($argv[2]);
$core = (new \SwitcherCore\Switcher\Core(
    new  Reader(__DIR__ . "/../configs")
))->setWalker($walker)->init();

$inputs_list = $core->getNeedInputs();
if(in_array('telnet', $inputs_list)) {
    $telnet = (new \SwitcherCore\Switcher\Objects\TelnetLazyConnect($argv[1], 23))
        ->connectOverProxy($proxyConfig->getTelnetConfiguration()['address'])
        ->login($argv[3], $argv[4]);
    $core->setTelnet($telnet);
}

if(in_array('routeros_api', $inputs_list)) {
    $routerOS = (new \SwitcherCore\Switcher\Objects\RouterOsLazyConnect())
        ->setPort(55055)
        ->connect($argv[1], $argv[3], $argv[4]);
    $core->setRouterOsAPI($routerOS);
}

//Prepare modules list
$modules = $core->getModulesData();
usort($modules, function($a, $b) {return strcmp($a['name'], $b['name']);});


STEP_CHOOSING:
echo "Choose module:\n";
foreach ($modules as $num=>$module) {
    echo "{$num})\t{$module['name']}\n";
}
echo "\n";
echo "Module num: ";
$step_num = trim(fgets($handle));
if(!isset($modules[$step_num])) {
    echo "\nIncorrect step number\n\n\n";
    goto STEP_CHOOSING;
}
$module = $modules[$step_num];
echo "Start testing module {$module['name']} ({$module['class']})\n";

$params = [];
if($module['arguments']) {
    echo "Module has params, setup please\n";
    echo "Params: \n";
    foreach ($module['arguments'] as $argument) {
        ARGUMENT_INPUT:
        $star = $argument['required'] ? "*" : "";
        echo "   {$star}{$argument['name']}: ";
        $param = trim(fgets($handle));
        if(!$param && !$argument['required']) {
            continue;
        }
        if(!preg_match("/{$argument['pattern']}/", $param)) {
            echo "Incorrect param. Param has pattern: {$argument['pattern']}\n";
            goto ARGUMENT_INPUT;
        }  else {
            $params[$argument['name']] = $param;
        }
    }
}

echo "==========================RESPONSE===============================\n";
echo "\$parameters=json_decode('".json_encode($params)."', true);\n";
echo "\$core->action('{$module['name']}', \$parameters);\n\n";
echo json_encode($core->action($module['name'], $params), JSON_PRETTY_PRINT);
echo "\n==============================================================\n\n\n";

echo "Diagnostic finished!\n";
goto STEP_CHOOSING;