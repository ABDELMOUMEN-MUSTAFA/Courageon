<?php

namespace App\Controllers;

use App\Models\{
    Formation,
    Formateur,
    Categorie
};

class HomeController
{
	private $formationModel;
	private $formateurModel;
	private $categorieModel;

	public function __construct()
	{
		$this->formationModel = new Formation;
		$this->formateurModel = new Formateur;
		$this->categorieModel = new Categorie;
	}

	public function index($request)
	{
		if($request->getMethod() !== 'GET'){
			return \App\Libraries\Response::json(null, 405, "Method Not Allowed");
		}

		$data = [
			'formations' => $this->formationModel->getPopularCourses(),
			'categories' => $this->categorieModel->getPopularCategories(),
			'formateurs' => $this->formateurModel->getPopularFormateurs(),
			'totalFormations' => $this->formationModel->count(),
		];

		return view("home/index", $data);
	}
}
