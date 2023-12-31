<?php

namespace App\Controllers;

use App\Models\{
    Categorie,
    Preview,
    Formation,
    Video,
    Inscription,
    Langue,
    Level,
    Notification
};

use App\Libraries\{Response, Validator};

class CoursesController
{
    public function search($request)
    {
        if($request->getMethod() === 'GET'){
            $formationModel = new Formation;

            $data = [
                'niveaux' => $formationModel->groupByNiveau(),
                'langues' => $formationModel->groupByLangue(),
                'categories' => $formationModel->groupByCategorie(),
                'durations' => $formationModel->groupByDuration(),
            ];

            return view("courses/index", $data);
        }

        return Response::json(null, 405, "Method Not Allowed");
    }

    public function index($request, $search = null, $relationship = null)
    {  
        if($request->getMethod() !== 'GET'){
            return Response::json(null, 405, "Method Not Allowed");
        }

        if(!is_null($relationship)){
        	if(!auth()){
        		return redirect('user/login');
	        }

	        if(session('user')->get()->type !== 'formateur'){
	           return view('errors/page_404', [], 404);
	        }

            if(!session('user')->get()->email_verified_at) {
                return redirect('user/verify');
            }
    
            if(!session('user')->get()->is_all_info_present){
                return redirect('user/continue');
            }

            $formationRelationships = ['videos'];
            if(!in_array($relationship, $formationRelationships)){
                return view('errors/page_404', [], 404);
            }

            $validator = new Validator(['id_formation' => $search]);

            // CHECK IF THIS COURSE BELONGS TO THE AUTH FORMATEUR.
            $relationship = [
                "from" => "formateurs",
                "join" => "formations",
                "on" => "id_formateur",
                "where" => [
                    "id_formation" => $search,
                    "formateurs->id_formateur" => session('user')->get()->id_formateur
                ]
            ];

            $validator->checkPermissions($relationship);

	    	$validator->validate([
	            'id_formation' => 'numeric|exists:formations',
	        ]);

            $langues = (new Langue)->all();
            $notifications = $this->_getNotifications();
            return view('videos/index', compact('notifications', 'langues', 'search'));
        }

        if(!is_null($search)){
            $formationModel = new Formation;
        	$formation = $formationModel->whereSlug($search);

	        if(!$formation){
	            return redirect('courses/search');
	        }

            $videoModel = new Video;
			$videos = $videoModel->getVideosOfFormationPublic($formation->id_formation);
            $inscriptionModel = new Inscription;
			$formation->inscriptions = $inscriptionModel->countApprenantsOfFormation($formation->id_formateur, $formation->id_formation);
            

            $previewModel = new Preview;
			$data = [
				'formation' => $formation,
				'videos' => $videos,
				'totalVideos' => count($videos),
				'previewVideo' => $previewModel->getPreviewVideo($formation->id_formation),
			];

			return view("courses/show", $data);
        }

        if(!auth()){
            return redirect('user/login');
        }

        if(session('user')->get()->type !== 'formateur'){
           return view('errors/page_404', [], 404);
        }

        if(!session('user')->get()->email_verified_at) {
            return redirect('user/verify');
        }

        if(!session('user')->get()->is_all_info_present){
			return redirect('user/continue');
		}

        $categorieModel = new Categorie;
        return view('formateurs/courses/index', ['categories' => $categorieModel->all(), 'notifications' => $this->_getNotifications()]);
    }

    public function add($request)
    {
        if($request->getMethod() !== 'GET'){
            return Response::json(null, 405, "Method Not Allowed");
        }

        if(!auth()){
            return redirect('user/login');
        }

        if(session('user')->get()->type !== 'formateur'){
           return Response::json(null, 403, "You don't have permission to access this resource."); 
        }

        if(!session('user')->get()->email_verified_at) {
            return redirect('user/verify');
        }

        if(!session('user')->get()->is_all_info_present){
			return redirect('user/continue');
		}

        $categorieModel = new Categorie;
        $langueModel = new Langue;
        $levelModel = new Level;

        $categories = $categorieModel->all();
        $langues = $langueModel->all();
        $niveaux = $levelModel->all();
        $notifications = $this->_getNotifications();

        return view("courses/add", compact('categories', 'niveaux', 'langues', 'notifications'));
    }

