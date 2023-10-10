<?php

namespace App\Controllers;

use App\Seeders\{
    CategorieSeeder,
    EtudiantSeeder,
    FormateurSeeder,
    FormationSeeder,
    InscriptionSeeder,
    VideoSeeder
};


class FakerController {

	public function index($request)
	{
		if($request->getMethod() === 'GET'){
			return view('faker/index');
		}
		
		if($request->getMethod() === 'POST'){
			$data = $this->_generate();
			return \App\Libraries\Response::json($data);
		}

		return \App\Libraries\Response::json(null, 405, "Method Not Allowed");
	}

	private function _generate()
	{
		$numberRecords = 10;
		
		// Create Categories
		$categorie = new CategorieSeeder;
		$categorieIDs = $categorie->seed($numberRecords);

		// Create Etudiants
		$etudiant = new EtudiantSeeder;
		$etudiantIDs = $etudiant->seed($numberRecords);

		// Create Formateurs
		$formateur = new FormateurSeeder;
		$formateurIDs = $formateur->seed($numberRecords);

		// Create Formations
		// custom formateur IDs.
		// $formateurIDs = ['FOR1', 'FOR2', 'FOR3', 'FOR4', 'FOR5', 'FOR6', 'FOR7', 'FOR8', 'FOR9'];
		$formation = new FormationSeeder;
		$formationIDs = $formation->seed($formateurIDs);

		// Create Inscription
		// Exemple: 
		// 		$formateurIDs = ['FOR1', 'FOR2', 'FOR3', ...];
		// 		$etudiantIDs = ['ETU1', 'ETU2', 'ETU3', ...];
		// 		$formationIDs = ['idFormation1' => 'idFormateur1', 'idFormation2' => 'idFormateur2', ...];

		$inscription = new InscriptionSeeder;
		$inscriptionIDs = $inscription->seed($etudiantIDs, $formationIDs);

		// Create Video
		$video = new VideoSeeder;
		$videoIDs = $video->seed($formationIDs);

		$data = [
			'categories' => count($categorieIDs),
			'etudiants' => count($etudiantIDs),
			'formateurs' => count($formateurIDs),
			'formations' => count($formationIDs),
			'inscriptions' => count($inscriptionIDs),
			'videos' => count($videoIDs),
		];

		return $data;
	}

}
