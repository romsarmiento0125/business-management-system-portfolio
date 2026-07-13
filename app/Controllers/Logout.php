<?php

namespace App\Controllers;

use App\Models\CoreModel;

class Logout extends BaseController
{
    public function index()
    {
        // Destroy the current session
        session()->destroy();

        // Redirect to the login page (adjust the route if needed)
        return redirect()->to('/login')->with('message', 'You have been logged out.');
    }
}
