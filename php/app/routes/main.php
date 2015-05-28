<?php

use Forio\Model\User as User;
use Forio\Model\Project as Project;
use Forio\Model\Experience as Experience;
use Forio\Model\Education as Education;
use Forio\Model\Image as Image;

/**
 * Homepage
 */
$app->get('/', function() use ($app) {
    $theme = $app->config('application')['theme']['name'];
    $user = User::orderBy('id', 'desc')->first(['firstname', 'lastname', 'title', 'profile_pic']);
    if (is_null($user)) {
        $app->redirect('/register');
    }
    $projects = Project::with('images')->get();
    $data = array(
        'projects' => $projects,
        'profile'  => $user->toArray()
    );
    $app->render($theme . '/index.html.twig', $data);
});

/**
 * Project page 
 */
$app->get('/projects/:slug', function($slug) use ($app) {
    $theme = $app->config('application')['theme']['name'];
    $user = User::orderBy('id', 'desc')->first(['firstname', 'lastname', 'title', 'profile_pic']);
    if (is_null($user)) {
        $app->redirect('/create-account');
    }
    $project = Project::where('slug', $slug)->first();
    $data = array(
        'project' => $project,
        'profile' => $user->toArray()
    );
    $app->render($theme . '/project.html.twig', $data);
});

/**
 * About page
 */
$app->get('/about', function() use ($app) {
    $theme = $app->config('application')['theme']['name'];
    $user = User::orderBy('id', 'desc')->first(['firstname', 'lastname', 'title', 'email', 'location', 'bio', 'clients', 'profile_pic']);
    if (is_null($user)) {
        $app->redirect('/create-account');
    }
    $experiences = Experience::all();
    $educations = Education::all();
    $data = array(
        'profile'     => $user->toArray(),
        'experiences' => $experiences->toArray(),
        'educations'  => $educations->toArray()
    );
    $app->render($theme . '/about.html.twig', $data);
});

$app->get('/image/:id', function($id) use ($app) {
    $image = Image::find($id);
    if (is_null($image)) {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'Image does not exist.'
        )));
    } else {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => true,
            'data'    => $image->toArray()
        )));
    }
});

/**
 * Login page
 */
$app->get('/login', function() use ($app) {
    $theme = $app->config('application')['theme']['name'];
    if (User::isLoggedIn()) {
        $app->redirect('/admin/projects');
    } else {
        $app->render($theme . '/login.html.twig');
    }
});

/**
 * Create account page
 */
$app->get('/register', function() use ($app) {
    $theme = $app->config('application')['theme']['name'];
    $user = User::orderBy('id', 'desc')->first(['firstname', 'lastname', 'title', 'profile_pic']);
    if (is_null($user)) {
        $app->render($theme . '/account.html.twig');
    } else {
        $app->redirect('/');
    }
});

/**
 * Process request for creating new account.
 */
$app->post('/account/create', function() use ($app) {
    $user = User::orderBy('id', 'desc')->first(['firstname', 'lastname', 'title', 'profile_pic']);
    if (is_null($user)) {
        $user = new User();
        $user->firstname = ucfirst($app->request->post('firstname'));
        $user->lastname = ucfirst($app->request->post('lastname'));
        $user->title = ucfirst($app->request->post('title'));
        $user->email = $app->request->post('email');
        $user->password = password_hash($app->request->post('password'), PASSWORD_DEFAULT);
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();
    }

    echo 'Hello';die;
});

/**
 * Route for creating a user.
 */
$app->get('/create-test-user/:email/:password', function($email, $password) use ($app) {
    $user = new User();
    $user->email = $email;
    $user->password = password_hash($password, PASSWORD_DEFAULT);
    $user->last_login = date('Y-m-d H:i:s');
    $user->save();
});