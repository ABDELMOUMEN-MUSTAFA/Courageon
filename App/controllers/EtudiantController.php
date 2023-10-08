<?php

namespace App\Controllers;

use App\Models\{
    Formation,
    Video,
    Inscription,
    Etudiant,
    Message,
    Formateur,
    Notification
};

use App\Libraries\{
    Response,
    Validator
};

class EtudiantController
{
	private $id_etudiant;

	public function __construct()
	{
		if (!auth()) {
			return redirect('user/login');
		}

		if(session('user')->get()->type !== 'etudiant'){
			return view('errors/page_404', [], 404);
		}

		if(!session('user')->get()->email_verified_at) {
			return redirect('user/verify');
		}

		$this->id_etudiant = session('user')->get()->id_etudiant;
	}

	public function index($request)
	{
		if($request->getMethod() !== 'GET'){
			return Response::json(null, 405, "Method Not Allowed");
		}

		return view("etudiants/index");
	}

	public function inscriptions($request)
	{
		if($request->getMethod() !== 'GET'){
			return Response::json(null, 405, "Method Not Allowed");
		}

		$inscriptionModel = new Inscription;
		$totalInscriptions = $inscriptionModel->countInscriptionsOfEtudiant($this->id_etudiant);
		$formations = paginator($totalInscriptions, 4, 'my_courses', $inscriptionModel, 'getFormationsOfEtudiant', ['id' => $this->id_etudiant]);
		return Response::json($formations);
	}

	public function joinCourse($request)
    {
		if($request->getMethod() !== 'POST'){
			return Response::json(null, 405, "Method Not Allowed");
		}

		// Check CSRF token
		if(!csrf_token($request->post('_token'))){
			return Response::json(null, 403, "Invalid Token");
		}

		$validator = new Validator([
			'code' => strip_tags(trim($request->post('code')))
		]);

		$validator->validate([
			'code' => 'required|min:30|max:60|alphanum|exists:formateurs'
		]);

		$formationModel = new Formation;
		$formations = $formationModel->getPrivateFormations($validator->validated()["code"]);
		if(!$formations){
			return Response::json(null, 400, "Sorry! this instractor doesn't have any courses yet.");
		}

		$inscriptionModel = new Inscription;
        foreach ($formations as $formation) {
            $inscription = $inscriptionModel->checkIfAlready($this->id_etudiant, $formation->id_formation);
            if (!$inscription) {
                $inscriptionData = [
                    "id_formation" => $formation->id_formation,
                    "id_etudiant" => $this->id_etudiant,
                    "id_formateur" => $formation->id_formateur,
                    "prix" => $formation->prix,
                    "transaction_info" => 0,
                    "payment_id" => 0,
                    "payment_state" => 'approved',
                    "date_inscription" => date('Y-m-d H:i:s'),
                    "approval_url" => 0
                ];

                $inscriptionModel->create($inscriptionData);
            }
        }

		$notificationModel = new Notification;
		$notificationModel->create([
			'content' => "Joined Your Private Courses",
			'sender_id' => session('user')->get()->id_etudiant,
			'recipient_id' => $formation->id_formateur
		]);
		
        return Response::json(null, 200, "Congrats! vous avez rejoindre toutes les formations de formateur <strong>{$formations[0]->nom} {$formations[0]->prenom}</strong>.");
    }

    public function formation($request, $id_formation = null)
    {
		if($request->getMethod() !== 'GET'){
			return Response::json(null, 405, "Method Not Allowed");
		}

		if(is_null($id_formation) || !is_numeric($id_formation)){
			return view("errors/page_404");
		}

		$inscriptionModel = new Inscription;
		$formation = $inscriptionModel->getMyCourse($this->id_etudiant, $id_formation);
		if(!$formation){
			return view("errors/page_404");
		}

		$videoModel = new Video;
		$videos = $videoModel->getVideosOfFormation($id_formation);
		return view('etudiants/formation', compact('formation', 'videos'));
    }

	public function bookmarks($request)
	{
		if($request->getMethod() !== 'GET'){
			return Response::json(null, 405, "Method Not Allowed");
		}

		$videoModel = new Video;
		$bookmarks = $videoModel->getMyBookmarks($this->id_etudiant);
		return view("etudiants/bookmarks", compact('bookmarks'));
	}

