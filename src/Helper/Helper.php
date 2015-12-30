<?php

namespace Helper;

use Intervention\Image\ImageManagerStatic;

class Helper {
    public function convertBase64ToTmpFile($rawBase64)
    {
        $name       = time().'_'.mt_rand(0,999999999).'.png';
        $photo      = ImageManagerStatic::make($rawBase64);

        $photo->widen($photo->width() / 1.5);
        $photo->encode('png');
        $photo->save('share/'.$name);
        return $name;
    }

    public function removeTmpFile($name)
    {
        $result = false;
        if(file_exists($name)){
            $result = unlink($name);
        }

        if(isset($_SESSION['image'])){
            unset($_SESSION['image']);
        }

        return $result;
    }

}