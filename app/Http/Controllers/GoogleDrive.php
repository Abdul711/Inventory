<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Support\Facades\File;

class GoogleDrive extends Controller
{
    public function uploadImage (){
       set_time_limit(10000);
$client = new Client();

$client->setAuthConfig(
    storage_path('app/google/service-account.json')
);

$client->addScope(Drive::DRIVE_READONLY);

$drive = new Drive($client);

$folderId = '14CCLmou8h6twlzXzaSIkWlA2I2VHTXQV';
// https://drive.google.com/drive/folders/?usp=drive_link
$directory = public_path('female_employee');

// if (!File::exists($directory)) {
//     File::makeDirectory($directory, 0755, true);
// }
$credentials = json_decode(
    file_get_contents(storage_path('app/google/service-account.json')),
    true
);


$files = $drive->files->listFiles([
    'q' => "'{$folderId}' in parents and trashed = false",
    'fields' => 'files(id,name,mimeType)',
]);
// dd(
//     collect($files->getFiles())->map(function ($file) {
//         return [
//             'id' => $file->getId(),
//             'name' => $file->getName(),
//             'mimeType' => $file->getMimeType(),
//         ];
//     })
// );


foreach ($files->getFiles() as $file) {

    if (!str_starts_with($file->getMimeType(), 'image/')) {
        continue;
    }

    $response = $drive->files->get(
        $file->getId(),
        [
            'alt' => 'media',
        ]
    );

    $content = $response->getBody()->getContents();

    File::put(
        public_path(
            'female_employee/' . $file->getName()
        ),
        $content
    );

    echo "Downloaded: " . $file->getName() . PHP_EOL;
     }       
    }
}