<?php

require __DIR__ . "/../vendor/autoload.php";


/**Example port index:
Frame - 1
Slot - 2
Port - 24
Display = 1/2/24
Delim = 0/0/2/24
Index = 110224
 */
function decode_cdata($index, $display_format = false) {
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


function encode_cdata($port) {
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




$decoded = decode_cdata($argv[1]);

echo $decoded . "\n";
echo decode_cdata($argv[1], true) . "\n";
echo \SwitcherCore\Modules\Helper::portDisplayToIndex(decode_cdata($argv[1], true)) . "\n";


echo "Decode - pon0/0/1:1 to index = " . \SwitcherCore\Modules\Helper::portDisplayToIndex("pon0/0/1:1") . "\n";
echo "Decode - index 300001001 to display " . \SwitcherCore\Modules\Helper::portIndexToDisplay(300001001)  . "\n";