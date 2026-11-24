<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserPreferenceController extends Controller
{
    public function updateTheme(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme' => ['required', Rule::in(array_keys(config('themes')))],
        ]);

        $request->user()->update([
            'theme_preference' => $validated['theme'],
        ]);

        return response()->json(['ok' => true]);
    }
}
