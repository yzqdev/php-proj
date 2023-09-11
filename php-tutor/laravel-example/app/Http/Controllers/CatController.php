<?php

namespace App\Http\Controllers;

use App\Models\Cat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cat = Cat::query()->create(['name' => 'tom cat','sex'=>"man",'weight'=>10]);
       dump($cat);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Cat $cat)
    {
      $cat=Cat::query()->where('name','tom cat')->get();
      dump($cat);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cat $cat)
    {

        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cat $cat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cat $cat)
    {
        //
    }
}
