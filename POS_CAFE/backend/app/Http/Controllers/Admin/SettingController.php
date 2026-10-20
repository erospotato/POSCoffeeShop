<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\SystemSetting;
use App\Services\AuditService;

class SettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->groupBy('group');
        return view('settings.index', compact('settings'));
    }

    public function update(SettingRequest $request)
    {
        $settings = $request->input('settings', []);

        foreach ($settings as $key => $value) {
            SystemSetting::set($key, $value);
        }

        AuditService::log('system_settings_updated');

        return back()->with('success', 'System settings updated successfully.');
    }
}
