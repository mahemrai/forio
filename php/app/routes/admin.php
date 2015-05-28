<?php
use Forio\Model\User as User;
use Forio\Model\Image as Image;
use Forio\Model\Project as Project;
use Forio\Model\Experience as Experience;
use Forio\Model\Education as Education;
use Forio\Model\Keyword as Keyword;

/**
 * This file contains logic for all the admin related routes.
 * @author Mahendra Rai
 */

/**
 * Admin dashboard page
 */
$app->get('/dashboard', function() use ($app) {
    if (User::isLoggedIn()) {
        $user = User::find(User::getId());
        $project = Project::orderBy('created_at', 'desc')->first();
        $experience = Experience::orderBy('created_at', 'desc')->first();
        $education = Education::orderBy('created_at', 'desc')->first();
        $data = array(
            'project'     => $project,
            'experience'  => $experience,
            'education'   => $education,
            'profile_pic' => $user->profile_pic
        );
        $app->render('admin/dashboard.html.twig', $data);
    } else {
        $app->redirect('/login');
    }
});

/**
 * Authenticate user
 */
$app->post('/admin/authenticate', function() use ($app) {
    $email = $app->request->post('email');
    $password = $app->request->post('password');
    $result = User::authenticate($email, $password);
    if ($result) {
        $app->redirect('/admin/projects');
    }
    $app->redirect('/admin/login');
});

/**
 * Logout user
 */
$app->get('/admin/logout', function() use ($app) {
    User::logout();
    $app->redirect('/login');
});

/**
 * Retrieve user's personal information.
 */
$app->get('/admin/info', function() use ($app) {
    $user = User::find(User::getId());
    if (is_null($user)) {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'User does not exist.'
        )));
    } else {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => true,
            'data'    => $user->toArray()
        )));
    }
});

$app->get('/admin/user/:id', function($id) use ($app) {
    $user = User::find($id);
    if (is_null($user)) {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'User does not exist.'
        )));
    } else {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => true,
            'data'    => $user->toArray()
        )));
    }
});

/**
 * Save user's personal information.
 */
$app->post('/admin/info/save', function() use ($app) {
    $data = $app->request->post('data');
    $user = User::find(2);
    if (is_null($user)) {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'User does not exist.'
        )));
    } else {
        $user->firstname = $data['firstname'];
        $user->lastname = $data['lastname'];
        $user->title = $data['title'];
        $user->email = $data['email'];
        $user->location = $data['location'];
        $user->bio = $data['bio'];
        $user->clients = $data['clients'];
        $user->save();

        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => true,
            'message' => 'Your info was updated successfully.'
        )));
    }
});

/**
 * Project management page
 */
$app->get('/admin/projects', function() use ($app) {
    $user = User::find(User::getId());
    $data = array(
        'projects' => Project::all(),
        'profile_pic' => $user->profile_pic
    );
    $app->render('admin/projects.html.twig', $data);
});

/**
 * Process request for creating or editing a project.
 */
$app->post('/admin/projects/save', function() use ($app) {
    $data = $app->request->post('data');
    //check if the request is for editing or creating a project
    if (empty($data['id'])) {
        $project = Project::create(array(
            'title'        => $data['title'],
            'description'  => addslashes($data['desc']),
            'type'         => $data['type'],
            'for'          => ($data['for']) ? $data['for'] : null,
            'slug'         => Project::createSlug($data['title']),
            'project_date' => ($data['date']) ? $data['date'] : null
        ));
    } else {
        $project = Project::find($data['id']);
        $project->title = $data['title'];
        $project->description = addslashes($data['desc']);
        $project->type = $data['type'];
        $project->for = $data['for'];
        $project->slug = Project::createSlug($data['title']);
        $project->project_date = $data['date'];
        $project->save();
    }

    if ($data['keywords']) {
        //explode string of tags into an array of tags
        $tags = explode(',', $data['keywords']);
        //go through each tag, add to database and create association
        //with the project
        foreach ($tags as $tag) {
            $keyword = Keyword::where(strtolower('name'), strtolower($tag))->get();
            if ($keyword->isEmpty()) {
                $keyword = Keyword::create(array(
                    'name' => $tag
                ));
                $tagsId[] = $keyword->id;
            } else {
                $keyword = $keyword->toArray();
                $tagsId[] = $keyword[0]['id'];
            }
        }

        $project->keywords()->sync($tagsId);
    }

    $app->response->headers->set('Content-Type', 'application/json');
    $app->response->setBody(json_encode(array(
        'success' => true,
        'message' => 'Your info was updated successfully.'
    )));
});

/**
 * Fetch information for the selected project.
 */
$app->get('/admin/project/:id/edit', function($id) use ($app) {
    $project = Project::find($id);
    foreach ($project->keywords as $keyword) {
        $keywords[] = $keyword->name;
    }
    $app->response->headers->set('Content-Type', 'application/json');
    $app->response->setBody(json_encode(array(
        'success' => true,
        'data'    => $project->toArray(),
    )));
});

/**
 * Process request for deleting a project.
 */
