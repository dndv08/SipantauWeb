<?php
namespace App\Controllers;

class Login extends BaseController
{
    public function index()
    {
        return view('auth/login');
    }

    public function authenticate()
    {
        // Logic autentikasi di sini
        // Validasi, cek database, set session, etc.
    }
}