<?php


namespace SwitcherCore\Config;


use SwitcherCore\Config\Objects\Model;

class ModelCollector extends Collector
{
    /**
     * @var Model[]
     */
    protected $modelsDB;
    protected function read()
    {
        $this->modelsDB = $this->reader->readModels();
    }

    /**
     * @param string $descr
     * @param string $hardware
     * @param string $oidId
     * @return Model
     * @throws \Exception
     */
    function getModelByDetect($descr = "",  $oidId = "") {
        foreach ($this->modelsDB as $model) {
            if(!$model->detectByDescription($descr)) {
                continue;
            }
            if(!$model->detectByObjId($oidId)) {
                continue;
            }
            return $model;
        }
        throw new \Exception("Model not found by detects with parameters descr='{$descr}', oidId='{$oidId}'");
    }

}