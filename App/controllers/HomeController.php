<?php

namespace App\Controllers;

use App\Models\{
    Formation,
    Formateur,
    Stocked
};

class HomeController
{
	private $formationModel;
	private $formateurModel;
	private $stockedModel;

	public function __construct()
	{
		$this->formationModel = new Formation;
		$this->formateurModel = new Formateur;
		$this->stockedModel = new Stocked;
	}

	public function index($request)
	{
		if($request->getMethod() !== 'GET'){
			return \App\Libraries\Response::json(null, 405, "Method Not Allowed");
		}

		$data = [
			'formations' => $this->formationModel->getPopularCourses(),
			'categories' => $this->stockedModel->getPopularCategories(),
			'formateurs' => $this->formateurModel->getPopularFormateurs(),
			'totalFormations' => $this->formationModel->count(),
		];

		return view("home/index", $data);
	}
}
