<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Services\InegiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use UnexpectedValueException;

class StateController extends Controller
{
    public function index(): View
    {
        $states = State::query()
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'name',
                'abbreviation',
                'total_population',
                'female_population',
                'male_population',
                'inhabited_dwellings',
            ]);

        return view('states.index', compact('states'));
    }

    public function municipalities(State $state, InegiService $inegi): JsonResponse
    {
        try {
            $municipalities = $inegi->municipalities($state->code);
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            report($exception);

            return response()->json([
                'message' => 'No fue posible consultar los municipios.',
            ], 502);
        }

        return response()->json([
            'municipalities' => $municipalities,
        ]);
    }
}
