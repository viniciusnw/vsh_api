<?php
function set_md5_pass($pass)
{
	return md5($pass . PASS_KEY);
}