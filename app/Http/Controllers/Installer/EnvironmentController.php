<?php

namespace App\Http\Controllers\Installer;

use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use RachidLaasri\LaravelInstaller\Controllers\EnvironmentController as BaseEnvironmentController;

class EnvironmentController extends BaseEnvironmentController
{
    public function environmentWizard()
    {
        InstallerLicenseBypass::ensureMarkerFile();
        return parent::environmentWizard();
    }

    public function saveWizard(Request $request, Redirector $redirect)
    {
        InstallerLicenseBypass::ensureMarkerFile();
        return parent::saveWizard($request, $redirect);
    }
}
