<?php

use App\Models\Formateur;
use App\Models\Etudiant;

class AjaxController
{
    private $formateurModel;
    private $etudiantModel;

    public function __construct()
    {
        $this->formateurModel = new Formateur;
        $this->etudiantModel = new Etudiant; 
    }

    public function checkEmail()
    {
        $request = new App\Libraries\Request;
        if($request->getMethod() === 'POST'){
            $isThisEmailNew  = true;

            if(!$request->post('email')){
                return App\Libraries\Response::json(null, 400, "The email is field is requied.");
            }

            if ($this->etudiantModel->whereEmail($request->post('email'))) {
                $isThisEmailNew = false;
            }

            if ($this->formateurModel->whereEmail($request->post('email'))) {
                $isThisEmailNew = false;
            }

            echo json_encode($isThisEmailNew);
            return;
        }

        return App\Libraries\Response::json(null, 405, "Method Not Allowed");
    }
}