    public function edit($request, $id_formation = null)
    {
        if($request->getMethod() !== 'GET'){
            return Response::json(null, 405, "Method Not Allowed");
        }

        if(!auth()){
            return redirect('user/login');
        }

        if(session('user')->get()->type !== 'formateur'){
           return Response::json(null, 403, "You don't have permission to access this resource."); 
        }

        if(!session('user')->get()->email_verified_at) {
            return redirect('user/verify');
        }

        if(!session('user')->get()->is_all_info_present){
			return redirect('user/continue');
		}

        if(!$id_formation){
            return Response::json(null, 400, 'Bad Request');
        }

        $validator = new Validator([
            'id_formation' => strip_tags(trim($id_formation)),
        ]);

        // CHECK IF THIS COURSE BELONGS TO THE AUTH FORMATEUR.
        $relationship = [
            "from" => "formateurs",
            "join" => "formations",
            "on" => "id_formateur",
            "where" => [
                "id_formation" => $id_formation,
                "formateurs->id_formateur" => session('user')->get()->id_formateur
            ]
        ];

        $validator->checkPermissions($relationship);

        $validator->validate([
            'id_formation' => 'required|exists:formations',
        ]);

        $formationModel = new Formation;
        $formation = $formationModel->find($id_formation);

        $categorieModel = new Categorie;
        $langueModel = new Langue;
        $levelModel = new Level;

        $categories = $categorieModel->all();
        $langues = $langueModel->all();
        $niveaux = $levelModel->all();
        $notifications = $this->_getNotifications();


        return view('courses/edit', compact('formation', 'categories', 'niveaux', 'langues', 'notifications')); 
    }

    public function removeAttachedFile($request, $id_formation = null)
    {
        if($request->getMethod() !== 'DELETE'){
            return Response::json(null, 405, "Method Not Allowed");
        }

        if(!auth()){
            return Response::json(null, 401, "Unauthorized");
        }

        if(session('user')->get()->type !== 'formateur'){
           return Response::json(null, 403, "You don't have permission to access this resource."); 
        }

        if(!session('user')->get()->email_verified_at) {
            return redirect('user/verify');
        }

        if(!session('user')->get()->is_all_info_present){
			return redirect('user/continue');
		}

        if(!$id_formation){
            return Response::json(null, 400, 'Bad Request');
        }

        $validator = new Validator([
            'id_formation' => strip_tags(trim($id_formation)),
        ]);

        // CHECK IF THIS COURSE BELONGS TO THE AUTH FORMATEUR.
        $relationship = [
            "from" => "formateurs",
            "join" => "formations",
            "on" => "id_formateur",
            "where" => [
                "id_formation" => $id_formation,
                "formateurs->id_formateur" => session('user')->get()->id_formateur
            ]
        ];

        $validator->checkPermissions($relationship);

        $validator->validate([
            'id_formation' => 'required|exists:formations',
        ]);

        $formationModel = new Formation;
        $file = $formationModel->select($id_formation, ['fichier_attache']);
        if(!$file->fichier_attache){
            return Response::json(null, 404, 'File not found');
        }

        unlink('files/'.$file->fichier_attache);
        if($formationModel->setColumnToNull('fichier_attache', 'formations', 'id_formation', $id_formation)){
            return Response::json(null, 200, "Remove Successfully.");
        }
        return Response::json(null, 500, "Something went wrong.");
    }

    public function sortVideos($request, $id_formation = null)
    {
        if($request->getMethod() !== 'POST'){
            return Response::json(null, 405, "Method Not Allowed");
        }

        if(!auth()){
            return Response::json(null, 401, "Unauthorized");
        }

        if(session('user')->get()->type !== 'formateur'){
           return Response::json(null, 403, "You don't have permission to access this resource."); 
        }

        if(!session('user')->get()->email_verified_at) {
            return redirect('user/verify');
        }

        if(!session('user')->get()->is_all_info_present){
			return redirect('user/continue');
		}

        if(!$id_formation){
            return Response::json(null, 400, 'Bad Request');
        }

        $order = $request->post('order');
        $validator = new Validator([
            'order' => $order,
            'id_formation' => strip_tags(trim($id_formation)),
        ]);

        // CHECK IF THIS COURSE BELONGS TO THE AUTH FORMATEUR.
        $relationship = [
            "from" => "formateurs",
            "join" => "formations",
            "on" => "id_formateur",
            "where" => [
                "id_formation" => $id_formation,
                "formateurs->id_formateur" => session('user')->get()->id_formateur
            ]
        ];

        $validator->checkPermissions($relationship);

        $validator->validate([
            'order' => 'required|array',
            'id_formation' => 'required|exists:formations',
        ]);

        $this->_validateArray($order);

        $videoModel = new Video;
        $videoModel->sortVideos($order, $id_formation);

        return Response::json(null, 200, "Sorted Successfully.");
    }

