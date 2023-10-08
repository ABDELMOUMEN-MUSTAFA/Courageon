<?php

namespace App\Controllers\Api;

use App\Models\Notification;

use App\Libraries\{Response, Validator};

class NotificationController extends \App\Controllers\Api\ApiController
{
    private $notificationModel;

    public function __construct()
    {
        if(!auth()){
            return Response::json(null, 401, "Unauthorized");
        }

        if(session('user')->get()->type !== 'formateur'){
            return Response::json(null, 403); 
        }

        if(!session('user')->get()->email_verified_at) {
            return Response::json(null, 403);
        }

        if(!session('user')->get()->is_all_info_present){
			return Response::json(null, 403); 
		}

        $this->notificationModel = new Notification;
        parent::__construct();
    }

    public function index($request)
    {
        $last_notif_time = $request->get('last_notif_time');
        if(!is_numeric($last_notif_time)){
            return Response::json(null, 412, "Invalid unix timestamp format.");
        }

        if(strlen($last_notif_time) !== 10){
            return Response::json(null, 412, "Invalid unix timestamp format.");
        }

        $last_notification = $this->notificationModel->newNotification(session('user')->get()->id_formateur, $last_notif_time);

        if ($last_notification && $last_notification->unix_timestamp > $last_notif_time) {
            return Response::json($last_notification);
        }
        return Response::json(null, 204);
    }

    public function update($request, $id)
    {
        $validator = new Validator([
            'id_notification' => strip_tags(trim($id))
        ]);

        // CHECK IF THE NOTIFICATION BELONGS TO AUTH FORMATEUR
        $relationship = [
			"from" => "notifications",
			"join" => "formateurs",
			"on" => "recipient_id=id_formateur",
			"where" => [
				"id_notification" => $id,
				"id_formateur" => session('user')->get()->id_formateur
			]
		];

        $validator->checkPermissions($relationship);

        $validator->validate([
            'id_notification' => 'required|numeric|exists:notifications',
        ]);

        if($this->notificationModel->update(['is_read' => true], $id)){
            return Response::json(null, 200, 'Read successfuly.');
        }
        return Response::json(null, 500, "Something went wrong.");
    }

    public function delete($id)
    {
        if($id === 'all'){
            if($this->notificationModel->clearSeen(session('user')->get()->id_formateur)){
                return Response::json(null, 204);
            }
            return Response::json(null, 500, "Something went wrong.");
        }
        return Response::json(null, 400, 'Bad Request');
    }


}