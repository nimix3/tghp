<?php
// INI Parser Class File V.1 By ArioWeb Tech Media Inc.
// Author : NIMIX3 ( www.NIMIX3.com )
// Under GPL3 License
function write_ini_file($array, $file)
{
    $res = array();
    foreach($array as $key => $val)
    {
        if(is_array($val))
        {
            $res[] = "[$key]";
            foreach($val as $skey => $sval) $res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
        }
        else $res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
    }
    safefilerewrite($file, implode("\r\n", $res));
}

function safefilerewrite($fileName, $dataToSave)
{    if ($fp = fopen($fileName, 'w'))
    {
        $startTime = microtime();
        do
        {            $canWrite = flock($fp, LOCK_EX);
           if(!$canWrite) usleep(round(rand(0, 100)*1000));
        } while ((!$canWrite)and((microtime()-$startTime) < 1000));
        if ($canWrite)
        {            fwrite($fp, $dataToSave);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }
}

function read_ini_file($file)
{
return parse_ini_file($file);
}

function parse_ini_advanced($array) {
    $returnArray = array();
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            $e = explode(':', $key);
            if (!empty($e[1])) {
                $x = array();
                foreach ($e as $tk => $tv) {
                    $x[$tk] = trim($tv);
                }
                $x = array_reverse($x, true);
                foreach ($x as $k => $v) {
                    $c = $x[0];
                    if (empty($returnArray[$c])) {
                        $returnArray[$c] = array();
                    }
                    if (isset($returnArray[$x[1]])) {
                        $returnArray[$c] = array_merge($returnArray[$c], $returnArray[$x[1]]);
                    }
                    if ($k === 0) {
                        $returnArray[$c] = array_merge($returnArray[$c], $array[$key]);
                    }
                }
            } else {
                $returnArray[$key] = $array[$key];
            }
        }
    }
    return $returnArray;
}

function recursive_parse($array)
{
    $returnArray = array();
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = recursive_parse($value);
            }
            $x = explode('.', $key);
            if (!empty($x[1])) {
                $x = array_reverse($x, true);
                if (isset($returnArray[$key])) {
                    unset($returnArray[$key]);
                }
                if (!isset($returnArray[$x[0]])) {
                    $returnArray[$x[0]] = array();
                }
                $first = true;
                foreach ($x as $k => $v) {
                    if ($first === true) {
                        $b = $array[$key];
                        $first = false;
                    }
                    $b = array($v => $b);
                }
                $returnArray[$x[0]] = array_merge_recursive($returnArray[$x[0]], $b[$x[0]]);
            } else {
                $returnArray[$key] = $array[$key];
            }
        }
    }
    return $returnArray;
}
// example
//$array = parse_ini_file('test.ini', true);
//$array = recursive_parse(parse_ini_advanced($array));
?>