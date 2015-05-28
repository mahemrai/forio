<?php
//main app file
require 'vendor/autoload.php';
require 'config/database.php';

//slim app configuration
$app = new \Slim\Slim(array(
    'mode'           => 'development',
    'templates.path' => 'app/views',
    'view'           => new \Slim\Views\Twig(),
));

//load configuration from YAML file if it exists
if (file_exists('config/app.yml')) {
    \BurningDiode\Slim\Config\Yaml::getInstance()->addFile('config/app.yml');
    $app->config('application');
}

//development environment
$app->configureMode('development', function() use ($app) {
    $app->config(array(
        'debug' => true
    ));

    $theme = $app->config('application')['theme']['name'];
    
    Less_Autoloader::register();
    $parser = new Less_Parser();
    $parser->parseFile('public/less/admin.less', 'css');
    $css = $parser->getCss();
    file_put_contents('public/css/admin.css', $css);

    $parser = new Less_Parser();
    $parser->parseFile('public/less/' . $theme . '.less', 'css');
    $css = $parser->getCss();
    file_put_contents('public/css/' . $theme . '.css', $css);
});

//view configuration
$view = $app->view();
$view->parserOptions = array(
    'debug'            => true,
    'charset'          => 'utf-8',
    'cache'            => realpath('/app/views/cache'),
    'auto_reload'      => true,
    'strict_variables' => false,
    'autoescape'       => true
);

//load twig extensions
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
    new Twig_Extension_Debug()
);

//main application routes
require 'app/routes/main.php';

//admin routes
require 'app/routes/admin.php';

//run app
$app->run();