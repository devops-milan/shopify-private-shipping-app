<?php
function array_under_reset($array, $key, $type=1){
	if (is_array($array)){
		$tmp = array();
		foreach ($array as $v) {
			if ($type === 1){
				$tmp[$v[$key]] = $v;
			}elseif($type === 2){
				$tmp[$v[$key]][] = $v;
			}
		}
		return $tmp;
	}else{
		return $array;
	}
}

function ajax_output($data = array(), $content_type = 'json') {
    if ($content_type == 'html') {
        header('Content-Type: text/html; charset=utf-8');
        echo $data;
    } else {
        header('Content-type: application/json');
        echo json_encode($data);
    }
    exit;
}