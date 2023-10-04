<?php

/**
 * Model Langue
 */
namespace App\Models;

use App\Libraries\Database;

class Langue
{
	private $connect;

	public function __construct()
	{
		$this->connect = Database::getConnection();
	}

	public function find($id)
	{
		$query = $this->connect->prepare("
			SELECT * 
			FROM langues 
			WHERE id_langue=:id
		");

		$query->bindParam(':id', $id);
		$query->execute();
		$langue = $query->fetch(\PDO::FETCH_OBJ);
		if ($query->rowCount() > 0) {
			return $langue;
		}
		return false;
	}

	public function all()
	{
		$query = $this->connect->prepare("
			SELECT * 
			FROM langues
		");

		$query->execute();
		$langues = $query->fetchAll(\PDO::FETCH_OBJ);
		if ($query->rowCount() > 0) {
			return $langues;
		}
		return [];
	}

	public function create($langue)
	{
		$query = $this->connect->prepare("
			INSERT INTO langues VALUES (DEFAULT, :nom)
		");
		$query->execute(['nom' => $langue]);
		$lastInsertId = $this->connect->lastInsertId();
		if ($lastInsertId > 0) {
			return $lastInsertId;
		}
		return false;
	}

	public function delete($langueID)
	{
		$query = $this->connect->prepare("
			DELETE FROM langues 
			WHERE id_langue = :id
		");
		$query->execute(['id' => $langueID]);
		if ($query->rowCount() > 0) {
			return true;
		}
		return false;
	}

    public function edit($data)
	{
		$query = $this->connect->prepare("
			UPDATE langues 
			SET nom = :nom_langue
			WHERE id_langue = :id_langue
		");
		$query->execute(['id_langue' => $data->langueID, 'nom_langue' => $data->NouveauNom]);
		if ($query->rowCount() > 0) {
			return true;
		}
		return false;
	}
}
