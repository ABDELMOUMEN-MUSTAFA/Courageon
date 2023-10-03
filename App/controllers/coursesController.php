<?php

namespace App\Controllers;

use App\Models\{
    Stocked,
    Preview,
    Formation,
    Video,
    Inscription
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

            $formationRelationships = ['videos'];
            if(!in_array($relationship, $formationRelationships)){
                return view('errors/page_404', [], 404);
            }

            $validator = new Validator(['id_formation' => $search]);

	    	$validator->validate([
	            'id_formation' => 'numeric|exists:formations|check:formations',
	        ]);


            $videoModel = new Video;
            $videos = $videoModel->getVideosOfFormation($search);
            return view('videos/index', compact('videos'));
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

        $stockedModel = new Stocked;
        return view('formateurs/courses/index', ['categories' => $stockedModel->getAllCategories()]);
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

        $stockedModel = new Stocked;
        $categories = $stockedModel->getAllCategories();
        $niveaux = $stockedModel->getAllLevels();
        $langues = $stockedModel->getAllLangues();

        return view("courses/add", compact('categories', 'niveaux', 'langues'));
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

        $validator = new Validator([
            'id_formation' => strip_tags(trim($id_formation)),
        ]);

        $validator->validate([
            'id_formation' => 'required|exists:formations|check:formations',
        ]);

        $formationModel = new Formation;
        $stockedModel = new Stocked;

        $formation = $formationModel->find($id_formation);
        $categories = $stockedModel->getAllCategories();
        $niveaux = $stockedModel->getAllLevels();
        $langues = $stockedModel->getAllLangues();

        return view('courses/edit', compact('formation', 'categories', 'niveaux', 'langues')); 
    }

    public function removeAttachedFile($request, $id_formation)
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

        $validator = new Validator([
            'id_formation' => strip_tags(trim($id_formation)),
        ]);

        $validator->validate([
            'id_formation' => 'required|exists:formations|check:formations',
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

        $order = $request->post('order');
        $validator = new Validator([
            'order' => $order,
            'id_formation' => strip_tags(trim($id_formation)),
        ]);

        $validator->validate([
            'order' => 'required|array',
            'id_formation' => 'required|exists:formations|check:formations',
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

        $id_video = $request->post('id_video');
        $validator = new Validator([
            'id_formation' => strip_tags(trim($id_formation)),
            'id_video' => strip_tags(trim($id_video))
        ]);

        $validator->validate([
            'id_formation' => 'required|numeric|exists:formations|check:formations',
            'id_video' => 'required|numeric|exists:videos',
        ]);

        // CHECK IF THE VIDEO BELONGS TO GIVING FORMATION ID AND THE AUTH FORMATEUR OWNED IT.
        $relationship = [
            "from" => "formations",
            "join" => "videos",
            "using" => "id_formation",
            "where" => [
                "id_video" => $id_video,
                "id_formateur" => session('user')->get()->id_formateur
            ]
        ];

        $validator->checkPermissions($relationship);

        $previewModel = new Preview;
        if($previewModel->update($id_video, $id_formation)){
            return Response::json(null, 200, 'Updated Successfully.');
        }
        return Response::json(null, 500, "Coudn't update the preview, please try again later.");
    }
}
