<?php

/**
 * File Description
 *
 * @author C. Moller <xavier.tnc@gmail.com> - 05 Jun 2014
 *
 * Licensed under the MIT license. Please see LICENSE for more information.
 *
 */

$flash_bag = array(
    'somedata' => array('a','b','c' => array('A','B'))
);

function setDeep($key, $value)
{
    global $flash_bag;

    if(strpos($key, '.') === false)
    {
        $flash_bag[$key] = $value;
    }
    else
    {
        $current = &$flash_bag;

        foreach(explode('.', $key) as $key)
        {
            $current = &$current[$key];
        }

        $current = $value;
    }
}

$start_memory = memory_get_usage();

for($i=0; $i < 6; $i++)
{
    $test_array['alert'.$i] = array(

            'danger' => array(
                'Error'.$i.'a',
                'Error'.$i.'b'
            ),

            'warning' => array(
                'Warning'.$i.'a',
                'Warning'.$i.'b'
            ),

            'info' => array(),

            'success' => array(
                'Happy'.$i.'a',
                'Happy'.$i.'b'
            ),
    );
}

$array_memory = memory_get_usage() - $start_memory;

echo '<h1>Array GET + HAS Test</h1><hr><br>';

var_dump($test_array);

echo '<br>';

echo 'ARRAY MEMORY USAGE: ' . $array_memory;

echo '<br>';

echo 'Array Get "alert1.warning" : ', print_r(array_get($test_array, 'alert1.warning'), true), '<br>';
echo 'Array Get "alert2.info" : ', print_r(array_get($test_array, 'alert2.info'), true), '<br>';
echo 'Array Get "alert3.success" : ', print_r(array_get($test_array, 'alert3.success'), true), '<br>';
echo 'Array Get "alert3.fake" : ', print_r(array_get($test_array, 'alert3.fake'), true), '<br>';

echo '<br>';

$alert5 = &array_get($test_array, 'alert5');

echo 'Array Get "alert5" : ', print_r($alert5, true), '<br>';

$alert5['fake'] = array('Fake1', 'Fake2');

echo 'Alert5 Updated: ', print_r($alert5, true), '<br>';

echo '<br>';

echo 'test_array(alert5): ', print_r($test_array['alert5'], true), '<br>';

echo '<br>';

echo 'Array Has "alert1.warning" : ', print_r(array_has($test_array, 'alert1.warning'), true), '<br>';
echo 'Array Has "alert2.info" : ', print_r(array_has($test_array, 'alert2.info'), true), '<br>';
echo 'Array Has "alert3.fake" : ', print_r(array_has($test_array, 'alert3.fake'), true), '<br>';

echo '<br>';

echo 'Array Get "alert5.success" : ', print_r(array_get($test_array, 'alert5.success'), true), '<br>';

echo '<br>';

var_dump($flash_bag);

echo '<br>';

setDeep('somedata.0', array('B','F','F'));

setDeep('somedata.c', array('S','E','N'));

setDeep('brother.from.other.mother', array('B','E','S','T'));

echo '<br>';

print_r($flash_bag);

die('THE END');
