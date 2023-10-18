<?php

/**
 * Model Subtitle
 */
namespace App\Models;

use App\Libraries\Database;

class Subtitle
{
	private $connect;

	public function __construct()
	{
		$this->connect = Database::getConnection();
	}

	public function where($selectColumns, $conditions)
	{
        $filters = array_keys($conditions);
        foreach($filters as $key => $column){
            $filters[$key] = "{$column} = :{$column}";
        }

		$query = $this->connect->prepare("
			SELECT 
                ".implode(',', $selectColumns)."
			FROM sous_titres
            JOIN langues USING (id_langue)
			WHERE ".implode(' AND ', $filters)
        );

		foreach ($conditions as $column => $value) $query->bindValue($column, $value);
        $query->execute();

		$subtitles = $query->fetchAll(\PDO::FETCH_OBJ);
		if ($query->rowCount() > 0) {
			return $subtitles;
		}
		return [];
	}

	public function find($id)
	{
		$query = $this->connect->prepare("
			SELECT 
				id_sous_titre,
				source,
				id_video,
				id_langue
			FROM sous_titres
			WHERE id_sous_titre = :id_sous_titre
		");

		$query->bindValue(':id_sous_titre', $id);
		$query->execute();

		$sous_titre = $query->fetch(\PDO::FETCH_OBJ);
		if ($query->rowCount() > 0) {
			return $sous_titre;
		}
		return false;
	}

    public function create($subtitle)
	{
		$query = $this->connect->prepare("
			INSERT INTO sous_titres VALUES (DEFAULT, :source, :id_video, :id_langue)
		");
		$query->execute([
            'source' => $subtitle['source'], 
            'id_video' => $subtitle['id_video'],
            'id_langue' => $subtitle['id_langue']
        ]);

		$lastInsertId = $this->connect->lastInsertId();
		if ($lastInsertId > 0) {
			return $lastInsertId;
		}
		return false;
	}

	public function delete($levelID)
	{
		$query = $this->connect->prepare("
			DELETE FROM sous_titres 
			WHERE id_sous_titre = :id
		");

		$query->execute(['id' => $levelID]);
		if ($query->rowCount() > 0) {
			return true;
		}
		return false;
	}
}
