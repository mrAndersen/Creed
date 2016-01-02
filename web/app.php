<?php

require_once('../vendor/autoload.php');

CONST CONSUMER_KEY              = 'huuk0VGzJfY5bSOWwCPS0qGrp';
CONST CONSUMER_SECRET           = 'db3FSPFqMT8ckyCXhLtxd9pvEcN3lsUud0I9mGudfakVumNIWC';
CONST TWITTER_OAUTH_ENDPOINT    = 'http://creedifightfor.warner-films.ru//shareTwitter';

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

$app->post('/sendVkImage',function(){
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

    $imageURL   = $request->request->get('imageURL');
    $postURL    = $request->request->get('postURL');

    $client = new \GuzzleHttp\Client();

    $response = $client->request('POST',$postURL,[
        'multipart' => [
            [
                'name' => 'photo',
                'contents' => fopen($imageURL,'r')
            ]
        ]
    ]);

    $response = new \Symfony\Component\HttpFoundation\JsonResponse(json_decode($response->getBody()->__toString(),true));
    $response->send();
});

$app->post('/postImage',function(){
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

    $photo  = $request->request->get('photo');

    $helper  = new \Helper\Helper();
    $name   = $helper->convertBase64ToTmpFile($photo);
    $url = $request->getScheme() . '://' . $request->getHost() . '/share/'. $name;

    $response = new \Symfony\Component\HttpFoundation\JsonResponse(['url' => $url]);
    $response->send();
});

$app->post('/authTwitter',function(){
    session_start();
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

    $_SESSION['image'] = $request->request->get('photo');
    $_SESSION['text']  = $request->request->get('text');

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

    $_SESSION['oauth_token']    = $request->query->get('oauth_token');
    $_SESSION['oauth_verifier'] = $request->query->get('oauth_verifier');

    $connection = new \Abraham\TwitterOAuth\TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET,$_SESSION['oauth_token'],$_SESSION['oauth_token_secret']);
    $access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_SESSION['oauth_verifier']));

    $_SESSION['oauth_token']        = $access_token['oauth_token'];
    $_SESSION['oauth_token_secret'] = $access_token['oauth_token_secret'];
    $_SESSION['twitter_user_id']    = $access_token['user_id'];

    $connection = new \Abraham\TwitterOAuth\TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET,$_SESSION['oauth_token'],$_SESSION['oauth_token_secret']);
    $imageURL = $_SESSION['image'];

    $photo = $connection->upload('media/upload',['media' => $imageURL]);

    $statues = $connection->post("statuses/update", [
        'status' => $_SESSION['text'],
        'media_ids' => $photo->media_id
    ]);

    $response = new \Symfony\Component\HttpFoundation\RedirectResponse('https://twitter.com');
    $response->send();
});

$app->run();

