<?php

require_once('../vendor/autoload.php');

CONST CONSUMER_KEY              = 'huuk0VGzJfY5bSOWwCPS0qGrp';
CONST CONSUMER_SECRET           = 'db3FSPFqMT8ckyCXhLtxd9pvEcN3lsUud0I9mGudfakVumNIWC';
CONST TWITTER_OAUTH_ENDPOINT    = 'http://creed.unrelcode.ru/shareTwitter';

$app = new \Slim\Slim(['debug' => true]);

$app->get('/', function(){
    session_cache_limiter(false);
    session_start();

    $loader     = new Twig_Loader_Filesystem('../src/views');
    $twig       = new Twig_Environment($loader);
    $view       =  $twig->render('index.html.twig');

    $response = new \Symfony\Component\HttpFoundation\Response($view);
    $response->send();
});

$app->post('/',function(){
    session_start();
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

    $image = $request->request->get('image',null);
    $type  = $request->request->get('type',null);

    $result = [];

    if($type == 'vk'){
        $vk = new \Share\Vk();
        if($vk->isAuthorized()){
            $vk->post($image);
        }else{
            $authURL = $vk->createAuthorizeURL();
            $result['auth'] = $authURL;
        }
    }

    $response = new \Symfony\Component\HttpFoundation\JsonResponse($result);
    $response->send();
});

$app->get('/callback_auth',function() use($app){
    session_start();
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    $vk      = $request->get('vk',null);

    if($vk){
        $vk = new \Share\Vk();
        $vk->getAndSetToken($request->get('code'));



    }

});










$app->post('/vkPostImage',function(){
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

    $vkPostUrl  = $request->request->get('vk_post_url',null);
    $photo      = $request->request->get('photo',null);
    $photo      = base64_decode(explode(',',$photo)[1]);
    $photo      = imagecreatefromstring($photo);

    $name       = time().mt_rand(0,999).'.png';
    $photo      = imagepng($photo,$name);

    $client = new \GuzzleHttp\Client();
    $response = $client->request('POST',$vkPostUrl,[
        'multipart' => [
            [
                'name' => 'photo',
                'contents' => '@'.$name
            ]
        ]
    ]);

    $response = new \Symfony\Component\HttpFoundation\JsonResponse(json_decode($response->getBody()->__toString(),true));
    $response->send();
});


$app->post('/shareTwitterAuth',function(){
    session_start();
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

    $_SESSION['image'] = $request->request->get('photo');

    $connection = new \Abraham\TwitterOAuth\TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
    $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => TWITTER_OAUTH_ENDPOINT));

    $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
    $authURL = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

    $response = new \Symfony\Component\HttpFoundation\JsonResponse(['url' => $authURL]);
    $response->send();
});


$app->get('/shareTwitter',function(){
    session_start();
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    $helper  = new \Helper\Helper();

    $_SESSION['oauth_token']    = $request->query->get('oauth_token');
    $_SESSION['oauth_verifier'] = $request->query->get('oauth_verifier');

    $connection = new \Abraham\TwitterOAuth\TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET,$_SESSION['oauth_token'],$_SESSION['oauth_token_secret']);
    $access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_SESSION['oauth_verifier']));

    $_SESSION['oauth_token']        = $access_token['oauth_token'];
    $_SESSION['oauth_token_secret'] = $access_token['oauth_token_secret'];
    $_SESSION['twitter_user_id']    = $access_token['user_id'];

    $connection = new \Abraham\TwitterOAuth\TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET,$_SESSION['oauth_token'],$_SESSION['oauth_token_secret']);

    $name = $helper->convertBase64ToTmpFile($_SESSION['image']);
    $photo = $connection->upload('media/upload',['media' => $name]);
    $helper->removeTmpFile($name);

    $statues = $connection->post("statuses/update", [
        'status' => '#ГотовБоротьсяЗа #Creed',
        'media_ids' => $photo->media_id
    ]);

    $response = new \Symfony\Component\HttpFoundation\RedirectResponse('https://twitter.com');
    $response->send();
});














$app->run();