$app->delete('/admin/project/:id/delete', function($id) use ($app) {
    $project = Project::find($id);
    $project->keywords()->detach();
    if (count($project->images()->getResults())) {
        $project->images()->detach();
    }
    if ($project->delete()) {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => true,
            'message' => 'Project was deleted successfully.'
        )));
    } else {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'Could not delete the project. Try again.'
        )));
    }
});

/**
 * Projects page listing user's projects.
 */
$app->get('/admin/project/:id', function($id) use ($app) {
    $user = User::find(User::getId());
    $project = Project::find($id);
    $data = array(
        'project'     => $project,
        'images'      => $project->images,
        'profile_pic' => $user->profile_pic
    );
    $app->render('admin/images.html.twig', $data);
});

/**
 * Experience page listing user's experiences.
 */
$app->get('/admin/experiences', function() use ($app) {
    $user = User::find(User::getId());
    $experiences = array(
        'experiences' => Experience::all(),
        'profile_pic' => $user->profile_pic
    );
    $app->render('admin/experiences.html.twig', $experiences);
});

/**
 * Process request for saving new experiences.
 */
$app->post('/admin/experiences/save', function() use ($app) {
    $data = $app->request->post('data');
    if (empty($data['id'])) {
        $experience = Experience::create(array(
            'title'        => $data['title'],
            'organisation' => $data['organisation'],
            'location'     => $data['location'],
            'start_date'   => $data['startdate'],
            'end_date'     => $data['enddate']
        ));
    } else {
        $experience = Experience::find($data['id']);
        $experience->title = $data['title'];
        $experience->organisation = $data['organisation'];
        $experience->location = $data['location'];
        $experience->start_date = $data['startdate'];
        $experience->end_date = $data['enddate'];
        $experience->save();
    }

    if (!is_null($experience)) {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => true,
            'message' => 'New experience was successfully added.'
        )));
    } else {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'Failed to add new experience.'
        )));
    }
});

/**
 * Fetch data for the selected experience.
 */
$app->get('/admin/experience/:id/edit', function($id) use ($app) {
    $experience = Experience::find($id);
    $app->response->headers->set('Content-Type', 'application/json');
    $app->response->setBody(json_encode(array(
        'success' => true,
        'data'    => $experience->toArray()
    )));
});

/**
 * Process DELETE request for deleting experience.
 */
$app->delete('/admin/experience/:id/delete', function($id) use ($app) {
    $experience = Experience::find($id);
    if ($experience->delete()) {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => true,
            'message' => 'Experience was successfully deleted.'
        )));
    } else {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'Could not delete the experience. Try again.'
        )));
    }
});

/**
 * Education page listing user's educations.
 */
$app->get('/admin/educations', function() use ($app) {
    $user = User::find(User::getId());
    $educations = array(
        'educations'  => Education::all(),
        'profile_pic' => $user->profile_pic
    );
    $app->render('admin/educations.html.twig', $educations);
});

/**
 * Process request for saving new educations.
 */
$app->post('/admin/educations/save', function() use ($app) {
    $data = $app->request->post('data');
    if (empty($data['id'])) {
        $education = Education::create(array(
            'course'     => $data['course'],
            'school'     => $data['school'],
            'location'   => $data['location'],
            'start_year' => $data['startyear'],
            'end_year'   => $data['endyear']
        ));
    } else {
        $education = Education::find($data['id']);
        $education->course = $data['course'];
        $education->school = $data['school'];
        $education->location = $data['location'];
        $education->start_year = $data['startyear'];
        $education->end_year = $data['endyear'];
        $education->save();
    }

    if (!is_null($education)) {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => true,
            'message' => 'New education was successfully added.'
        )));
    } else {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'Failed to add new education.'
        )));
    }
});

/**
 * Fetch education detail for editing.
 */
$app->get('/admin/education/:id/edit', function($id) use ($app) {
    $education = Education::find($id);
    $app->response->headers->set('Content-Type', 'application/json');
    $app->response->setBody(json_encode(array(
        'success' => true,
        'data'    => $education->toArray()
    )));
});

/**
 * Process request for deleting educations.
 */
$app->delete('/admin/educations/:id/delete', function($id) use ($app) {
    $education = Education::find($id);
    if ($education->delete()) {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => true,
            'message' => 'Education was successfully deleted.'
        )));
    } else {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'Could not delete the education. Try again.'
        )));
    }
});

/**
 * Fetch keywords matching the term.
 */
$app->get('/admin/keywords/:term', function($term) use ($app) {
    $keywords = Keyword::where('name', 'LIKE', '%'.$term.'%')->get();
    if (empty($keywords)) {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'Keyword does not exist.'
        )));
    } else {
        //add keywords found to an array
        foreach ($keywords as $keyword) {
            $data[]['name'] = $keyword['name'];
        }
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode($data));
    }
});

/**
 * Process request for removing keyword association with the selected project.
 */
$app->delete('/admin/keyword/:project/:keyword', function($project, $keyword) use ($app) {
    $project = Project::find($project);
    if ($project->keywords()->detach()) {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => true,
            'message' => 'Keyword removed from the project.'
        )));
    } else {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'Keyword could not be removed from the project. Try again.'
        )));
    }
});

