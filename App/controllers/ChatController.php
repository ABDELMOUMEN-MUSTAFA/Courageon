<?php

namespace App\Controllers;

use App\Libraries\{Response, Validator};

use App\Models\Message;

class ChatController
{
    public function __construct()
    {
        if(!auth()){
            return Response::json(null, 401, "Unauthorized");
        }

        if(!session('user')->get()->email_verified_at) {
            return redirect('user/verify');
        }

        if(!session('user')->get()->is_all_info_present){
			return redirect('user/continue');
		}
    }

    public function index($request, $from_user = null)
    {
        if($request->getMethod() !== 'GET'){
            return Response::json(null, 405, "Method Not Allowed");
        }

        if(is_null($from_user)){
            return Response::json(null, 404, "User not found");
        }
        
        $last_message_time = $request->get('last_time');
        if(!is_numeric($last_message_time)){
            return Response::json(null, 412, "Invalid unix timestamp format.");
        }

        if(strlen($last_message_time) !== 10){
            return Response::json(null, 412, "Invalid unix timestamp format.");
        }

        $user = session('user')->get()->type === 'formateur' ? 'etudiant' : 'formateur';
        $currentUserType = session('user')->get()->type;
        
        $validator = new Validator([
            "id_$user" => $from_user
        ]);

        $validator->validate([
            "id_$user" => "required|exists:".$user."s",
        ]);

        $messageModel = new Message;
        $myContacts = $messageModel->{'my'.ucfirst($user).'s'}((session('user')->get()->{"id_$currentUserType"}));
        $allowedContacts = [];
		foreach($myContacts as $contact) array_push($allowedContacts, $contact->{"id_$user"});
		
		// Prevent getting conversations that user not allowed to
		if($from_user && !in_array($from_user, $allowedContacts)){
			return Response::json(null, 403, "Insufficient permissions.");
		}

        sleep(1);

        // Get the last message
        $last_message = $messageModel->getLastMessage($from_user, session('user')->get()->{"id_$currentUserType"});
        
        if ($last_message && $last_message->unix_timestamp > $last_message_time) {
            return Response::json($last_message);
        }
        return Response::json(null, 204);
    }
}