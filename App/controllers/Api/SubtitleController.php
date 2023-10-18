<?php

namespace App\Controllers\Api;

use App\Libraries\{Response, Validator};
use App\Models\Subtitle;

class SubtitleController extends \App\Controllers\Api\ApiController
{
    private $subtitleModel;

    public function __construct()
    {
        $this->subtitleModel = new Subtitle;
        parent::__construct();
    }

    public function index($request)
    {
        $id_video = $request->get('id_video');
        if(!$id_video || !is_numeric($id_video)){
            return Response::json(null, 400, "Bad Request");
        }

        $validator = new Validator([
            'id_video' => $id_video
        ]);

        $validator->validate([
            'id_video' => 'required|numeric|exists:videos',
        ]);
        
        $subtitles = $this->subtitleModel->where(['id_sous_titre', 'id_video', 'nom', 'source'], ['id_video' => $id_video]);
        return Response::json($subtitles);
    }

    public function delete($id)
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

        $validator = new Validator([
            'id_sous_titre' => $id
        ]);

        $validator->validate([
            'id_sous_titre' => 'required|numeric|exists:sous_titres',
        ]);

        $sous_titre = $this->subtitleModel->find($id);

        // CHECK IF THE VIDEO BELONGS TO THE AUTH FORMATEUR.
        $relationship = [
            "from" => "formations",
            "join" => "videos",
            "on" => "id_formation",
            "where" => [
                "id_video" => $sous_titre->id_video,
                "id_formateur" => session('user')->get()->id_formateur
            ]
        ];

        $validator->checkPermissions($relationship);

        if($this->subtitleModel->delete($id)){
        	unlink($sous_titre->source);
        	return Response::json(null, 200, 'Deleted successfuly.');
        }

        return Response::json(null, 500, "Coudn't delete the subtitle, please try again later.");  
    }
}