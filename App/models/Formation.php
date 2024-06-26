<?php

/**
 *  Model Formation
 */

namespace App\Models;

use App\Libraries\Database;

use Carbon\Carbon;

class Formation
{
    private $connect;

    public function __construct()
    {
        $this->connect = Database::getConnection();
    }

    public function count($etat = 'public')
    {
        $query = $this->connect->prepare("
            SELECT 
                COUNT(*) AS total_formations 
            FROM formations
            WHERE etat = '{$etat}'
        ");

        $query->execute();
        $response = $query->fetch(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            return $response->total_formations;
        }
        return 0;
    }

    public function create($formation)
    {
        $query = $this->connect->prepare("
            INSERT INTO formations (id_niveau, id_formateur, id_categorie, nom, image, mass_horaire, prix, description, etat, id_langue, background_img, fichier_attache) VALUES (:id_niveau, :id_formateur, :id_categorie, :nom, :image, :mass_horaire, :prix, :description, :etat, :id_langue, :background_img, :fichier_attache)
        ");

        $query->bindValue(":id_niveau", $formation["id_niveau"]);
        $query->bindValue(":id_formateur", $formation["id_formateur"]);
        $query->bindValue(":id_categorie", $formation["id_categorie"]);
        $query->bindValue(":nom", $formation["nom"]);
        $query->bindValue(":image", $formation["image"]);
        $query->bindValue(":mass_horaire", $formation["masse_horaire"]);
        $query->bindValue(":prix", $formation["prix"]);
        $query->bindValue(":description", $formation["description"]);
        $query->bindValue(":etat", $formation["etat"]);
        $query->bindValue(":id_langue", $formation["id_langue"]);
        $query->bindValue(":background_img", $formation["background_img"] ?? null);
        $query->bindValue(":fichier_attache", $formation["fichier_attache"] ?? null);
        $query->execute();

        $lastInsertId = $this->connect->lastInsertId();
        if ($lastInsertId > 0) {
            return $lastInsertId;
        }
        return false;
    }

    public function update($formation, $id)
    {
        $columnsToUpdate = array_keys($formation);
        $updateFields = '';
        foreach ($columnsToUpdate as $column) {
            $updateFields .= "{$column} = :{$column}, ";
        }
        $updateFields = rtrim($updateFields, ', ');

        $query = $this->connect->prepare("
            UPDATE formations
            SET {$updateFields}
            WHERE id_formation = :id_formation
        ");

        foreach ($formation as $field => $value) {
            $query->bindValue(":{$field}", $value);
        }

        $query->bindValue(':id_formation', $id);

        $query->execute();
        if ($query->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $query = $this->connect->prepare("
            DELETE FROM formations 
            WHERE id_formation=:id
        ");

        $query->bindValue(":id", $id);
        $query->execute();

        if ($query->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function getFormationsOfFormateur($id)
    {
        $query = $this->connect->prepare("
            SELECT 
                id_formation,
                fore.nom AS nomFormation,
                fore.slug,
                fore.image,
                fore.slug,
                DATE(fore.date_creation) AS date_creation,
                IF(TIME_FORMAT(mass_horaire, '%H') > 0, 
                    CONCAT(TIME_FORMAT(mass_horaire, '%H'), 'H ', TIME_FORMAT(mass_horaire, '%i'), 'Min'), 
                    TIME_FORMAT(mass_horaire, '%iMin')
                ) AS mass_horaire,
                prix,
                description,
                jaimes,
                n.nom AS nomNiveau,
                c.id_categorie,
                c.nom AS nomCategorie
            FROM formations fore
            JOIN formateurs f USING (id_formateur)
            JOIN categories c ON fore.id_categorie = c.id_categorie
            JOIN niveaux n ON fore.id_niveau = n.id_niveau
            WHERE id_formateur = :id_formateur
            AND etat = 'public'
            ORDER BY fore.date_creation DESC
        ");

        $query->bindValue(":id_formateur", $id);
        $query->execute();

        $formations = $query->fetchAll(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            return $formations;
        }
        return [];
    }

    public function toggleLike($id_etudiant, $id_formation)
    {
        // toggleLike
        $isLiked = $this->isLiked($id_etudiant, $id_formation);
        if ($isLiked) {
            // remove like
            $query = $this->connect->prepare("
                DELETE FROM jaimes 
                WHERE id_etudiant=:eId 
                AND id_formation=:fId
            ");
        } else {
            // add like
            $query = $this->connect->prepare("
                INSERT INTO jaimes(id_etudiant, id_formation) VALUES (:eId,:fId)
            ");
        }
        $query->bindValue(':eId', $id_etudiant);
        $query->bindValue(':fId', $id_formation);
        $query->execute();

        if ($query->rowCount() > 0) {
            return ["isLiked" => !$isLiked];
        }
        return [];
    }

    public function isLiked($id_etudiant, $id_formation)
    {
        $query = $this->connect->prepare("
            SELECT COUNT(*) AS isLiked
            FROM jaimes 
            WHERE id_etudiant=:eId 
            AND id_formation=:fId
        ");

        $query->bindValue(':eId', $id_etudiant);
        $query->bindValue(':fId', $id_formation);
        $query->execute();

        $isLiked = $query->fetch(\PDO::FETCH_OBJ)->isLiked;
		return (bool) $isLiked;
    }

    public function getLikes($id_formation)
    {
        $query = $this->connect->prepare("
            SELECT jaimes 
            FROM formations 
            WHERE  id_formation=:id_formation
        ");

        $query->bindValue(':id_formation', $id_formation);
        $query->execute();

        $jaimes = $query->fetch(\PDO::FETCH_ASSOC);
        if ($query->rowCount() > 0) {
            return $jaimes;
        }
        return [];
    }

    public function getPopularCourses()
    {
        $query = $this->connect->prepare("
            SELECT
                fore.id_formation,
                fore.slug,
                fore.image AS imgFormation,
                IF(TIME_FORMAT(mass_horaire, '%H') > 0, 
                    CONCAT(TIME_FORMAT(mass_horaire, '%H'), 'H ', TIME_FORMAT(mass_horaire, '%i'), 'Min'), 
                    TIME_FORMAT(mass_horaire, '%iMin')
                ) AS mass_horaire,
                fore.nom AS nomFormation,
                fore.date_creation,
                COUNT(id_inscription) AS total_inscriptions,
                fore.prix,
                jaimes,
                description,
                f.id_formateur,
                f.nom AS nomFormateur,
                f.prenom,
                f.img AS imgFormateur,
                c.id_categorie,
                c.nom AS nomCategorie,
                l.id_langue,
                l.nom AS nomLangue,
                n.id_niveau,
                n.nom AS nomNiveau,
                n.icon AS iconNiveau
            FROM formations fore
            LEFT JOIN inscriptions i ON fore.id_formation = i.id_formation
            JOIN formateurs f ON fore.id_formateur = f.id_formateur
            JOIN categories c ON fore.id_categorie = c.id_categorie
            JOIN langues l ON fore.id_langue = l.id_langue
            JOIN niveaux n ON fore.id_niveau = n.id_niveau
            WHERE fore.etat = 'public'
            GROUP BY id_formation, fore.nom, jaimes
            ORDER BY GREATEST(COUNT(id_inscription), jaimes) DESC
            LIMIT 10
        ");

        $query->execute();
        $formations = $query->fetchAll(\PDO::FETCH_OBJ);

        if ($query->rowCount() > 0) {
            return $formations;
        }
        return [];
    }
    
    public function find($id)
    {
        $query = $this->connect->prepare("
            SELECT 
                f.id_formation,
                f.image AS imgFormation,
                f.mass_horaire,
                c.nom AS nomCategorie,
                f.nom AS nomFormation,
                f.prix,
                f.description,
                f.jaimes,
                f.etat,
                ft.id_formateur,
                ft.nom AS nomFormateur,
                ft.prenom,
                ft.id_categorie AS categorie,
                ft.img AS imgFormateur,
                DATE(f.date_creation) AS date_creation,
                f.id_niveau AS niveau,
                f.id_langue AS langue,
                l.nom AS nomLangue,
                n.nom AS nomNiveau,
                n.icon AS iconNiveau,
                v.url,
                v.nom AS nomVideo,
                f.background_img AS bgImg,
                fichier_attache,
                f.slug
            FROM formations AS f
            JOIN formateurs AS ft ON f.id_formateur = ft.id_formateur
            JOIN categories AS c ON f.id_categorie = c.id_categorie
            JOIN langues AS l ON f.id_langue = l.id_langue
            JOIN niveaux AS n ON f.id_niveau = n.id_niveau
            JOIN videos AS v ON f.id_formation = v.id_formation
            JOIN apercus AS a ON v.id_video = a.id_video
            WHERE f.id_formation = :id
        ");

        $query->bindValue(":id", $id);
        $query->execute();

        $formation = $query->fetch(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            return $formation;
        }
        return false;
    }

    public function all()
    {
        $query = $this->connect->prepare("
            SELECT
                fore.id_formation,
                image AS imgFormation,
                mass_horaire,
                fore.nom AS nomFormation,
                fore.date_creation,
                prix,
                description,
                jaimes,
                description,
                f.id_formateur,
                f.nom AS nomFormateur,
                f.prenom,
                f.img AS imgFormateur,
                c.id_categorie,
                c.nom AS nomCategorie,
                l.id_langue,
                l.nom AS nomLangue,
                n.id_niveau,
                n.nom AS nomNiveau
            FROM formations fore
            JOIN formateurs f ON fore.id_formateur = f.id_formateur
            JOIN categories c ON fore.id_categorie = c.id_categorie
            JOIN langues l ON fore.id_langue = l.id_langue
            JOIN niveaux n ON fore.id_niveau = n.id_niveau
        ");

        $query->execute();
        $formations = $query->fetchAll(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            return $formations;
        }
        return [];
    }

    public function mostLiked($offset)
    {
        $query = $this->connect->prepare("
            SELECT 
                formations.id_formation,
                formations.image AS imgFormation,
                formations.mass_horaire,
                categories.nom AS nomCategorie,
                formations.nom AS nomFormation,
                formations.prix,
                formations.description,
                formations.jaimes,
                formateurs.id_formateur,
                formateurs.nom AS nomFormateur,
                formateurs.prenom,
                formateurs.img AS imgFormateur
            FROM formations, formateurs, categories
            WHERE formations.id_formateur = formateurs.id_formateur
            AND categories.id_categorie = formations.id_categorie
            AND formations.etat = 'public'
            ORDER BY formations.jaimes DESC
            LIMIT {$offset}, 10
        ");

        $query->execute();
        $formations = $query->fetchAll(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            return $formations;
        }
        return [];
    }

    public function getCanJoinCourses($code)
    {
        $query = $this->connect->prepare("
            SELECT 
                id_formation,
                f.id_formateur,
                f.nom,
                prenom,
                prix
            FROM formations 
            JOIN formateurs AS f USING (id_formateur)
            WHERE BINARY `code` = :code 
            AND etat = 'private'
            AND can_join = 1
        ");

        $query->execute([
            'code' => $code
        ]);

        $formations = $query->fetchAll(\PDO::FETCH_OBJ);

        if ($query->rowCount() > 0) {
            return $formations;
        }
        return [];
    }

    public function whereSlug($slug)
    {
        $query = $this->connect->prepare("
            SELECT 
                f.id_formation,
                f.image AS imgFormation,
                DATE_FORMAT(f.mass_horaire, '%H:%i') AS mass_horaire,
                cf.nom AS nomCategorieFormation,
                f.nom AS nomFormation,
                f.prix,
                f.description,
                f.jaimes,
                f.background_img AS bgImg,
                f.slug AS slugFormation,
                fo.id_formateur,
                fo.nom AS nomFormateur,
                fo.prenom,
                fo.slug AS slugFormateur,
                cf_formateurs.nom AS nomCategorieFormateur,
                fo.img AS imgFormateur,
                DATE(f.date_creation) AS date_creation,
                f.id_niveau AS niveau,
                f.id_langue AS langue,
                l.nom AS nomLangue,
                n.nom AS nomNiveau,
                n.icon AS iconNiveau
            FROM formations f
            JOIN formateurs fo ON f.id_formateur = fo.id_formateur
            JOIN categories cf ON cf.id_categorie = f.id_categorie
            JOIN categories cf_formateurs ON cf_formateurs.id_categorie = fo.id_categorie
            JOIN langues l ON f.id_langue = l.id_langue
            JOIN niveaux n ON f.id_niveau = n.id_niveau
            WHERE f.slug = :slug
        ");

        $query->bindValue(":slug", $slug);
        $query->execute();

        $formation = $query->fetch(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            return $formation;
        }
        return false;
    }

    public function filter($offset, $numberRecordsPerPage, $sort, $filter, $id_formateur = null)
    {
        $query = $this->connect->prepare("
            SELECT
                fore.id_formation,
                fore.slug,
                fore.image AS imgFormation,
                IF(TIME_FORMAT(mass_horaire, '%H') > 0, 
                    CONCAT(TIME_FORMAT(mass_horaire, '%H'), 'H ', TIME_FORMAT(mass_horaire, '%i'), 'Min'), 
                    TIME_FORMAT(mass_horaire, '%iMin')
                ) AS mass_horaire,
                fore.nom AS nomFormation,
                DATE_FORMAT(fore.date_creation, '%d/%m/%Y %H:%i') AS date_creation,
                prix,
                jaimes,
                description,
                f.id_formateur,
                f.nom AS nomFormateur,
                f.prenom,
                f.img AS imgFormateur,
                c.id_categorie,
                c.nom AS nomCategorie,
                l.id_langue,
                l.nom AS nomLangue,
                n.id_niveau,
                n.nom AS nomNiveau,
                n.icon AS iconNiveau,
                IF(insc.total_inscriptions IS NULL, 0, insc.total_inscriptions) AS total_inscriptions
            FROM formations fore
            JOIN formateurs f ON fore.id_formateur = f.id_formateur
            JOIN categories c ON fore.id_categorie = c.id_categorie
            JOIN langues l ON fore.id_langue = l.id_langue
            JOIN niveaux n ON fore.id_niveau = n.id_niveau
            LEFT JOIN (
                SELECT
                    f.id_formation,
                    COUNT(i.id_formation) AS total_inscriptions
                FROM formations f
                LEFT JOIN inscriptions i ON f.id_formation = i.id_formation
                WHERE i.payment_state = 'approved'
                GROUP BY f.id_formation
            ) AS insc ON fore.id_formation = insc.id_formation
            WHERE
            {$filter}
            {$id_formateur}
            ORDER BY {$sort} DESC
            LIMIT {$offset}, {$numberRecordsPerPage}
        ");

        $query->execute();

        $formations = $query->fetchAll(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            return $formations;
        }
        return [];
    }

    public function countFiltred($filter, $id_formateur = null)
    {
        $query = $this->connect->prepare("
            SELECT
                COUNT(fore.id_formation) AS total_filtred
            FROM formations fore
            JOIN formateurs f ON fore.id_formateur = f.id_formateur
            JOIN categories c ON fore.id_categorie = c.id_categorie
            JOIN langues l ON fore.id_langue = l.id_langue
            JOIN niveaux n ON fore.id_niveau = n.id_niveau
            WHERE 
            {$filter}
            {$id_formateur}
        ");

        $query->execute();

        $response = $query->fetch(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            return $response->total_filtred;
        }
        return [];
    }

    public function groupByCategorie()
    {
        $query = $this->connect->prepare("
            SELECT
                c.nom,
                COALESCE(COUNT(f.id_categorie), 0) AS total_formations
            FROM categories c
            LEFT JOIN formations f USING (id_categorie)
            WHERE COALESCE(f.etat, 'public') = 'public'
            GROUP BY c.id_categorie, c.nom
        ");

        $query->execute();

        $categories = $query->fetchAll(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            return $categories;
        }
        return [];
    }

    public function groupByLangue()
    {
        $query = $this->connect->prepare("
            SELECT
                l.nom,
                COALESCE(COUNT(f.id_langue), 0) AS total_formations
            FROM langues l
            LEFT JOIN formations f USING (id_langue)
            WHERE COALESCE(f.etat, 'public') = 'public'
            GROUP BY l.id_langue, l.nom
        ");

        $query->execute();

        $categories = $query->fetchAll(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            return $categories;
        }
        return [];
    }

    public function groupByNiveau()
    {
        $query = $this->connect->prepare("
            SELECT
                n.nom,
                COALESCE(COUNT(f.id_niveau), 0) AS total_formations
            FROM niveaux n
            LEFT JOIN formations f USING (id_niveau)
            WHERE COALESCE(f.etat, 'public') = 'public'
            GROUP BY n.id_niveau, n.nom
        ");

        $query->execute();

        $categories = $query->fetchAll(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            return $categories;
        }
        return [];
    }

    public function groupByDuration()
    {
        $query = $this->connect->prepare("
            CALL group_formation_by_duration()
        ");

        $query->execute();

        $categories = $query->fetchAll(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            return $categories;
        }
        return [];
    }

    public function select($id_formation,Array $selectedFields)
    {
        $query = $this->connect->prepare("
            SELECT 
                ".implode(', ', $selectedFields)."
            FROM formations
            WHERE id_formation = :id_formation
        ");

        $query->bindValue(':id_formation', $id_formation);
        $query->execute();

        $formation = $query->fetch(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            return $formation;
        }
        return false;
    }

    public function setColumnToNull($columnName, $table, $column_id, $id)
    {
        $query = $this->connect->prepare("
            UPDATE {$table}
            SET {$columnName} = NULL
            WHERE {$column_id} = :id
        ");

        $query->bindValue(':id', $id);

        $query->execute();
        if ($query->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function where($columns, $conditions)
    {
        $filters = array_keys($conditions);
        foreach($filters as $key => $column){
            $filters[$key] = "{$column} = :{$column}";
        }
        
        $query = $this->connect->prepare("
            SELECT 
                ".implode(', ', $columns)."
            FROM formations
            WHERE ".implode(' AND ', $filters)
        );

        foreach ($conditions as $column => $value) $query->bindValue($column, $value);
        $query->execute();

        $formations = $query->fetchAll(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            return $formations;
        }
        return [];
    }

    public function videos($offset, $numberRecordsPerPage, $idFormation)
	{
		$query = $this->connect->prepare("
			SELECT 
				v.id_video,
				f.id_formation,
				v.nom AS nomVideo,
				url,
				duree,
				v.description,
				v.created_at,
				date_creation,
				f.nom AS nomFormation,
				mass_horaire,
				IF(a.id_video = v.id_video, 1, 0) AS is_preview,
				ordre,
				thumbnail,
				IF(b.id_video = v.id_video, 1, 0) AS is_bookmarked
			FROM videos v
			LEFT JOIN apercus a ON v.id_video = a.id_video
			LEFT JOIN bookmarks b ON v.id_video = b.id_video
			JOIN formations f ON v.id_formation = f.id_formation
			WHERE f.id_formation = :id_formation
			ORDER BY ordre
            LIMIT {$offset}, {$numberRecordsPerPage}
		");

		$query->bindParam(':id_formation', $idFormation);
		$query->execute();

		$videos = $query->fetchAll(\PDO::FETCH_OBJ);
		if ($query->rowCount() > 0) {
			foreach ($videos as $video) {
				$datetime = new Carbon($video->created_at);
				$video->created_at = $datetime->diffForHumans();
				$duree = explode(":", $video->duree);
				$video->duree = $duree[1].":".$duree[2];
			}
			
			return $videos;
		}
		return [];
	}

    public function promotions($id_formateur)
    {
        $query = $this->connect->prepare("
            SELECT
                f.id_formation,
                id_promotion,
                nom AS nomFormation,
                image AS imgFormation,
                prix,
                reduction,
                date_start,
                date_end
            FROM formations f
            JOIN promotions USING (id_formation)
            WHERE etat = 'public' AND id_formateur = :id_formateur
		");

		$query->bindParam(':id_formateur', $id_formateur);
		$query->execute();

		$promotions = $query->fetchAll(\PDO::FETCH_OBJ);
		if ($query->rowCount() > 0) {
			return $promotions;
		}
		return [];
    }
}