<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Http\Requests\StoreEditionRequest;
use App\Http\Requests\UpdateEditionRequest;

class EditionController extends Controller
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEditionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Edition $edition)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Edition $edition)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEditionRequest $request, Edition $edition)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Edition $edition)
    {
        //
    }
}
