<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\PagesInterface;

class PagesController extends Controller
{

    protected $pagesInterface;
    public function __construct(PagesInterface $pagesInterface){
        $this->pagesInterface= $pagesInterface;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $response = $this->pagesInterface->getAllPages($request);
        return response()->json($response['data']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $response = $this->pagesInterface->createPage($request->all());
        return response()->json($response['data']);
    }

    public function show(string $id)
    {
        $response = $this->pagesInterface->getPageSetting($id);
        return response()->json($response['data']);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        print_r($request->all());
        die('kk');
    }

    public function updatePageSetting(Request $request, $id){
        $response = $this->pagesInterface->updatePage($request->all(), $id);
        return response()->json($response['data']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = $this->pagesInterface->delPage($id);
        return response()->json($response['data']);
    }
}