/**
 * Load selected image.
 */
$app->get('/admin/image/:id', function($id) use ($app) {
    $image = Image::find($id);
    if ($image) {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => true,
            'data'    => $image->toArray()
        )));
    } else {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'Image does not exist.'
        )));
    }
});

/**
 * Upload image to temp folder by confirming correct file type is
 * chosen.
 */
$app->post('/admin/image/upload', function() use ($app) {
    $file = $_FILES['pic'];
    //temp folder path for uploading images
    $upload_path = __DIR__ . '/../../tmp/' . $file['name'];
    //url path to file stored temporarily in the server
    $url = $app->config('application')['url']['base'] . $app->config('application')['image_paths']['tmp'];
    
    //confirm file type is allowed and upload it to temp folder
    if (in_array($file['type'], Image::$accepted_image_types)) {
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $app->response->headers->set('Content-Type', 'application/json');
            $app->response->setBody(json_encode(array(
                'success'       => true,
                'temp_img_path' => $url . $file['name']
            )));
        } else {
            $app->response->headers->set('Content-Type', 'application/json');
            $app->response->setBody(json_encode(array(
                'success' => false,
                'message' => 'Could not upload the file. Try again.'
            )));
        }
    } else {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'Invalid file type. Allowed file types are JPEG, JPG, GIF and PNG'
        )));
    }
});

$app->post('/admin/profile-pic/upload', function() use ($app) {
    $file = $_FILES['pic'];
    $upload_path = __DIR__ . '/../../public/image/profile/' . $file['name'];
    $url = $app->config('application')['url']['base'] . $app->config('application')['image_paths']['profile'];

    //confirm file type is allowed and upload it to temp folder
    if (in_array($file['type'], Image::$accepted_image_types)) {
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $app->response->headers->set('Content-Type', 'application/json');
            $app->response->setBody(json_encode(array(
                'success'       => true,
                'temp_img_path' => $url . $file['name']
            )));
        } else {
            $app->response->headers->set('Content-Type', 'application/json');
            $app->response->setBody(json_encode(array(
                'success' => false,
                'message' => 'Could not upload the file. Try again.'
            )));
        }
    } else {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'Invalid file type. Allowed file types are JPEG, JPG, GIF and PNG'
        )));
    }
});

$app->post('/admin/profile-pic/save', function() use ($app) {
    $data = $app->request->post('data');
    $user = User::find(User::getId());
    $user->profile_pic = $data['file'];
    if ($user->save()) {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => true,
            'message' => 'Profile picture updated.'
        )));
    } else {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'Failed to update profile picture. Try again.'
        )));
    }
});

/**
 * Crop uploaded image, create a thumbnail and copy the image to
 * permanent folder as well as store file data in the database.
 */
$app->post('/admin/image/process', function() use ($app) {
    $data = $app->request->post('data');
    $image = new Image($app->config('application')['url']['base']);
    $result = $image->copy($data['file'], $app->config('application')['image_paths']);
    
    //check whether image was cropped and thumbnail was created
    if (!is_null($result)) {
        //store image data in the database
        $image->title = $data['title'];
        $image->description = str_replace("'", '', $data['desc']);
        $image->filename = $result['large'];
        $image->thumbnail = $result['thumb'];
        $image->cover = $result['cover'];
        $image->project_id = $data['project'];
        $image->save();
        
        //remove temporary image file
        unlink(__DIR__ . '/../../tmp/' . $data['file']);
        
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => true,
            'message' => 'Image uploaded successfully.'
        )));
    } else {
        //remove temporary image file
        unlink(__DIR__ . '/../../tmp/' . $data['file']);
        
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'Image could not be updated. Try again.'
        )));
    }
});

/**
 * Update image info in the database.
 */
$app->post('/admin/image/save', function() use ($app) {
    $data = $app->request->post('data');
    $image = Image::find($data['id']);
    
    //confirm image being edited exists in the database
    if ($image) {
        $image->title = $data['title'];
        $image->description = htmlspecialchars($data['desc'], ENT_QUOTES);
        $image->updated_at = date('Y-m-d H:i:s');
        $image->save();

        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => true,
            'message' => 'Image info updated.'
        )));
    } else {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'Image info could not be updated. Try again.'
        )));
    }
});

/**
 * Delete an image from the database and directories. Complete
 * the process by redirecting user to an image management page.
 */
$app->delete('/admin/image/:id/delete', function($id) use ($app) {
    $image = Image::find($id);
    $file_large = $image->filename;
    $file_thumb = $image->thumbnail;
    
    //check whether the file data was deleted from the database
    //and remove image files from the server
    if ($image->delete()) {
        unlink(__DIR__ . '/../..' . $file_large);
        unlink(__DIR__ . '/../..' . $file_thumb);

        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => true,
            'message' => 'Image successfully deleted.'
        )));
    } else {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array(
            'success' => false,
            'message' => 'Image could not be deleted. Try again.'
        )));
    }
});