<?php

namespace App\Controllers\Api;

use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use Intervention\Image\ImageManagerStatic as Image;

use App\Models\Video;

use App\Libraries\{Response, Validator};
use App\Models\Subtitle;

class VideoController extends \App\Controllers\Api\ApiController
{
    private $videoModel;

    public function __construct()
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

        $this->videoModel = new Video;
        parent::__construct();
    }

	public function store($request)
    {
        // Check CSRF token
        if(!csrf_token($request->post('_token'))){
            return Response::json(null, 403, "Invalid Token");
        }

        $validator = new Validator([
            'nom' => strip_tags(trim($request->post('v_title'))),
            'description' => strip_tags($request->post("v_description")),
            'id_formation' => strip_tags(trim($request->post('id_formation'))),
            'url' => $request->file('lesson_video')
        ]);

        // CHECK IF THIS COURSE BELONGS TO THE AUTH FORMATEUR.
        $relationship = [
            "from" => "formateurs",
            "join" => "formations",
            "on" => "id_formateur",
            "where" => [
                "id_formation" => $request->post('id_formation'),
                "formateurs->id_formateur" => session('user')->get()->id_formateur
            ]
        ];

        $validator->checkPermissions($relationship);

        $validator->validate([
            'nom' => 'required|min:3|max:80',
            'description' => 'required|min:3|max:800',
            'id_formation' => 'required|exists:formations',
            'url' => 'required|size:1024|video|video_duration:50',
        ]);

        $video = $validator->validated();
        unset($video['type']);
        
        $video['url'] = uploader($request->file('lesson_video'), "videos");
        $video['thumbnail'] = $this->getThumbnail('videos/'.$video['url']);
        $id_video = $this->videoModel->create($video);
        $video = $this->videoModel->find($id_video);


        $langues = $request->post('langues');
        $subtitles = $request->file('subtitles');

        if($langues && $subtitles){
            if(!is_array($langues) || !is_array($subtitles)){
                return Response::json(null, 400, "Bad Request");
            }

            foreach($langues as $langue){
                $validator = new Validator([
                    'id_langue' => $langue
                ]);

                $validator->validate([
                    'id_langue' => 'numeric|exists:langues'
                ]);
            }

            $validator = new Validator([
                'subtitles' => $subtitles
            ]);

            $validator->validate([
                'subtitles' => 'file:application/octet-stream'
            ]);

            $subtitles = multiUploader($subtitles, 'subtitles');
            foreach ($subtitles as $key => $subtitle) {
                (new Subtitle)->create([
                    'source' => 'subtitles/'.$subtitle,
                    'id_video' => $video->id_video,
                    'id_langue' => $langues[$key]
                ]);
            }
        }

        return Response::json($video, 201, 'Created successfuly.');
    }

    public function update($request, $id)
    {
        // Check CSRF token
        if(!csrf_token($request->post('_token'))){
            return Response::json(null, 403, "Invalid Token");
        }
        
    	$validator = new Validator([
            'nom' => strip_tags(trim($request->post('v_title'))),
            'description' => strip_tags($request->post("v_description")),
            'id_video' => strip_tags(trim($id)),
        ]);

    	// CHECK IF THE VIDEO BELONGS TO GIVING FORMATION ID AND THE AUTH FORMATEUR OWNED IT.
        $relationship = [
            "from" => "formations",
            "join" => "videos",
            "on" => "id_formation",
            "where" => [
                "id_video" => $id,
                "id_formateur" => session('user')->get()->id_formateur
            ]
        ];

        $validator->checkPermissions($relationship);

        $validator->validate([
            'nom' => 'required|min:3|max:80',
            'description' => 'required|min:3|max:800',
            'id_video' => 'required|numeric|exists:videos',
        ]);

        $video = $validator->validated();
        unset($video['type']);

        // uploading video is optional, but if is set check validation. 
        if(isset($_FILES['lesson_video'])){
        	if($_FILES['lesson_video']['error'] === 0){
        		$validator = new Validator([
            		'url' => $_FILES['lesson_video']
        		]);

        		$validator->validate([
            		'url' => 'size:1024|video|video_duration:50',
        		]);

        		$video['url'] = uploader($_FILES['lesson_video'], "videos");
        		$video['thumbnail'] = $this->getThumbnail('videos/'.$video['url']);

        		$oldVideo = $this->videoModel->find($id);
        		// Remove old files (video and thumbnail)
        		unlink('videos/'.$oldVideo->url);
        		unlink('images/'.$oldVideo->thumbnail);
        		unset($oldVideo);
        	}
        }

        $langues = $request->post('langues');
        $subtitles = $request->file('subtitles');

        if($langues && $subtitles){
            if(!is_array($langues) || !is_array($subtitles)){
                return Response::json(null, 400, "Bad Request");
            }

            foreach($langues as $langue){
                $validator = new Validator([
                    'id_langue' => $langue
                ]);

                $validator->validate([
                    'id_langue' => 'numeric|exists:langues'
                ]);
            }

            $validator = new Validator([
                'subtitles' => $subtitles
            ]);

            $validator->validate([
                'subtitles' => 'file:application/octet-stream'
            ]);

            $subtitles = multiUploader($subtitles, 'subtitles');
            foreach ($subtitles as $key => $subtitle) {
                (new Subtitle)->create([
                    'source' => 'subtitles/'.$subtitle,
                    'id_video' => $id,
                    'id_langue' => $langues[$key]
                ]);
            }
        }
        
        $this->videoModel->update($video, $id);
        return Response::json($this->videoModel->find($id), 200, 'Updated successfuly.');
    }

    public function delete($id)
    {
    	if(!is_numeric($id)){
    		return Response::json(null, 400, "Bad Request");
    	}

    	$validator = new Validator(['id_video' => $id]);

    	// CHECK IF THE VIDEO BELONGS TO THE AUTH FORMATEUR.
        $relationship = [
            "from" => "formations",
            "join" => "videos",
            "on" => "id_formation",
            "where" => [
                "id_video" => $id,
                "id_formateur" => session('user')->get()->id_formateur
            ]
        ];

        $validator->checkPermissions($relationship);
        
        $video = $this->videoModel->find($id);

        if($this->videoModel->delete($id)){
        	unlink('videos/'.$video->url);
        	unlink('images/'.$video->thumbnail);
        	return Response::json(null, 200, 'Deleted successfuly.');
        }
        return Response::json(null, 500, "Coudn't delete the video, please try again later."); 
    }

    private function getThumbnail($videoPath)
    {
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => 'c:\ffmpeg\bin\ffmpeg.exe',
            'ffprobe.binaries' => 'c:\ffmpeg\bin\ffprobe.exe' 
        ]);

        $video = $ffmpeg->open($videoPath);
        $thumbnailPath = 'videos/'.generateUniqueName(uniqid()).'jpg';
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
}