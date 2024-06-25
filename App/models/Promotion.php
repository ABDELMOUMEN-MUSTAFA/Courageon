<?php

/**
 * Model Promotion
 */
namespace App\Models;

use App\Libraries\Database;

class Promotion
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
			FROM promotions
			WHERE id_promotion=:id
		");

		$query->bindParam(':id', $id);
		$query->execute();
		$promotion = $query->fetch(\PDO::FETCH_OBJ);
		if ($query->rowCount() > 0) {
			return $promotion;
		}
		return false;
	}

	public function all()
	{
		$query = $this->connect->prepare("
			SELECT * 
			FROM promotions
		");

		$query->execute();
		$promotions = $query->fetchAll(\PDO::FETCH_OBJ);
		if ($query->rowCount() > 0) {
			return $promotions;
		}
		return [];
	}

    public function create($promotion)
	{
		$query = $this->connect->prepare("
			INSERT INTO promotions VALUES (DEFAULT, :id_formation, :reduction, :date_start, :date_end)
		");

		$query->execute([
            'id_formation' => $promotion->id_formation, 
            'reduction' => $promotion->reduction,
            'date_start' => $promotion->date_start ?? null,
            'date_end' => $promotion->date_end
        ]);

		$lastInsertId = $this->connect->lastInsertId();
		if ($lastInsertId > 0) {
			return $lastInsertId;
		}
		return false;
	}

	public function delete($promotionID)
	{
		$query = $this->connect->prepare("
			DELETE FROM promotions 
			WHERE id_promotion = :id
		");
		$query->execute(['id' => $promotionID]);
		if ($query->rowCount() > 0) {
			return true;
		}
		return false;
	}

    public function update($data, $id)
	{
        $sql = "UPDATE promotions SET ";
        
        $updates = [];
        foreach ($data as $field => $value) {
            $updates[] = "$field = :$field";
        }
        
        $sql .= implode(', ', $updates);
        $sql .= " WHERE id_promotion = :id";
        
        $query = $this->connect->prepare($sql);

        foreach ($data as $field => $value) {
            $query->bindValue(":$field", $value);
        }

        $query->bindValue(':id', $id);
        $query->execute();

		return $query->rowCount() > 0;
	}
}
