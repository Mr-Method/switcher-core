<?php
require __DIR__ . "/../vendor/autoload.php";

use SwitcherCore\Config\Reader;
use SnmpWrapper\WrapperWorker;
use SnmpWrapper\Walker;

$reader = new  Reader(__DIR__ . "/../configs");
$model = SwitcherCore\Config\ModelCollector::init($reader);
$oids = SwitcherCore\Config\OidCollector::init($reader);
$wrapper = new  WrapperWorker("http://37.57.212.3:8080");
$walker =  (new  Walker($wrapper))
    ->useCache(false);

$switcher = new \SwitcherCore\Switcher\SnmpSwitcher($walker,$model,$oids);

$switcher->connect('10.50.124.132', 'kievsnmprw');

//
/**
Get information by port with filters
Array
(
    [0] => Array
    (
        [port] => 28
        [speed] => 0
        [type] => gigabitEthernet
        [last_change] => 0d 0h 0min 0sec
        [oper_status] => DOWN
        [admin_status] => ENABLED
    )
)

Get information for FDB
*/
//print_r(json_encode($switcher->getFDB(27)));
/*
 * Array
(
    [0] => Array
    (
        [port] => 27
        [vlan_id] => 453
        [mac] => 4C:CC:6A:D5:81:93
        [status] => LEARNED
    )

    [1] => Array
    (
        [port] => 27
        [vlan_id] => 453
        [mac] => B0:BE:76:1B:49:54
        [status] => LEARNED
    )
)

Get System info
*/
//print_r(json_encode($switcher->getSystemInfo()));
/*
 *  Array
(
    [descr] => D-Link DES-3028 Fast Ethernet Switch
    [uptime] => 11d 19h 13min 38sec
    [contact] =>
    [name] => Kiev-Borshchagovskij (493/5013)
    [location] => Zodchikh, 6a(9)
)

 *
 *
Get error information in default parser
*/
//print_r(json_encode($switcher->getErrors()));
/*
Array
(
    [0] => Array
    (
        [port] => 3
            [in_errors] => 66
            [out_errors] => 0
            [in_discards] => 0
            [out_discards] => 0
        )

)*/

//print_r(json_encode($switcher->getRmon(3)));
/*
[0] => Array
            (
                [ether_stats_oversize_pkts] => 0
                [port] => 3
                [ether_stats_collisions] => 0
                [ether_stats_drop_events] => 0
                [ether_stats_crc_align_errors] => 66
                [ether_stats_undersize_pkts] => 0
                [ether_stats_fragments] => 0
                [ether_stats_jabber] => 0
            )

)*/

//print_r(json_encode($switcher->getErrors(3)));
/*
 * Array
(
    [0] => Array
    (
        [port] => 3
        [in_errors] => 66
        [out_errors] => 0
        [in_discards] => 0
        [out_discards] => 0
    )

)
*/
//print_r(json_encode($switcher->getCounters(3)));
/*
Array
(
    [0] => Array
    (
            [hc_in_multicast_pkts] => 1849766
            [port] => 3
            [hc_out_octets] => 82896948093
            [hc_out_broadcast_pkts] => 207218038
            [hc_in_broadcast_pkts] => 255
            [hc_in_octets] => 5991600259
            [hc_out_multicast_pkts] => 390801
        )

)
*/
//print_r(json_encode($switcher->getPVID(3)));
/*
Array
(
    [0] => Array
    (
            [pvid] => 453
            [port] => 3
        )

)
*/
//print_r(json_encode($switcher->getVlans(430)));
/*
[
    {
        "name": "switches430",
        "id": 430,
        "ports": {
        "egress": [
            26
        ],
            "untagged": [],
            "forbidden": [],
            "tagged": [
            26
        ]
        }
    }
]*/
//print_r(json_encode($switcher->getVlansByPort(26)));
/*
[
    {
        "port": 26,
        "untagged": [],
        "tagged": [
            {
                "name": "switches430",
                "id": 430
            },
            {
                "name": "INTERNET",
                "id": 453
            }
        ],
        "egress": [
            {
                "name": "switches430",
                "id": 430
            },
            {
                "name": "INTERNET",
                "id": 453
            }
        ],
        "forbidden": []
    }
]
*/
print_r(json_encode($switcher->getCableDiag(27)));
/*
[
    {
        "port": 27,
        "pairs": [
            {
                "number": 1,
                "status": "OK",
                "length": 33
            },
            {
                "number": 2,
                "status": "OK",
                "length": 33
            },
            {
                "number": 3,
                "status": "OK",
                "length": 33
            },
            {
                "number": 4,
                "status": "OK",
                "length": 33
            }
        ]
    }
]
 *
 **/