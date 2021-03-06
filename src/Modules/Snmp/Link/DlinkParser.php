<?php


namespace SwitcherCore\Modules\Snmp\Link;

use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class DlinkParser extends AbstractModule
{
    private $indexesPort = [];
    protected function formate() {
          $link_status = $this->getResponseByName('dlink.PortInfoLinkStatus');
          $nway_status = $this->getResponseByName('dlink.PortInfoNwayStatus');
          $link_state = $this->getResponseByName('dlink.PortCtrlPortAdminState');
          $nway_state = $this->getResponseByName('dlink.PortCtrlPortNwayState');
          $addr_learning = $this->getResponseByName('dlink.PortCtrlAddressLearning');
          $description = $this->getResponseByName('if.Alias');
          $medium_type = $this->getResponseByName('dlink.PortInfoMediumType');
          $types = $this->getResponseByName('if.Type');

          if($link_state->error()) {
              throw new \Exception($link_state->error());
          }
          if($types->error()) {
              throw new \Exception($types->error());
          }
          if($medium_type->error()) {
              throw new \Exception($medium_type->error());
          }
          if($link_status->error()) {
              throw new \Exception($link_status->error());
          }
          if($nway_status->error()) {
              throw new \Exception($nway_status->error());
          }
          if($nway_state->error()) {
              throw new \Exception($nway_state->error());
          }
          if($description->error()) {
              throw new \Exception($description->error());
          }
          if($addr_learning->error()) {
              throw new \Exception($addr_learning->error());
          }

          $indexMediumType = [];
          foreach ($medium_type->fetchAll() as $d) {
              $indexMediumType[Helper::getIndexByOid($d->getOid())] = $d->getParsedValue();
          }

          $response=[];
          foreach ($link_status->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid(),1);
              $type = $indexMediumType[Helper::getIndexByOid($d->getOid())];
              $response["{$port}-{$type}"]['port'] = $port;
              $response["{$port}-{$type}"]['medium_type'] = $type;
              $response["{$port}-{$type}"]['type'] = null;
              $response["{$port}-{$type}"]['last_change'] = null;
              $response["{$port}-{$type}"]['connector_present'] = null;
              $response["{$port}-{$type}"]['oper_status'] = $d->getParsedValue();
              $response["{$port}-{$type}"]['description'] = "";
              $response["{$port}-{$type}"]['admin_state'] = "";
              $response["{$port}-{$type}"]['nway_status'] = "";
              $response["{$port}-{$type}"]['address_learning'] = "";
          }

        foreach ($nway_state->fetchAll() as $d) {
            $port = Helper::getIndexByOid($d->getOid(), 1);
            $type = $indexMediumType[Helper::getIndexByOid($d->getOid())];
                $response["{$port}-{$type}"]['admin_state'] = $d->getParsedValue();
        }

          foreach ($link_state->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid(),1);
              $type = $indexMediumType[Helper::getIndexByOid($d->getOid())];
              if($d->getParsedValue() == 'Disabled') {
                  $response["{$port}-{$type}"]['admin_state'] = $d->getParsedValue();
              }
          }

          foreach ($nway_status->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid(),1);
              $type = $indexMediumType[Helper::getIndexByOid($d->getOid())];
              $response["{$port}-{$type}"]['nway_status'] =  $d->getParsedValue();
          }
          foreach ($addr_learning->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid(),1);
              $type = $indexMediumType[Helper::getIndexByOid($d->getOid())];
              $response["{$port}-{$type}"]['address_learning'] =  $d->getParsedValue();
          }

        foreach ($description->fetchAll() as $d) {
            $port = Helper::getIndexByOid($d->getOid());
            if(isset($response["{$port}-Cooper"])) $response["{$port}-Cooper"]['description'] =   $d->getValue();
            if(isset($response["{$port}-Fiber"])) $response["{$port}-Fiber"]['description'] =   $d->getValue();
        }
        foreach ($types->fetchAll() as $d) {
            $port = Helper::getIndexByOid($d->getOid());
            if(isset($this->obj->model->getExtra()['ge_ports']) && in_array($port, $this->obj->model->getExtra()['ge_ports'])) {
                $d->setParsed('GE');
            }
            if(isset($response["{$port}-Cooper"])) $response["{$port}-Cooper"]['type'] =   $d->getParsedValue();
            if(isset($response["{$port}-Fiber"])) $response["{$port}-Fiber"]['type'] =   $d->getParsedValue();
        }
          return $response;
    }
    function getPretty()
    {
        return $this->formate();
    }
    function getPrettyFiltered($filter = [])
    {
        Helper::prepareFilter($filter);
        $response = $this->formate();

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
        $prepared = [
            \SnmpWrapper\Oid::init($this->obj->oidCollector->getOidByName('dlink.PortInfoLinkStatus')->getOid()) ,
            \SnmpWrapper\Oid::init($this->obj->oidCollector->getOidByName('dlink.PortInfoMediumType')->getOid(), true) ,
            \SnmpWrapper\Oid::init($this->obj->oidCollector->getOidByName('dlink.PortInfoNwayStatus')->getOid()) ,
            \SnmpWrapper\Oid::init($this->obj->oidCollector->getOidByName('dlink.PortCtrlPortAdminState')->getOid()) ,
            \SnmpWrapper\Oid::init($this->obj->oidCollector->getOidByName('dlink.PortCtrlPortNwayState')->getOid()) ,
            \SnmpWrapper\Oid::init($this->obj->oidCollector->getOidByName('dlink.PortCtrlAddressLearning')->getOid()) ,
            \SnmpWrapper\Oid::init($this->obj->oidCollector->getOidByName('if.Alias')->getOid()) ,
            \SnmpWrapper\Oid::init($this->obj->oidCollector->getOidByName('if.Type')->getOid(), true) ,
        ];
        $this->response = $this->formatResponse($this->obj->walker->walkBulk($prepared));
        return $this;
    }
}


