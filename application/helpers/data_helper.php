<?php
function clean_db_data_array($db_data)
{
	foreach ($db_data as $key => $data){
		if($data == '' || is_null($data)){
			unset($db_data[$key]);
		}
	}
	return $db_data;
}