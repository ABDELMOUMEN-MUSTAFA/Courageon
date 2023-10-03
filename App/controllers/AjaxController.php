<?php

namespace App\Controllers;

use App\Libraries\Response;

class AjaxController
{
    public function __construct()
    {
        if(auth()){
            return Response::json(null, 400, "Bad Request");
        }
    }

    public function checkEmail()
    {
        $request = new \App\Libraries\Request;
        if($request->getMethod() === 'POST'){
            $isThisEmailNew  = true;

            if(!$request->post('email')){
                return Response::json(null, 400, "The email is field is requied.");
            }

            $etudiantModel = new \App\Models\Etudiant; 
            if ($etudiantModel->whereEmail($request->post('email'))) {
                $isThisEmailNew = false;
            }

            $formateurModel = new \App\Models\Formateur;
            if ($formateurModel->whereEmail($request->post('email'))) {
                $isThisEmailNew = false;
            }

            exit(json_encode($isThisEmailNew));
        }

        return Response::json(null, 405, "Method Not Allowed");
    }
}