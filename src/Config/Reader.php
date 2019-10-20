<?php
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 05.06.2019
 * Time: 16:33
 */

namespace SwitcherCore\Config;


use Config\Objects\Command;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\Objects\Oid;

class Reader
{
    public $configPath = "";

    /**
     * Reader constructor.
     * @param string $config_path
     */
    function __construct($config_path)
    {
        $this->configPath = $config_path;
    }

    /**
     * @return array
     * @throws \ErrorException
     */
    function readModels() {
        $files = array_filter(scandir($this->configPath), function ($elem) {

            if(preg_match('/^.*\.models\.(yml|yaml)$/', $elem)) {
                return true;
            } else {
                return false;
            }
        });
        $models = [];
        foreach ($files as $filename) {
            $parsedYaml = yaml_parse_file("{$this->configPath}/{$filename}");
            if(!$parsedYaml) {
                throw new \ErrorException("Error reading yaml configuration - {$this->configPath}/{$filename}");
            } elseif(!isset($parsedYaml['models'])) {
                throw new \ErrorException("Incorrect structure of {$this->configPath}/{$filename} - block model must be setted");
            }
            foreach ($parsedYaml['models'] as $model) {
                $models[] = Model::init($model);
            }
        }
        return $models;
    }

    /**
     * @return array
     * @throws \ErrorException
     */
    public function readGlobalOids() {
        return $this->readOids("{$this->configPath}/oids/global.oids.yml");
    }

    /**
     * @param string $path
     * @return array
     * @throws \ErrorException
     */
    private function readOids(string $path) {
            $data = yaml_parse_file($path);
            if (!$data) {
                throw new \ErrorException("Error reading config $path");
            }
            $list = [];
            foreach ($data as $oid) {
                $list[] = Oid::init($oid);
            }
            return $list;
    }
    /**
     * @param string $path
     * @return array
     * @throws \ErrorException
     */
    private function readAndParseCommands(string $path) {
            $data = yaml_parse_file($path);
            if (!$data) {
                throw new \ErrorException("Error reading config $path");
            }
            $list = [];
            foreach ($data['commands'] as $command) {
                $list[] = Command::init($command);
            }
            return $list;
    }

    /**
     * @param Model $model
     * @return array
     * @throws \ErrorException
     */
    function readEnterpriseOids(Model $model)   {
          $oids = [];
          foreach ($model->getOidsPatches() as $path) {
              $data =  $this->readOids("{$this->configPath}/{$path}");
              if(!$data) {
                  throw new \Exception("Oids in path $path is empty. Please, fix it.");
              }
              $oids = array_merge($oids, $data);
          }
          return $oids;
    }

    /**
     * @param Model $model
     * @return Command[]
     * @throws \ErrorException
     */
    function readCommands(Model $model) {
        $commands = [];
        foreach ($model->getCommandPatches() as $path) {
            $data =  $this->readAndParseCommands("{$this->configPath}/{$path}");
            if(!$data) {
                throw new \Exception("Commands in path $path is empty. Please, fix it.");
            }
            $commands = array_merge($commands, $data);
        }
        return $commands;
    }
}



