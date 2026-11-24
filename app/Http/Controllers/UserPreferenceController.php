<?php

namespace App\Http\Controllers;

use App\Support\ThemeColors;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserPreferenceController extends Controller
{
    public function updateTheme(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tema' => ['required', Rule::in(array_keys(config('themes')))],
            'cores' => ['sometimes', 'array:background,buttons,text'],
            'cores.background' => ['required_with:cores', 'regex:/\A#[0-9A-Fa-f]{6}\z/'],
            'cores.buttons' => ['required_with:cores', 'regex:/\A#[0-9A-Fa-f]{6}\z/'],
            'cores.text' => ['required_with:cores', 'regex:/\A#[0-9A-Fa-f]{6}\z/'],
        ]);

        if (isset($validated['cores'])) {
            $textColor = $validated['cores']['text'];
            if (! ThemeColors::hasAccessibleContrast($textColor, $validated['cores']['background'])) {
                throw ValidationException::withMessages([
                    'cores.text' => 'Escolha uma cor de texto com contraste suficiente sobre o fundo.',
                ]);
            }
        }

        $preferences = [
            'tema_preferido' => $validated['tema'],
        ];

        if (isset($validated['cores'])) {
            $preferences['cores_tema_personalizado'] = $validated['cores'];
        }

        $request->user()->update($preferences);

        return response()->json([
            'ok' => true,
            'button_text' => isset($validated['cores'])
                ? ThemeColors::contrastText($validated['cores']['buttons'])
                : null,
        ]);
    }
}
