<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->redirect(Auth::check() ? '/dashboard' : '/login');
    }
}
