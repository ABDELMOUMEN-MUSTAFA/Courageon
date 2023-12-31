<?php

namespace App\Controllers\Api;

use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use Intervention\Image\ImageManagerStatic as Image;

use App\Models\{
    Formation,
    Video,
    Preview
};

use App\Libraries\{Response, Validator};

class CourseController extends \App\Controllers\Api\ApiController
{
    private $formationModel;

    public function __construct()
    {
        $this->formationModel = new Formation;
        parent::__construct();
    }

	public function index($request)
    {
        if($request->get('page_for') !== 'formateur' || !auth()){
            return Response::json($this->publicCourses($request));    
        }

        if(session('user')->get()->type !== 'formateur'){
           return Response::json(null, 403); 
        }

        return Response::json($this->formateurCourses($request));
    }

    private function publicCourses($request)
    {
        $filters = [
            'fore.nom' => htmlspecialchars(strip_tags($request->get('q'))),
            'c.nom' => htmlspecialchars(strip_tags($request->get('categorie'))),
            'l.nom' => htmlspecialchars(strip_tags($request->get('langue'))),
            'n.nom' => htmlspecialchars(strip_tags($request->get('niveau'))),
        ];

        $filters = array_filter($filters);

        $filterQuery = "fore.etat = 'public' ";
        foreach ($filters as $key => $value) {
            $filterQuery .= "AND {$key} LIKE '%{$value}%' ";   
        }

        $duration = htmlspecialchars(strip_tags($request->get('duration')));
        $durations = [
            'extraShort' => "'00' AND '01:00:59'",
            'short' => "'01:00:00' AND '03:00:59'", 
            'medium' => "'03:00:00' AND '06:00:59'",
            'long' => "'06:00:00' AND '17:00:59'",
            'extraLong' => "'17:00:00' AND '800:00'"
        ];

        if(array_key_exists($duration, $durations)){
            $filterQuery .= 'AND mass_horaire BETWEEN ' . $durations[$duration];
        }

        $sort = htmlspecialchars(strip_tags($request->get('sort')));
        $sorts = ['newest' => 'fore.date_creation', 'mostLiked' => 'fore.jaimes'];
        $sort = array_key_exists($sort, $sorts) ? $sorts[$sort] : 'insc.total_inscriptions';

        $totalFiltred = $this->formationModel->countFiltred($filterQuery);
        return paginator($totalFiltred, 10, 'courses', $this->formationModel, 'filter', [
            'sort' => $sort,
            'filter' => $filterQuery,
        ]);
    }

    private function formateurCourses($request)
    {
        $filters = [
            'fore.nom' => htmlspecialchars(strip_tags($request->get('q'))),
            'c.nom' => htmlspecialchars(strip_tags($request->get('categorie'))),
        ];

        $filters = array_filter($filters);

        $filterQuery = 'fore.etat IS NOT NULL ';
        foreach ($filters as $key => $value) {
            $filterQuery .= "AND {$key} LIKE '%{$value}%' ";   
        }

        $sort = htmlspecialchars(strip_tags($request->get('sort')));
        $sorts = ['nom' => 'fore.nom', 'likes' => 'fore.jaimes', 'newest' => 'fore.date_creation'];
        $sort = array_key_exists($sort, $sorts) ? $sorts[$sort] : 'insc.total_inscriptions';

        $id_formateur = 'AND f.id_formateur = "'.session('user')->get()->id_formateur.'"';
        $totalFiltred = $this->formationModel->countFiltred($filterQuery, $id_formateur);
        
        return paginator($totalFiltred, 10, 'courses', $this->formationModel, 'filter', [
            'sort' => $sort,
            'filter' => $filterQuery,
            'id_formateur' => $id_formateur
        ]);
    }

