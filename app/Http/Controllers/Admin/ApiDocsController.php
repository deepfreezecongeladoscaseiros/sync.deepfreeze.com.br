<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiDocsController extends Controller
{
    public function index()
    {
        $baseUrl = config('app.url');
        
        return view('admin.api_docs.index', compact('baseUrl'));
    }
}
