<?php

namespace App\Services;

use App\Contracts\StaticDomainInterface;
use Illuminate\Support\Facades\Validator;
use App\Repositories\StaticDomainRepository;

class StaticDnsService implements StaticDomainInterface
{
    public function __construct(StaticDomainRepository $staticDomainRepository ){
        $this->staticDomainRepository = $staticDomainRepository;
     }

    public function createStaticDns(array $data)
    {
        $message = [
            'dns_server_one.unique' => 'Dns already added. Please choose a different name.',
            'dns_server_two.unique' => 'Dns already added. Please choose a different name.',
        ];
        $validator = Validator::make($data, [
            'dns_server_one' => 'required|string|max:255|unique:static_domain_dns,dns_server_one',
            'dns_server_two' => 'required|string|max:255|unique:static_domain_dns,dns_server_two',
        ],$message);

        if ($validator->fails()) {
            // Return validation errors
            return [
                'status' => 422,
                'data' => ['errors' => $validator->errors()]
            ];
        }

        $staticDomainDns = $this->staticDomainRepository->create($data);
        // Return success response
        return [
            'status' => 201,
            'data' => ['message' => 'Dns created successfully', 'dns' => $staticDomainDns]
        ];
    }

    // Implement other methods defined in DomainServiceInterface
}