    public function toggleLikeFormation($request, $id_formation = null)
    {
		if($request->getMethod() !== 'POST'){
			return Response::json(null, 405, "Method Not Allowed");
		}

		$validator = new Validator([
			'id_formation' => strip_tags(trim($id_formation))
		]);

		// CHECK IF THE AUTH ETUDIANT OWEN THIS COURSE.
        $relationship = [
            "from" => "inscriptions",
            "join" => "etudiants",
            "on" => "id_etudiant",
            "where" => [
                "id_formation" => $id_formation,
                "etudiants->id_etudiant" => session('user')->get()->id_etudiant
            ]
        ];

        $validator->checkPermissions($relationship);

		$validator->validate([
			'id_formation' => 'required|numeric|exists:formations'
		]);

		$formationModel = new Formation;
		$notificationModel = new Notification;
		$isLiked = $formationModel->toggleLike($this->id_etudiant, $id_formation);
		# (:content, :is_read, :url, :icon, :created_at, :sender_id, :recipient_id)
		$formation = $formationModel->select($id_formation, ['nom', 'id_formateur']);
		$notificationModel->create([
			'content' => ($isLiked['isLiked'] ? 'Liked' : 'Unliked')." Your Course <strong>{$formation->nom}</strong>",
			'sender_id' => $this->id_etudiant,
			'recipient_id' => $formation->id_formateur
		]);
        $newLikes = $formationModel->getLikes($id_formation);
        return Response::json(array_merge($newLikes, $isLiked));
    }

    public function toggleBookmarkVideo($request, $id_video = null)
    {
		if($request->getMethod() !== 'POST'){
			return Response::json(null, 405, "Method Not Allowed");
		}

		$validator = new Validator([
			'id_video' => strip_tags(trim($id_video))
		]);

		// CHECK IF THE ETUDIANT HAS THIS VIDEO
		$relationship = [
			"from" => "inscriptions",
			"join" => "videos",
			"on" => "id_formation",
			"where" => [
				"id_video" => $id_video,
				"id_etudiant" => $this->id_etudiant,
			]
		];

		$validator->checkPermissions($relationship);

		$validator->validate([
			'id_video' => 'required|numeric|exists:videos'
		]);

		$videoModel = new Video;
        $response = $videoModel->toggleBookmark($this->id_etudiant, $id_video);
        return Response::json($response);
    }

	public function messages($request, $slug = null)
    {
		if($request->getMethod() !== 'GET'){
			return Response::json(null, 405, "Method Not Allowed");
		}

		$messageModel = new Message;
		$myFormateurs = $messageModel->myFormateurs($this->id_etudiant);
		$allowedFormateurs = [];
		foreach($myFormateurs as $formateur) array_push($allowedFormateurs, $formateur->slug);
		
		// Prevent getting conversations that user not allowed to
		if($slug && !in_array($slug, $allowedFormateurs)){
			return Response::json(null, 403, "Something went wrong!");
		}

		if(is_null($slug)){
			$conversations = false;
			$formateur = false;

			return view('etudiants/messages', compact('conversations', 'formateur', 'myFormateurs'));
		}

		$conversations = $messageModel->conversations(session("user")->get()->slug, $slug);

		// Match video name with its formation
		$videoModel = new Video;
		foreach ($conversations as $conversation) {
			if (preg_match('/@([^@]+)@/', $conversation->message, $matches)) {
				$nomVideo = $matches[1];
				if($video = $videoModel->whereNom($nomVideo)){
					$conversation->message = preg_replace('/@([^@]+)@/', "<div class=\"pb-2\"><a target=\"_blank\" href=\"".URLROOT."/etudiant/formation/{$video->id_formation}\">{$nomVideo}</a></div>", $conversation->message, 1);
				}
			}
		}

		$formateurModel = new Formateur;
		$formateur = $formateurModel->whereSlug($slug);

		$last_message = $messageModel->getLastMessage($formateur->id_formateur, $this->id_etudiant);
		$last_message_time = $last_message->unix_timestamp ?? '00000000';

        return view('etudiants/messages', compact('conversations', 'formateur', 'myFormateurs', 'last_message_time'));
    }

	public function edit($request)
	{
		if($request->getMethod() === 'GET'){
			$etudiantModel = new Etudiant;
			return view('etudiants/edit-profil', ['etudiant' => $etudiantModel->find($this->id_etudiant)]);
		}

		$tabs = ["AccountTab", "PrivateTab"];
		if($request->getMethod() === 'PUT'){
			if(!in_array($request->post("tab"), $tabs)){
				return Response::json(null, 400, "Bad Request");
			}

			return $this->{"_edit".$request->post('tab')}($request);
		}

		return Response::json(null, 405, "Method Not Allowed");
	}

