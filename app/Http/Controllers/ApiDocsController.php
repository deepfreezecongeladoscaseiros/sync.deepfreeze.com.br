<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiDocsController extends Controller
{
    public function index()
    {
        $baseUrl = config('app.url');
        
        return view('api_docs.index', compact('baseUrl'));
    }
}
