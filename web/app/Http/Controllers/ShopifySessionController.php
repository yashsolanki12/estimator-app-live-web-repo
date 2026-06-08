<?php

namespace App\Http\Controllers;

use App\Models\ShopifySession;
use Illuminate\Http\Request;

class ShopifySessionController extends Controller
{
    public function index()
    {
        return response()->json(ShopifySession::all());
    }

    public function show($id)
    {
        return response()->json(ShopifySession::findOrFail($id));
    }
}