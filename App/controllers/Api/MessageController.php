<?php

namespace App\Controllers\Api;

use App\Models\Message;

use App\Libraries\{Response, Validator};

class MessageController extends \App\Controllers\Api\ApiController
{
    private $messageModel;

    public function __construct()
    {
        if(!auth()){
            return Response::json(null, 401, "Unauthorized");
        }

        if(!session('user')->get()->email_verified_at) {
            return Response::json(null, 403);
        }

        $this->messageModel = new Message;
        parent::__construct();
    }

    public function store($request)
    {
        if(session("user")->get()->type === 'etudiant'){
            return $this->_storeEtudiantMsg($request);
        }

        if(!session('user')->get()->is_all_info_present){
			return Response::json(null, 403); 
		}
        
        return $this->_storeFormateurMsg($request);
    }

    private function _storeEtudiantMsg($request)
    {
        $validator = new Validator([
            'from' => session('user')->get()->id_etudiant,
            'id_formateur' => strip_tags(trim($request->post('to'))),
            'message' => htmlspecialchars(trim($request->post('message'))),
        ]);

        // PREVENT SENDING MESSAGE TO NOT AUTORIZED FORMATEUR
        $relationship = [
			"from" => "inscriptions",
			"join" => "formateurs",
			"on" => "id_formateur",
			"where" => [
				"id_etudiant" => session('user')->get()->id_etudiant,
				"inscriptions->id_formateur" => $request->post('to')
			]
		];
        
        $validator->checkPermissions($relationship);

        $validator->validate([
            'id_formateur' => 'required|min:4|exists:formateurs',
            'message' => 'required|max:255',
        ]);

        $message = $validator->validated();
        $nomVideo = strip_tags(trim($request->post('nom_video')));
        if(strlen($nomVideo) > 1 || strlen($nomVideo) > 80){
            $message['nom_video'] = $nomVideo."@";
        }else{
            $message['nom_video'] = "";
        }
        $message["message"] =  $message["nom_video"].$message["message"];
        $message["to"] = $message["id_formateur"];
        unset($message["id_formateur"]);
        unset($message["nom_video"]);
        unset($message["type"]);
        
        if($conversation = $this->messageModel->create($message)){
            return Response::json($conversation, 201, 'Sent successfuly.');
        }
        return Response::json(null, 500, "Something went wrong.");
    }

    private function _storeFormateurMsg($request)
    {        
        $validator = new Validator([
            'from' => session('user')->get()->id_formateur,
            'id_etudiant' => strip_tags(trim($request->post('to'))),
            'message' => htmlspecialchars(trim($request->post('message'))),
        ]);

        // PREVENT SENDING MESSAGE TO NOT AUTORIZED ETUDIANT
        $relationship = [
			"from" => "inscriptions",
			"join" => "etudiants",
			"on" => "id_etudiant",
			"where" => [
				"id_formateur" => session('user')->get()->id_formateur,
				"inscriptions->id_etudiant" => $request->post('to')
			]
		];
        
        $validator->checkPermissions($relationship);

        $validator->validate([
            'id_etudiant' => 'required|min:4|exists:etudiants',
            'message' => 'required|max:255',
        ]);

        $message = $validator->validated();
        $nomVideo = strip_tags(trim($request->post('nom_video')));
        if(strlen($nomVideo) > 1 || strlen($nomVideo) > 80){
            $message['nom_video'] = $nomVideo."@";
        }else{
            $message['nom_video'] = "";
        }
        $message["message"] =  $message["nom_video"].$message["message"];
        $message["to"] = $message["id_etudiant"];
        unset($message["id_etudiant"]);
        unset($message["nom_video"]);
        unset($message["type"]);
        
        if($conversation = $this->messageModel->create($message)){
            return Response::json($conversation, 201, 'Sent successfuly.');
        }
        return Response::json(null, 500, "Something went wrong.");
    }
}