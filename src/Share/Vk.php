<?php

namespace Share;

use Symfony\Component\HttpFoundation\Session\Session;

class Vk {

    CONST APP_ID                = "4905664";
    CONST API_KEY               = "VV1yDKwU0stQyhSYCZRL";
    CONST AUTH_CALLBACK_HOST    = "http://creed";
    CONST AUTH_CALLBACK_URL     = '/callback_auth?vk=true';

    function __construct()
    {
        $this->vk = new \VK\VK($this::APP_ID,$this::API_KEY);
    }

    public function createAuthorizeURL()
    {
        return $this->vk->getAuthorizeURL('wall',$this::AUTH_CALLBACK_HOST.$this::AUTH_CALLBACK_URL);
    }

    public function isAuthorized()
    {
        return isset($_SESSION['vk_token']) ? true : false;
    }

    public function getAndSetToken($code)
    {
        $token = $this->vk->getAccessToken($code,$this::AUTH_CALLBACK_HOST.$this::AUTH_CALLBACK_URL);
        $_SESSION['vk_token'] = $token;
    }



    public function post($image)
    {
        if($this->isAuthorized()){
            $this->vk->setAccessToken($_SESSION['vk_token']['access_token']);

            $response = $this->vk->api('wall.post',[
                'friends_only' => 0,
                'message' => '#ГотовБоротьсяЗа',
                'attachments' => ''
            ]);

            die;
        }

    }




}