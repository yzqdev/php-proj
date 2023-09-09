<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TagController extends Controller
{
    public function getAll()
    {
        $tags = Tag::all();

        return Response::json($tags);
    }

    public function create(Request $request)
    {
        $tagName = $request->get('name');
        return Tag::create(['name' => $tagName]);

    }
}
