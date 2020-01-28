<?php


namespace SwitcherCore\Modules\CData;



use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

abstract class AbstractModuleCData extends AbstractModule
{
    private $indexesPort = [];

    function getIndexes($ethernetOnly = true) {
        $indexes = [];
        if($this->indexesPort) {
            return $this->indexesPort;
        }
        $response = $this->formatResponse($this->obj->walker->walk([
            Oid::init($this->obj->oidCollector->getOidByName('if.Descr')->getOid()),
        ]));
        foreach ($response['if.Descr']->fetchAll() as $resp) {
            $this->indexesPort[Helper::getIndexByOid($resp->getOid())] = Helper::portDisplayToIndex($resp->getValue());
        }
        $this->indexesPort = $indexes;
        return $indexes;
    }

    /**
     * Преобразовует полученный snmp-индекс порта(ОНУ) в индекс системы или читабельный 
     *
     * @param $index
     * @param bool $display_format
     * @return string
     */
    function decodeOnuPort($index, $display_format = false) {
        $port_num = str_split(decbin($index));
        $port = "3000" .
            sprintf("%'.02d", bindec(join(array_slice($port_num, 13, 4))) - 6) .
            sprintf("%'.03d", bindec(join(array_slice($port_num, 17, 8))))
        ;
        if($display_format) {
            return \SwitcherCore\Modules\Helper::portIndexToDisplay($port, true);
        }
        return $port;
    }
    static function encodeOnuPort($port) {

    }
}