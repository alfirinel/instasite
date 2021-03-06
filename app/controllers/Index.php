<?php


namespace app\controllers;


use app\core\Route;
use app\core\View;
use app\exceptions\UploadException;
use app\helpers\Session;
use app\models\AuthModel;
use app\models\Photo;

class Index
{
    const UPLOAD_DIR = 'images/gallery/';

    protected $view;

    protected $model;

    protected $authModel;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
//        if(!Session::isAuth()){
//            throw new NoAuthException();
//        }

        $this->model = new Photo();
        $this->view = new View();
        $this->authModel = new AuthModel();
    }


    public function index()
    {
        $this->view->render('start');
    }

    public function gallery()
    {
        $this->view->render('gallery', [
            'photos' => $this->model->all(),
            'cur_page'=>$_GET['page'],
            'total'=>4,
        ]);
    }

    public function user()
    {
        $user = Session::getAuthUser();
        $this->view->render('user', [
            'photos' => $this->model->getByUserID($user['id']),
            'cur_page'=>$_GET['page'],
            'total'=>$this->model->totalCount(),
        ]);
    }

    public function store()
    {
        $user = Session::getAuthUser();
        $photo = $_FILES['photo'];
        $path = self::UPLOAD_DIR . $photo['name'];
        if (!move_uploaded_file($photo['tmp_name'], $path)) {
            throw new UploadException('no upload');
        }
        $this->model->add('/'.$path, $user['id']);
        Route::redirect('user');
    }

    public function destroy()
    {
        $id = filter_input(INPUT_POST, 'id');
        //TODO если нет ид, то 404 статус
        $this->model->delete($id);
        Route::redirect('user');
    }

    public function like()
    {
        $user = Session::getAuthUser();
        $photoId = filter_input(INPUT_POST, 'id');
        $this->model->addLike($user['id'], $photoId);
        Route::redirect('gallery');
    }

}