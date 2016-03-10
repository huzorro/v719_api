<?php
require './vendor/autoload.php';
$config = include '../Application/Common/Conf/config.php';
$dsn = "$config[DB_TYPE]:dbname=$config[DB_NAME];host=$config[DB_HOST];charset=utf8";

$pdo = new PDO($dsn, $config['DB_USER'], $config['DB_PWD']);
$db = new NotORM($pdo);
// $client = new GuzzleHttp\Client();
$app = new \Slim\Slim();
$app->hook('slim.before', function () use ($app) {
    $app->log->setWriter(new \Slim\Logger\DateTimeFileWriter());
    $app->response()->header("Access-Control-Allow-Origin", "*");       
});
$app->log->setEnabled(true);
$app->log->setLevel(\Slim\Log::DEBUG);
$app->view(new \JsonApiView("data"));
$app->add(new \JsonApiMiddleware());
$app->get('/', function() use ($app) {
    $app->render(200);
});
$app->get('/category:_', function() use ($app, $db) {
   $categorys = array();
   foreach ($db->v_category() as $category) {
       # code...
       $categorys[] = array(
         "id" => $category["id"],
         "name" => $category["name"],
         "title" => $category["title"],
         "model" => $category["model"]         
       );
   }
//    $app->response()->header("Content-Type", "application/json");
//    echo json_encode($categorys);  
    $app->render(200, $categorys);
});

$app->get('/member:_', function() use ($app, $db) {
    $members = array();
    foreach ($db->v_member as $member) {
        # code...
        $members[] = array(
            "uid" => $member["uid"],
            "nickname" => $member["nickname"]
        );
    }
    
    $app->render(200, $members);
});

$app->post('/document/article', function() use($app, $db) {
    $body = $app->request()->getBody();
    $app->log->debug($body);
    $data = json_decode($body, true);   
    //v_document
    //uid, title, category_id, group_id, model_id, position, display, status         
    $result = $db->v_document->insert($data["document"]);
    $app->log->debug(json_encode($data["document"]));
    $app->log->debug(json_encode($result));
    $data["article"]["id"] = $result["id"];
    $app->log->debug($result["id"]);
    //v_document_article
    //id, content
    $db->v_document_article->insert($data["article"]);
    $app->log->debug(json_encode($data["article"]));
    $app->render(200);        
});

$app->run();