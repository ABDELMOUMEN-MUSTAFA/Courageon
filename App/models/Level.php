<?php

/**
 * Model Level
 */
namespace App\Models;

use App\Libraries\Database;

class Level
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
			FROM niveaux 
			WHERE id_niveau=:id
		");

		$query->bindParam(':id', $id);
		$query->execute();
		$level = $query->fetch(\PDO::FETCH_OBJ);
		if ($query->rowCount() > 0) {
			return $level;
		}
		return false;
	}

	public function all()
	{
		$query = $this->connect->prepare("
			SELECT * 
			FROM niveaux
		");

		$query->execute();
		$levels = $query->fetchAll(\PDO::FETCH_OBJ);
		if ($query->rowCount() > 0) {
			return $levels;
		}
		return [];
	}

    public function create($level)
	{
		$query = $this->connect->prepare("
			INSERT INTO niveaux VALUES (DEFAULT, :nom, :icon)
		");
		$query->execute(['nom' => $level->nom, 'icon' => $level->icon]);
		$lastInsertId = $this->connect->lastInsertId();
		if ($lastInsertId > 0) {
			return $lastInsertId;
		}
		return false;
	}

	public function delete($levelID)
	{
		$query = $this->connect->prepare("
			DELETE FROM niveaux 
			WHERE id_niveau = :id
		");
		$query->execute(['id' => $levelID]);
		if ($query->rowCount() > 0) {
			return true;
		}
		return false;
	}

    public function edit($data)
	{
		$query = $this->connect->prepare("
			UPDATE niveaux 
			SET nom = :nom_niveau
			WHERE id_niveau = :id_niveau
		");
		$query->execute(['id_niveau' => $data->levelID, 'nom_niveau' => $data->NouveauNom]);
		if ($query->rowCount() > 0) {
			return true;
		}
		return false;
	}
}
