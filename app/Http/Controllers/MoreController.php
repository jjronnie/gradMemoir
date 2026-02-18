<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class MoreController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('More');
    }

    public function howItWorks(): Response
    {
        return Inertia::render('HowItWorks');
    }

    public function terms(): Response
    {
        return Inertia::render('Terms');
    }

    public function accountSuspended(): Response
    {
        return Inertia::render('AccountSuspended');
    }
}
