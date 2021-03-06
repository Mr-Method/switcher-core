<?php


namespace SwitcherCore\Modules;



use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Switcher\Objects\InputsStore;
use SwitcherCore\Switcher\Objects\ModuleStore;
use SwitcherCore\Switcher\Objects\WrappedResponse;

abstract class AbstractModule
{
    private $indexesPort = [];
    /**
     * @var WrappedResponse[]
     */
    protected $response;

    /**
     * @var InputsStore
     */
    protected $obj;

    /**
     * @var ModuleStore
     */
    protected $module;

    public function setInputsStore(InputsStore $obj) {
        $this->obj = $obj;
        return $this;
    }


    public function setModuleStore(ModuleStore $obj) {
        $this->module = $obj;
        return $this;
    }

    final public function start($params = []) {
        $this->run($params);
        return $this;
    }

    /**
     * @param array $params
     * @return self
     */
    public abstract function run($params = []);


    /**
     * @param array $filter
     * @return self
     */
    public function parse($filter = []) {

    }

    /**
     * @return array
     */
    public function getRaw() {
        return  $this->response;
    }

    /**
     * @return array
     */
    public abstract function getPretty();
    public abstract function getPrettyFiltered($filter = []);

    /**
     * @param PoollerResponse[] $response
     * @return WrappedResponse[]
     *
     * @throws \Exception
     */
    protected function formatResponse($response) {
        $formated = [];
        foreach ($response as $resp) {
            $oid = $this->obj->oidCollector->findOidById($resp->getOid());
            $formated[$oid->getName()] = WrappedResponse::init($resp, $oid->getValues());
        }

        return $formated;
    }
    function getIndexes($ethernetOnly = true) {
        $indexes = [];
        if($this->indexesPort) {
            return $this->indexesPort;
        }
        $response = $this->formatResponse($this->obj->walker->walk([
            Oid::init($this->obj->oidCollector->getOidByName('if.Index')->getOid()),
            Oid::init($this->obj->oidCollector->getOidByName('if.Type')->getOid()),
        ]));
        $types = [];
        foreach ($response['if.Type']->fetchAll() as $resp) {
            $types[Helper::getIndexByOid($resp->getOid())] = $resp->getParsedValue();
        }

        foreach ($response['if.Index']->fetchAll() as $resp) {
            if($ethernetOnly && in_array($types[Helper::getIndexByOid($resp->getOid())], ['FE','GE'])) {
                $indexes[Helper::getIndexByOid($resp->getOid())] = $resp->getValue();
            }
        }
        $this->indexesPort = $indexes;
        return $indexes;
    }
    /**
     * @param $name
     * @return WrappedResponse
     * @throws IncompleteResponseException
     */
    protected function getResponseByName($name) {
        if(!isset($this->response[$name])) {
            throw  new IncompleteResponseException("Response with oid $name not found");
        }
        return $this->response[$name];
    }

}