    public function store($request)
    {
        if(!auth()){
            return Response::json(null, 401);
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

        // Check CSRF token (pass true to trigger the checker if no token provided)
        if(!csrf_token($request->post('_token'))){
            return Response::json(null, 403, "Invalid Token");
        }

        $validator = new Validator([
            'nom' => strip_tags(trim($request->post('nom'))),
            'description' => $this->_strip_critical_tags($request->post("description")),
            'prix' => strip_tags(trim($request->post('prix'))),
            'etat' => strip_tags(trim($request->post('etat'))),
            'id_categorie' => strip_tags(trim($request->post('id_categorie'))),
            'id_niveau' => strip_tags(trim($request->post('id_niveau'))),
            'id_langue' => strip_tags(trim($request->post('id_langue'))),
            'preview' => $request->file('preview'),
            'image' => $request->file('image')
        ]);

        $validator->validate([
            'nom' => 'required|min:3|max:80',
            'description' => 'required|min:15|max:700',
            'prix' => 'required|numeric|numeric_min:10|numeric_max:1000',
            'etat' => 'required|in_array:public,private',
            'id_categorie' => 'required|exists:categories',
            'id_niveau' => 'required|exists:niveaux',
            'id_langue' => 'required|exists:langues',
            'preview' => 'required|size:1024|video|video_duration:50',
            'image' => 'required|size:5|image'
        ]);

        $formation = $validator->validated();
        unset($formation['type']);
        $formation['image'] = uploader($request->file('image'), 'images/formations');
        $formation['masse_horaire'] = $formation['duration'];
        $formation['id_formateur'] = session('user')->get()->id_formateur;
        
        // background
        if($request->file('background')){
            unset($validator);

            $validator = new Validator([
                'background_img' => $request->file("background"),
            ]);

            $validator->validate([
                'background_img' => 'size:10|image',
            ]);

            $formation['background_img'] = uploader($request->file("background"), 'images/formations');
        }

        // attached file
        if($request->file('attached')){
            unset($validator);

            $validator = new Validator([
                'fichier_attache' => $request->file("attached"),
            ]);

            $validator->validate([
                'fichier_attache' => 'size:50|file:application/zip',
            ]);

            $formation['fichier_attache'] = uploader($request->file("attached"), 'files/formations');
        }

        // Create formation
        $id_formation = $this->formationModel->create($formation);

        // Create Video
        $video = [];
        $video['id_formation'] = $id_formation;
        $preview_name = strip_tags(trim($request->post('preview_name')));

        if(strlen($preview_name) === 0 || strlen($preview_name) > 80){
            $video['nom'] = $request->file('image')['name'];
        }else{
            $video['nom'] = $preview_name;
        }

        $video['url'] = uploader($request->file('preview'), "videos");
        $video['duration'] = $formation['duration'];


        $video['thumbnail'] = $this->_getThumbnail('videos/'.$video['url']);
        // $video['thumbnail'] = "dsqdqsdsqdqsd";
        $videoModel = new Video;
        
        $id_video = $videoModel->create($video);

        
        // Create Preview
        $previewModel = new Preview;
        $previewModel->insertPreviewVideo($id_video, $id_formation);
        
        return Response::json(['id_formation' => $id_formation], 201);
    }

    public function update($request, $id_formation)
    {
        if(!auth()){
            return Response::json(null, 401);
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

        // Check CSRF token
        if(!csrf_token($request->post('_token'))){
            return Response::json(null, 403, "Invalid Token");
        }
        
        $validator = new Validator([
            'id_formation' => strip_tags(trim($id_formation)),
            'nom' => strip_tags(trim($request->post('nom'))),
            'description' => $this->_strip_critical_tags($request->post("description")),
            'prix' => strip_tags(trim($request->post('prix'))),
            'etat' => strip_tags(trim($request->post('etat'))),
            'id_categorie' => strip_tags(trim($request->post('id_categorie'))),
            'id_niveau' => strip_tags(trim($request->post('id_niveau'))),
            'id_langue' => strip_tags(trim($request->post('id_langue'))),
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
            'nom' => 'required|min:3|max:80',
            'description' => 'required|min:15|max:700',
            'prix' => 'required|numeric|numeric_min:10|numeric_max:1000',
            'etat' => 'required|in_array:public,private',
            'id_categorie' => 'required|exists:categories',
            'id_niveau' => 'required|exists:niveaux',
            'id_langue' => 'required|exists:langues',
        ]);

        $formation = $validator->validated();
        unset($formation['type']);
        $video = [];

        // update background
        if($request->file('background')){
            unset($validator);

            $validator = new Validator([
                'background_img' => $request->file("background"),
            ]);

            $validator->validate([
                'background_img' => 'size:10|image',
            ]);

            $oldBackground = $this->formationModel->select($id_formation, ['background_img']);
            if($oldBackground->background_img){
                unlink('images/'.$oldBackground->background_img);
            }
            $formation['background_img'] = uploader($request->file("background"), 'images/formations');
        }

        // update attached file
        if($request->file('attached')){
            unset($validator);

            $validator = new Validator([
                'fichier_attache' => $request->file("attached"),
            ]);

            $validator->validate([
                'fichier_attache' => 'size:50|file:application/zip',
            ]);

            $oldFile = $this->formationModel->select($id_formation, ['fichier_attache']);
            if($oldFile->fichier_attache){
                unlink('files/'.$oldFile->fichier_attache);
            }
            $formation['fichier_attache'] = uploader($request->file("attached"), 'files/formations');
        }

        // update preview
        if($request->file('preview')){
            unset($validator);

            $validator = new Validator([
                'preview' => $request->file('preview')
            ]);

            $validator->validate([
                'preview' => 'size:1024|video|video_duration:50',
            ]);

            $video['url'] = uploader($request->file('preview'), "videos/formations");
            $video['thumbnail'] = $this->_getThumbnail('videos/'.$video['url']);

            $previewModel = new Preview;
            $videoModel = new Video;
            $id_video = $previewModel->getPreviewOfFormation($id_formation)->id_video;
            $oldVideo = $videoModel->find($id_video);
            // Remove old files (video and thumbnail)
            unlink('videos/'.$oldVideo->url);
            unlink('images/'.$oldVideo->thumbnail);
            unset($oldVideo);
            $videoModel->update($video, $id_video);
        }

        // update image
        if($request->file('image')){
            unset($validator);

            $validator = new Validator([
                'image' => $request->file('image')
            ]);

            $validator->validate([
                'image' => 'required|size:5|image',
            ]);

            unlink('images/'.$this->formationModel->select($id_formation, ['image'])->image);
            $formation['image'] = uploader($request->file('image'), 'images/formations');
        }

        // update formation
        if($this->formationModel->update($formation, $id_formation)){
            return Response::json(null, 200, 'Updated successfuly.');
        }
        return Response::json(null, 500, "Coudn't update the course, please try again later."); 
    }

    public function delete($id_formation)
    {
        if(!auth()){
            return Response::json(null, 401);
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

        if($this->formationModel->delete($id_formation)){
            return Response::json(null, 200, 'Deleted successfuly.');
        }
        return Response::json(null, 500, "Coudn't delete the course, please try again later."); 
    }

    private function _strip_critical_tags($text)
    {
        $dom = new \DOMDocument();
        $dom->loadHTML($text ?: "N");
        $tags_to_remove = ['script', 'style', 'iframe', 'link', 'video', 'img'];
        foreach($tags_to_remove as $tag){
            $element = $dom->getElementsByTagName($tag);
            foreach($element as $item){
                $item->parentNode->removeChild($item);
            }
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        $cleanedHtml = '';

        if ($body) {
            foreach ($body->childNodes as $childNode) {
                $cleanedHtml .= $dom->saveHTML($childNode);
            }
        }
        return $cleanedHtml; 
    }

    private function _getThumbnail($video)
    {
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => 'c:\ffmpeg\bin\ffmpeg.exe',
            'ffprobe.binaries' => 'c:\ffmpeg\bin\ffprobe.exe',
        ]);

        $video = $ffmpeg->open($video);

        $thumbnailPath = 'videos/'.generateUniqueName(uniqid()).'.jpg';
        $frame = $video->frame(TimeCode::fromSeconds(5))->save('images/'.$thumbnailPath);

        $img = Image::make('images/'.$thumbnailPath);

        // resize image instance
        $img->resize(500, 500, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        imagejpeg($img->getCore(), 'images/'.$thumbnailPath, 50);
        return $thumbnailPath;
    }

    public function show($request, $id)
    {
        if(!auth()){
            return Response::json(null, 401);
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
            'id_formation' => strip_tags(trim($id)),
        ]);

        // CHECK IF THIS COURSE BELONGS TO THE AUTH FORMATEUR.
        $relationship = [
            "from" => "formateurs",
            "join" => "formations",
            "on" => "id_formateur",
            "where" => [
                "id_formation" => $id,
                "formateurs->id_formateur" => session('user')->get()->id_formateur
            ]
        ];

        $validator->checkPermissions($relationship);

        $childTables = ['videos'];
        $childTable = $request->getUri()[3] ?? null;
        if(in_array($childTable, $childTables)){
            switch ($childTable) {
                case 'videos':
                    $numberVideos = (new Video)->countVideosOfFormation($id);
                    $videos = paginator($numberVideos, 10, 'videos', $this->formationModel, 'videos', [
                        'idFormation' => $id,
                    ]);
                    return Response::json($videos);
                    break;
                
                default:
                    return Response::json(null, 404, "Not Found");
                    break;
            }
        }
    }
}