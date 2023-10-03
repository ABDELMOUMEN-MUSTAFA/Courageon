<?php

namespace App\Controllers;

use App\Models\Formation;
use App\Models\Formateur;
use App\Models\Stocked;

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

	public function index()
	{
		$data = [
			'formations' => $this->formationModel->getPopularCourses(),
			'categories' => $this->stockedModel->getPopularCategories(),
			'formateurs' => $this->formateurModel->getPopularFormateurs(),
			'totalFormations' => $this->formationModel->count(),
		];

		return view("home/index", $data);
	}
}
