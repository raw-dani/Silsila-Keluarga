<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppSettingController extends Controller
{
    /**
     * Get all app settings
     */
    public function index()
    {
        $settings = AppSetting::all();
        return response()->json($settings);
    }

    /**
     * Get a specific setting
     */
    public function show($key)
    {
        $setting = AppSetting::where('key', $key)->first();

        if (!$setting) {
            return response()->json(['message' => 'Setting not found'], 404);
        }

        return response()->json([
            'key' => $setting->key,
            'value' => AppSetting::getValue($setting->key),
            'type' => $setting->type,
            'description' => $setting->description,
        ]);
    }

    /**
     * Get family name setting
     */
    public function getFamilyName()
    {
        $familyName = AppSetting::getValue('family_name', 'Keluarga Besar');
        return response()->json([
            'family_name' => $familyName
        ]);
    }

    /**
     * Update family name setting
     */
    public function updateFamilyName(Request $request)
    {
        $request->validate([
            'family_name' => 'required|string|max:255',
        ]);

        AppSetting::setValue(
            'family_name',
            $request->family_name,
            'string',
            'Nama keluarga besar yang ditampilkan di aplikasi'
        );

        return response()->json([
            'message' => 'Nama keluarga berhasil diperbarui',
            'family_name' => $request->family_name
        ]);
    }

    /**
     * Update multiple settings
     */
    public function updateMultiple(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
            'settings.*.type' => 'sometimes|in:string,integer,boolean,json',
        ]);

        $updatedSettings = [];

        foreach ($request->settings as $settingData) {
            $setting = AppSetting::setValue(
                $settingData['key'],
                $settingData['value'],
                $settingData['type'] ?? 'string',
                $settingData['description'] ?? null
            );

            $updatedSettings[] = [
                'key' => $setting->key,
                'value' => AppSetting::getValue($setting->key),
                'type' => $setting->type,
                'description' => $setting->description,
            ];
        }

        return response()->json([
            'message' => 'Pengaturan berhasil diperbarui',
            'settings' => $updatedSettings
        ]);
    }

    /**
     * Create or update a setting
     */
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|unique:app_settings,key,' . ($request->id ?? 'NULL') . ',id',
            'value' => 'required',
            'type' => 'required|in:string,integer,boolean,json',
            'description' => 'nullable|string|max:1000',
        ]);

        $setting = AppSetting::setValue(
            $request->key,
            $request->value,
            $request->type,
            $request->description
        );

        return response()->json([
            'message' => 'Setting berhasil dibuat/diupdate',
            'setting' => [
                'key' => $setting->key,
                'value' => AppSetting::getValue($setting->key),
                'type' => $setting->type,
                'description' => $setting->description,
            ]
        ], 201);
    }

    /**
     * Update a setting
     */
    public function update(Request $request, $key)
    {
        $setting = AppSetting::where('key', $key)->first();

        if (!$setting) {
            throw ValidationException::withMessages([
                'key' => ['Setting tidak ditemukan']
            ]);
        }

        $request->validate([
            'value' => 'required',
            'type' => 'sometimes|in:string,integer,boolean,json',
            'description' => 'nullable|string|max:1000',
        ]);

        $setting->update([
            'value' => is_string($request->value) ? $request->value : json_encode($request->value),
            'type' => $request->type ?? $setting->type,
            'description' => $request->description ?? $setting->description,
        ]);

        return response()->json([
            'message' => 'Setting berhasil diperbarui',
            'setting' => [
                'key' => $setting->key,
                'value' => AppSetting::getValue($setting->key),
                'type' => $setting->type,
                'description' => $setting->description,
            ]
        ]);
    }

    /**
     * Delete a setting
     */
    public function destroy($key)
    {
        $setting = AppSetting::where('key', $key)->first();

        if (!$setting) {
            return response()->json(['message' => 'Setting tidak ditemukan'], 404);
        }

        $setting->delete();

        return response()->json([
            'message' => 'Setting berhasil dihapus'
        ]);
    }
}
