<?php

namespace Helper;

class Helper {


    public function convertBase64ToTmpFile($rawBase64)
    {
        $photo      = base64_decode(explode(',',$rawBase64)[1]);
        $photo      = imagecreatefromstring($photo);

        $name       = time().mt_rand(0,999).'.png';
        $photo      = imagepng($photo,$name);

        return $name;
    }

    public function removeTmpFile($name)
    {
        return unlink($name);
    }

}