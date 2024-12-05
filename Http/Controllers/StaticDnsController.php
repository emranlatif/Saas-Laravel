<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\StaticDomainInterface;
use App\Repositories\StaticDomainRepository;

class StaticDnsController extends Controller
{
    protected $staticDomainInterface;
    protected $staticDomainRepository;
    /**
     * Display a listing of the resource.
     */

    public function __construct(StaticDomainInterface $staticDomainInterface ,StaticDomainRepository $staticDomainRepository ){
        $this->staticDomainInterface = $staticDomainInterface;
        $this->staticDomainRepository = $staticDomainRepository;
    }

    public function index()
    {
        //
    }


    public function store(Request $request)
    {
        $data = $request->all();
        $response = $this->staticDomainInterface->createStaticDns($data);
        return response()->json($response['data'], $response['status']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
