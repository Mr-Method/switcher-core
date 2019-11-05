<?php


namespace SwitcherCore\Modules\Snmp\CableDiag;

use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Modules\Helper;


class DlinkDgs1100Parser extends DlinkParser
{
    public function run($filter = [])
    {
        Helper::prepareFilter($filter) ;
        $ports_list = $this->getPortList($filter);

        $ports_diag_result = [];
        $oidsDiag = [
            $this->obj->oidCollector->getOidByName('dlink.cableDiagPair1TestResult')->getOid(),
            $this->obj->oidCollector->getOidByName('dlink.cableDiagPair1FaultDistance')->getOid(),
            $this->obj->oidCollector->getOidByName('dlink.cableDiagPair2TestResult')->getOid(),
            $this->obj->oidCollector->getOidByName('dlink.cableDiagPair2FaultDistance')->getOid(),
            $this->obj->oidCollector->getOidByName('dlink.cableDiagPair3TestResult')->getOid(),
            $this->obj->oidCollector->getOidByName('dlink.cableDiagPair3FaultDistance')->getOid(),
            $this->obj->oidCollector->getOidByName('dlink.cableDiagPair4TestResult')->getOid(),
            $this->obj->oidCollector->getOidByName('dlink.cableDiagPair4FaultDistance')->getOid(),
        ];
        foreach ($ports_list as $port=>$count_pairs) {
            $triggerResult  = $this->formatResponse($this->obj->walker->set(
                $this->obj->oidCollector->getOidByName('dlink.cableDiagTriggerIndex')->getOid(),
                PoollerRequest::TypeIntegerValue,
                $port
            ))['dlink.cableDiagTriggerIndex']->fetchOne();

            $this->response = $this->formatResponse($this->obj->walker->get($oidsDiag));
            $ports_diag_result[] = [
                'port' => $port,
                'pairs' => [
                    [
                       'number' => 1,
                       'status' => $this->getResponseByName('dlink.cableDiagPair1TestResult')->fetchOne()->getParsedValue(),
                       'length' => $this->getResponseByName('dlink.cableDiagPair1FaultDistance')->fetchOne()->getValue(),
                    ],
                    [
                       'number' => 2,
                       'status' => $this->getResponseByName('dlink.cableDiagPair2TestResult')->fetchOne()->getParsedValue(),
                       'length' => $this->getResponseByName('dlink.cableDiagPair2FaultDistance')->fetchOne()->getValue(),
                    ],
                    [
                       'number' => 3,
                       'status' => $this->getResponseByName('dlink.cableDiagPair3TestResult')->fetchOne()->getParsedValue(),
                       'length' => $this->getResponseByName('dlink.cableDiagPair3FaultDistance')->fetchOne()->getValue(),
                    ],
                    [
                       'number' => 4,
                       'status' => $this->getResponseByName('dlink.cableDiagPair4TestResult')->fetchOne()->getParsedValue(),
                       'length' => $this->getResponseByName('dlink.cableDiagPair4FaultDistance')->fetchOne()->getValue(),
                    ],
                ]
            ];
        }
        $this->formatedResponse = $ports_diag_result;

        return $this;
    }

    /**
     * @return array
     */
    public function getPretty()
    {
       return $this->formatedResponse;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->formatedResponse;
    }
}