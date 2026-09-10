<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

/**
 * Public, unauthenticated marketing/informational pages.
 */
final class PageController extends Controller
{
    public function landing(): never
    {
        $this->view('public/landing', [
            'title' => 'LifeLink — Find blood donors when every minute counts',
        ]);
    }

    public function howItWorks(): never
    {
        $this->view('public/how-it-works', [
            'title' => 'How LifeLink works',
        ]);
    }
}
