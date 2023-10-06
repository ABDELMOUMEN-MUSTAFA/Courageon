<?php

/**
 *  Model Notification
 */

namespace App\Models;

use Carbon\Carbon;

use App\Libraries\Database;

class Notification
{
    private $connect;

    public function __construct()
    {
        $this->connect = Database::getConnection();
    }

    public function create($data)
    {
        $query = $this->connect->prepare("
			INSERT INTO notifications
			VALUES (DEFAULT, :content, DEFAULT, :url, :icon, DEFAULT, :sender_id, :recipient_id)
		");

		$query->bindValue(':content', $data['content']);
		$query->bindValue(':url', $data['url'] ?? null);
		$query->bindValue(':icon', $data['icon'] ?? null);
		$query->bindValue(':sender_id', $data['sender_id']);
		$query->bindValue(':recipient_id', $data['recipient_id']);
		$query->execute();

		$lastInsertId = $this->connect->lastInsertId();
		if ($lastInsertId > 0) {
			return $lastInsertId;
		}
		return false;
    }

    public function whereRecipient($recipient_id)
    {
        $query = $this->connect->prepare("
            SELECT
                id_notification,
                nom,
                prenom,
                img,
                created_at,
                UNIX_TIMESTAMP(created_at) AS unix_timestamp,
                content,
                is_read,
                slug
            FROM notifications n
            JOIN etudiants AS e ON n.sender_id = e.id_etudiant
            WHERE recipient_id = :recipient_id
            ORDER BY created_at DESC
        ");

        $query->bindValue(':recipient_id', $recipient_id);
        $query->execute();
        $notifications = $query->fetchAll(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            foreach($notifications as $n){
                $datetime = new Carbon($n->created_at);
                $n->created_at = $datetime->diffForHumans();
            }
            return $notifications;
        }
        return [];
    }

    public function update($data, $id_notification)
    {
        $sql = "UPDATE notifications SET ";
        
        $updates = [];
        foreach ($data as $field => $value) {
            $updates[] = "$field = :$field";
        }
        
        $sql .= implode(', ', $updates);
        $sql .= " WHERE id_notification = :id_notification";
        
        $query = $this->connect->prepare($sql);

        foreach ($data as $field => $value) {
            $query->bindValue(":$field", $value);
        }

        $query->bindValue(':id_notification', $id_notification);
        $query->execute();

        if ($query->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function clearSeen($recipient_id)
    {
        $query = $this->connect->prepare("
            DELETE FROM notifications
            WHERE recipient_id = :recipient_id AND is_read = 1
        ");

        $query->bindValue(':recipient_id', $recipient_id);
        $query->execute();

        if ($query->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function newNotification($recipient_id)
    {
        $query = $this->connect->prepare("
            SELECT
                id_notification,
                nom,
                prenom,
                img,
                created_at,
                UNIX_TIMESTAMP(created_at) AS unix_timestamp,
                content,
                is_read,
                slug
            FROM notifications n
            JOIN etudiants AS e ON n.sender_id = e.id_etudiant
            WHERE recipient_id = :recipient_id
            ORDER BY created_at DESC
            LIMIT 1
        ");

        $query->bindValue(':recipient_id', $recipient_id);
        $query->execute();

        $notification = $query->fetch(\PDO::FETCH_OBJ);
        if ($query->rowCount() > 0) {
            $datetime = new Carbon($notification->created_at);
            $notification->created_at = $datetime->diffForHumans();
            return $notification;
        }
        return false;
    }
}
