<?php

namespace App\Controllers;

use App\Core\Controller;

class LandingController extends Controller {
    
    public function index() {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }
        $this->view('landing/index');
    }
}
