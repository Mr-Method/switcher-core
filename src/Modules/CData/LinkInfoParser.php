<?php


namespace SwitcherCore\Modules\Snmp\Link;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\AbstractModuleCData;
use SwitcherCore\Modules\Helper;

;

class LinkInfoParser extends AbstractModuleCData
{
    private $indexesPort = [];
    protected function formate() {
          $snmp_high_speed = !$this->response['if.HighSpeed']->error() ? $this->response['if.HighSpeed']->fetchAll() : [];
          $snmp_type = !$this->response['if.Type']->error() ? $this->response['if.Type']->fetchAll() : [];
          $snmp_last_change = !$this->response['if.LastChange']->error() ? $this->response['if.LastChange']->fetchAll() : [];
          $snmp_oper_status = !$this->response['if.OperStatus']->error() ? $this->response['if.OperStatus']->fetchAll() : [];
          $snmp_admin_status = !$this->response['if.AdminStatus']->error() ? $this->response['if.AdminStatus']->fetchAll() : [];
          $descr = !$this->response['if.Alias']->error() ? $this->response['if.Alias']->fetchAll() : [];
          $ports = !$this->response['if.Descr']->error() ? $this->response['if.Descr']->fetchAll() : [];

          $indexes = [];
          foreach ($this->getIndexes() as $index=>$port) {
              $indexes[$index]['name'] = $port;
              $indexes[$index]['medium_type'] = null;
              $indexes[$index]['description'] = null;
              $indexes[$index]['oper_status'] = null;
              $indexes[$index]['nway_status'] = null;
              $indexes[$index]['admin_state'] = null;
              $indexes[$index]['admin_state'] = null;
              $indexes[$index]['last_change'] = null;
              $indexes[$index]['id'] = $port;
              $indexes[$index]['extra'] = [];
          }

          foreach ($ports as $index) {
              $indexes[Helper::getIndexByOid($index->getOid())]['name'] =  $index->getValue();
          }
          foreach ($snmp_high_speed as $index) {
              $indexes[Helper::getIndexByOid($index->getOid())]['nway_status'] =  $index->getValue();
          }
          foreach ($descr as $index) {
              $indexes[Helper::getIndexByOid($index->getOid())]['description'] =  $index->getValue();
          }

          foreach ($snmp_oper_status as $index) {
              $indexes[Helper::getIndexByOid($index->getOid())]['oper_status'] =  $index->getParsedValue();
          }
          foreach ($snmp_admin_status as $index) {
                $indexes[Helper::getIndexByOid($index->getOid())]['admin_state'] =  $index->getParsedValue();
          }
          foreach ($snmp_type as $index) {
              $indexes[Helper::getIndexByOid($index->getOid())]['type'] =  $index->getParsedValue();
          }
          foreach ($snmp_last_change as $index) {
              $indexes[Helper::getIndexByOid($index->getOid())]['last_change'] =  $index->getValueAsTimeTicks();
          }
          return $indexes;
    }
    function getPretty()
    {
        return $this->formate();
    }
    function getPrettyFiltered($filter = [])
    {
        Helper::prepareFilter($filter);
        $response = $this->formate();
        if($filter['type']) {
            $types = explode(",", $filter['type']);
            foreach ($response as $num => $resp) {
                if(!isset($resp['type']))  {
                    unset($response[$num]);
                    continue;
                }
                if (!in_array($resp['type'], $types)) {
                    unset($response[$num]);
                }
            }
        }
        if($filter['port']) {
            foreach ($response as $num=>$resp) {
                if(!isset($resp['port']))  {
                    unset($response[$num]);
                    continue;
                }
                if($filter['port'] != $resp['port']) {
                    unset($response[$num]);
                }
            }
        }
        return array_values($response);
    }
    public function run($filter = [])
    {
        $indexes = [];
        foreach ($this->getIndexes() as $index=>$port) {
         $indexes[$port] = $index;
        }

        $data = [
            $this->obj->oidCollector->getOidByName('if.HighSpeed')->getOid() ,
            $this->obj->oidCollector->getOidByName('if.Name')->getOid(),
            $this->obj->oidCollector->getOidByName('if.Type')->getOid(),
            $this->obj->oidCollector->getOidByName('if.LastChange')->getOid(),
            $this->obj->oidCollector->getOidByName('if.OperStatus')->getOid(),
            $this->obj->oidCollector->getOidByName('if.AdminStatus')->getOid(),
            $this->obj->oidCollector->getOidByName('if.Descr')->getOid(),
            $this->obj->oidCollector->getOidByName('if.Alias')->getOid(),
        ];

        if ($filter['port']) {
            foreach ($data as $num=>$d) {
                $data[$num] .= ".{$indexes[$filter['port']]}";
            }
        }
        $oidObjects = [];
        foreach ($data as $oid) {
            $oidObjects[] = Oid::init($oid);
        }
        $this->response = $this->formatResponse($this->obj->walker->walk($oidObjects));
        return $this;
    }
}