	private function _editAccountTab($request)
    {
		// Check CSRF token
		if(!csrf_token($request->post('_token'))){
			return Response::json(null, 403, "Invalid Token");
		}

    	$validator = new Validator([
            'nom' => strip_tags(trim($request->post("nom"))),
            'prenom' => strip_tags(trim($request->post("prenom"))),
        ]);

        $validator->validate([
            'nom' => 'required|min:3|max:15|alpha',
            'prenom' => 'required|min:3|max:15|alpha',
        ]);

        $updatedData = $validator->validated();

        // update avatar
        if($request->file('image')){
        	unset($validator);

        	$validator = new Validator([
            	'img' => $request->file("image"),
	        ]);

	        $validator->validate([
	            'img' => 'size:5|image',
	        ]);

			$etudiantModel = new Etudiant;
	        $oldAvatar = $etudiantModel->select($this->id_etudiant, ['img']);

	        if($oldAvatar !== 'users/avatars/default.png'){
	        	unlink('images/'.$oldAvatar->img);
	        }

	        $updatedData['img'] = uploader($request->file("image"), 'images/users/avatars');
	        $_SESSION['user']->img = $updatedData['img'];
        }

		$etudiantModel = new Etudiant;
        if($etudiant = $etudiantModel->update($updatedData, $this->id_etudiant)){
        	return Response::json($etudiant, 200, 'Updated successfuly.');
        }
        return Response::json(null, 500, "Coudn't update your account, please try again later.");
    }

	private function _editPrivateTab($request)
    {
		// Check CSRF token
		if(!csrf_token($request->post('_token'))){
			return Response::json(null, 403, "Invalid Token");
		}

    	$validator = new Validator([
            'cmdp' => $request->post("cmdp"),
            'password' => $request->post("mdp"),
            'password_confirmation' => $request->post("vmdp"),
        ]);

        $validator->validate([
            'cmdp' => 'required|check_password:etudiants',
            'password' => 'required|confirm|min:10|max:50',
        ]);


		$etudiantModel = new Etudiant;
        if($etudiantModel->update(['mot_de_passe' => $validator->validated()['password']], $this->id_etudiant)){
        	return Response::json(null, 200, 'Updated successfuly.');
        }
        return Response::json(null, 500, "Coudn't update your password, please try again later.");
    }

	public function changeEmail($request)
    {
    	if ($request->getMethod() === 'PUT') {
			// Check CSRF token
            if(!csrf_token($request->post('_token'))){
                return Response::json(null, 403, "Invalid Token");
            }
			
			$validator = new Validator([
            	'email' => strip_tags(trim($request->post("email"))),
	        ]);

	        $validator->validate([
	            'email' => 'email|max:100|unique:etudiants|unique:formateurs',
	        ]);			

	        $token = bin2hex(random_bytes(16));
			$etudiantModel = new Etudiant;
            $etudiantModel->updateToken(session('user')->get()->email, hash('sha256', $token), 30);
	        session('new_email')->set($validator->validated()['email']);

			sleep(30);

            try {
                $mail = new \App\Libraries\Mail;
                $mail->to(session('new_email')->get())
                ->subject("Vérification d'adresse e-mail")
                ->body(null, 'verify-email.php', [
                    '::tokenLink',
                    '::expirationTime',
                ],
                [
                    URLROOT."/etudiant/confirmEmail/?token=".$token,
                    '30 minutes',
                ])->attach(['images/logos/dark-logo.png' => 'logo'])
                ->send();

                return Response::json(null, 200, "Nous avons envoyé votre lien de vérification par e-mail.");
            } catch (\Exception $e) {
                // echo json_encode($mail->ErrorInfo);
                return Response::json(null, 500, "L'email n'a pas pu être envoyé.");
            }
		}

		return Response::json(null, 405, "Method Not Allowed");	
    }

    public function confirmEmail($request)
    {
    	if($request->getMethod() !== 'GET'){
    		return Response::json(null, 405, "Method Not Allowed");
    	}

    	if(!$request->get('token')){
            return view('errors/page_404');
        }

		$statement = \App\Libraries\Database::getConnection()->prepare("
            SELECT
                verification_token,
                expiration_token_at
            FROM etudiants
            WHERE verification_token = :token
        ");

        $statement->execute([
            "token" => hash('sha256', $request->get('token')),
        ]);

        $etudiant = $statement->fetch(\PDO::FETCH_OBJ);
        if(!$etudiant) {
            return view('errors/page_404');
        }

        if(strtotime($etudiant->expiration_token_at) < time()) {
            return view('errors/token_expired');
        }

		$etudiantModel = new Etudiant;
        $etudiantModel->update(["email" => session('new_email')->get()], $this->id_etudiant);
        $_SESSION['user']->email = session('new_email')->get();
        session('email')->remove();
        flash("emailChanged", '<i class="material-icons text-success mr-3">check_circle</i><div class="text-body">You\'re email changed successfuly</div>', "alert alert-light border-1 border-left-3 border-left-success d-flex");
        return redirect('etudiant/edit');
    }
}
