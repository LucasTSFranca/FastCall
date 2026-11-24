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
            'tema' => ['required', Rule::in(array_keys(config('themes')))],
        ]);

        $request->user()->update([
            'tema_preferido' => $validated['tema'],
        ]);

        return response()->json(['ok' => true]);
    }
}
