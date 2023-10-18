<?php

function generateUniqueName($originalName) {
	$extension = pathinfo($originalName, PATHINFO_EXTENSION);
	$timestamp = time();
	$randomString = bin2hex(random_bytes(8)); 

	return $timestamp . '_' . $randomString . '.' . $extension;
}

function uploader($file, $destinationPath) {
    $originalName = $file['name'];
    $uniqueFileName = generateUniqueName($originalName);
    $uploadedFilePath = $destinationPath . '/' . $uniqueFileName;
    $prefixToRemove = explode('/', $destinationPath)[0].'/';

    if (move_uploaded_file($file['tmp_name'], $uploadedFilePath)) {
        return str_replace($prefixToRemove, '', $uploadedFilePath);
    }
    return null;
}

function multiUploader($files, $destinationPath) {
    $paths = [];

    for ($i=0; $i < count($files['name']); $i++) { 
        $originalName = $files['name'][$i];
        $uniqueFileName = generateUniqueName($originalName);
        $uploadedFilePath = $destinationPath . '/' . $uniqueFileName;
        $prefixToRemove = explode('/', $destinationPath)[0].'/';

        if (move_uploaded_file($files['tmp_name'][$i], $uploadedFilePath)) {
            array_push($paths, str_replace($prefixToRemove, '', $uploadedFilePath));
        }
    }

    return $paths;
}