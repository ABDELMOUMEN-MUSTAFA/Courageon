<?php

// page redirect helper
function redirect($controller = '')
{
	ob_start();
	http_response_code(303);
	header('location: ' . URLROOT . '/' . $controller);
	ob_end_clean();
	exit;
}
