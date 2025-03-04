<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class WeatherController extends Controller
{
    protected $apiUrl = 'https://api.openweathermap.org/data/2.5/';
    protected $accessToken = '';

    public function index(Request $request)
    {

        $filters = [
            'languare' => $request->query('languare', 'ru'),
            'hours' => $request->query('hours', '10'),
            'citys' => $request->query('citys', 'Kharkiv')
        ];

        $getWeather = Http::get("{$this->apiUrl}forecast", [
            'q' => $filters['citys'],
            'cnt' => $filters['hours'],
            'lang' => $filters['languare'],
            'units' => 'metric',
            'appid' => $this->accessToken
        ])->json();

        return Inertia::render('Frontend/Weather', [
            'data' => $getWeather,
            'city' => $getWeather['city'],
            'list' => $getWeather['list'],
            'filters' => $filters // Передаем всё одним объектом
        ]);
    }
}