    private function _validateArray($array) {
        foreach ($array as $video) {
            foreach ($video as $key => $value) {
                if (!is_numeric($value) || $key !== 'id') {
                    return Response::json(null, 400, "Bad Request");
                }
            }
        }
    }

    public function setVideoToPreview($request, $id_formation = null)
    {
        if($request->getMethod() !== 'POST'){
            return Response::json(null, 405, "Method Not Allowed");
        }

        if(!auth()){
            return Response::json(null, 401, "Unauthorized");
        }

        if(session('user')->get()->type !== 'formateur'){
           return Response::json(null, 403, "You don't have permission to access this resource."); 
        }
        
        if(!session('user')->get()->email_verified_at) {
            return redirect('user/verify');
        }

        if(!session('user')->get()->is_all_info_present){
			return redirect('user/continue');
		}

        if(!$id_formation){
            return Response::json(null, 400, 'Bad Request');
        }

        $id_video = $request->post('id_video');

        $validator = new Validator([
            'id_formation' => strip_tags(trim($id_formation)),
            'id_video' => strip_tags(trim($id_video))
        ]);

        // CHECK IF THIS COURSE BELONGS TO THE AUTH FORMATEUR.
        $relationship = [
            "from" => "formations",
            "join" => "formateurs",
            "on" => "id_formateur",
            "where" => [
                "id_formation" => $id_formation,
                "formateurs->id_formateur" => session('user')->get()->id_formateur
            ]
        ];

        $validator->checkPermissions($relationship);

        // CHECK IF THIS VIDEO BELONGS TO GIVING FORMATION ID AND THE AUTH FORMATEUR OWNED IT.
        $relationship = [
            "from" => "formations",
            "join" => "videos",
            "on" => "id_formation",
            "where" => [
                "id_video" => $id_video,
                "id_formateur" => session('user')->get()->id_formateur
            ]
        ];

        $validator->checkPermissions($relationship);

        $validator->validate([
            'id_formation' => 'required|numeric|exists:formations',
            'id_video' => 'required|numeric|exists:videos',
        ]);

        $previewModel = new Preview;
        if($previewModel->update($id_video, $id_formation)){
            return Response::json(null, 200, 'Updated Successfully.');
        }
        return Response::json(null, 500, "Coudn't update the preview, please try again later.");
    }

    private function _getNotifications()
	{
		$notificationModel = new Notification;
		return $notificationModel->whereRecipient(session('user')->get()->id_formateur);
	}

    public function toggleCanJoin($request, $id_formation)
    {
        if($request->getMethod() !== 'PUT'){
            return Response::json(null, 405, "Method Not Allowed");
        }

        if(!auth()){
            return Response::json(null, 401, "Unauthorized");
        }

        if(session('user')->get()->type !== 'formateur'){
           return Response::json(null, 403, "You don't have permission to access this resource."); 
        }
        
        if(!session('user')->get()->email_verified_at) {
            return Response::json(null, 403, "You don't have permission to access this resource.");
        }

        if(!session('user')->get()->is_all_info_present){
			return Response::json(null, 403, "You don't have permission to access this resource.");
		}

        if(!$id_formation){
            return Response::json(null, 400, 'Bad Request');
        }

        $validator = new Validator([
            'id_formation' => strip_tags(trim($id_formation)),
        ]);

        // CHECK IF THIS COURSE BELONGS TO THE AUTH FORMATEUR.
        $relationship = [
            "from" => "formations",
            "join" => "formateurs",
            "on" => "id_formateur",
            "where" => [
                "id_formation" => $id_formation,
                "formateurs->id_formateur" => session('user')->get()->id_formateur
            ]
        ];

        $validator->checkPermissions($relationship);

        $validator->validate([
            'id_formation' => 'required|numeric|exists:formations',
        ]);

        $formationModel = new Formation;
        $can_join = (bool) $formationModel->where(['can_join'], ['id_formation' => $id_formation])[0]->can_join;
        $formationModel->update(['can_join' => intval(!$can_join)], $id_formation);
        return Response::json(null, 204);
    }
}
