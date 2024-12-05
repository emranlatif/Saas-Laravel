<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\DomainServiceInterface;
use App\Repositories\DomainRepository;


class Domains extends Controller
{

    protected $creatDomainService;
    protected $domainRepository;
    /**
     * Display a listing of the resource.
     */

    public function __construct(DomainServiceInterface $creatDomainService ,DomainRepository $domainRepository ){
        $this->creatDomainService = $creatDomainService;
        $this->domainRepository = $domainRepository;
    }
    public function index(Request $request)
    {
        $response = $this->creatDomainService->getDomains($request);
        return response()->json($response['data']);
    }

    public function checkDomain()
    {
        return response()->json(['message' => 'Domain exists'], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $response = $this->creatDomainService->createDomain($data);
        return response()->json($response['data']);
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
        $response = $this->creatDomainService->deleteDomain($id);
        return response()->json($response['data']);
    }
}
