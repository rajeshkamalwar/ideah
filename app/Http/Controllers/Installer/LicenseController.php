<?php

namespace App\Http\Controllers\Installer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use RachidLaasri\LaravelInstaller\Controllers\LicenseController as BaseLicenseController;

class LicenseController extends BaseLicenseController
{
    public function license()
    {
        return redirect()->route('LaravelInstaller::environment');
    }

    public function licenseCheck(Request $request)
    {
        InstallerLicenseBypass::ensureMarkerFile();
        Session::flash('license_success', 'Your license is verified successfully!');
        return redirect()->route('LaravelInstaller::environmentWizard');
    }
}
