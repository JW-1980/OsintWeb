<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Services\RequirementsChecker;
use Illuminate\Contracts\View\View;

/**
 * Welcome and requirements check controller
 */
class WelcomeController extends Controller
{
    public function __construct(
        private readonly RequirementsChecker $requirementsChecker
    ) {
    }

    /**
     * Display welcome page with requirements check
     */
    public function index(): View
    {
        $requirements = $this->requirementsChecker->check();
        $allPassed = $this->requirementsChecker->allPassed();
        $messages = $this->requirementsChecker->getMessages();

        return view('install.welcome', [
            'requirements' => $requirements,
            'allPassed' => $allPassed,
            'messages' => $messages,
        ]);
    }
}
