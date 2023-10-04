<?php

namespace App\Seeders;

use App\Models\Categorie;
use App\Models\Stocked;

class CategorieSeeder extends Seed {

	private function definition()
	{
       $data = [
		    'nom_categorie' => $this->faker->jobTitle(),
		    'image' => $this->getRandomImage(800, 533, 'categories'),
		];

		$categorieModel = new Categorie;
		return $categorieModel->create($data);
	}

	public function seed($records = 10)
	{
		$categories = [];

		for($i = $records; $i > 0 ;$i--){
			array_push($categories, $this->definition());

			if($i % 5 === 0) {
				sleep(1);
			}
		}

		return $categories;
	}
}