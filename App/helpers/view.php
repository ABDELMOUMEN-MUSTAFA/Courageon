<?php

// Load view helper
function view($view, $data = [], $status = 200)
{
    // check for view file
    if (file_exists('../App/views/' . $view . '.php')) {
        http_response_code($status);
        extract($data);

        // require that view
        require_once "../App/views/" . $view . ".php";
    } else {
        http_response_code(404);
        require_once "../App/views/errors/page_404.php";
    }
    exit;
}
