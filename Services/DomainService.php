<?php

namespace App\Services;

use App\Contracts\DomainServiceInterface;
use App\Models\DomainsModel;
use App\Models\StaticDnsModel;
use Illuminate\Support\Facades\Validator;
use App\Repositories\DomainRepository;
use App\Repositories\StaticDomainRepository;
use Illuminate\Validation\Rule;
use Exception;

class DomainService implements DomainServiceInterface
{
    public function __construct(DomainRepository $domainRepository, StaticDomainRepository $staticDnsRepo ){
        $this->domainRepository = $domainRepository;
        $this->staticDnsRepo = $staticDnsRepo;
     }

    public function createDomain(array $data)
    {
        $message = [
            'name.unique' => 'Domain already added. Please choose a different name.',
        ];
        $validator = Validator::make($data, [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('domains')->whereNull('deleted_at'),
            ],
        ],$message);

        if ($validator->fails()) {
            return [
                'data' => ['status' => 422,'errors' => $validator->errors()]
            ];
        }
        // $dnsRecords = dns_get_record($data['name'], DNS_ANY);
        try {
            $dnsRecords = dns_get_record($data['name'], DNS_ANY);
            if ($dnsRecords === false || empty($dnsRecords)) {
                throw new Exception('Domain not found.');
            }
            // print_r($dnsRecords);
        } catch (Exception $e) {
            return [

                    'data' => ['status' => 422,'message' =>$e->getMessage()]
                ];
        }
        $getDns = $this->staticDnsRepo->getDns();
        $checkDnsStatus = $this->checkDnsStatus($data['name'], $dnsRecords, $getDns);
        $data['status'] = $checkDnsStatus;
        // if (empty($dnsRecords)) {
        //     return [
                
        //         'data' => ['status' => 422,'message' =>'The domain is not registered.']
        //     ];
        // }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.cloudflare.com/client/v4/zones/aea7b6a9078be6d359b073a82087bec6/custom_hostnames",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                "hostname" => $data['name'],
                "ssl" => [
                    "bundle_method" => "ubiquitous",
                    // "certificate_authority" => "google",
                    // "custom_certificate" => "-----BEGIN CERTIFICATE-----\nMIIFJDCCBAygAwIBAgIQD0ifmj/Yi5NP/2gdUySbfzANBgkqhkiG9w0BAQsFADBN\n...",
                    // "custom_key" => "-----BEGIN RSA PRIVATE KEY-----\nMIIEowIBAAKCAQEAwQHoetcl9+5ikGzV6cMzWtWPJHqXT3wpbEkRU9Yz7lgvddmG\n...",
                    "method" => "http",
                    "settings" => [
                        "ciphers" => ["ECDHE-RSA-AES128-GCM-SHA256", "AES128-SHA"],
                        "early_hints" => "on",
                        "http2" => "on",
                        "min_tls_version" => "1.2",
                        "tls_1_3" => "on"
                    ],
                    "type" => "dv",
                    "wildcard" => false
                ]
            ]),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer V3f9lRpMXE8EQIBrjA6k_Zswedt9a9TnsCtjgO4A"
            ],
        ]);
        $response = curl_exec($curl);
        $responseArray = json_decode($response, true);
        // Check if the ownership_verification_http part exists
        $data['ownership_url'] = '';
        $data['ownership_body'] = '';
        if (isset($responseArray['result']['ownership_verification_http'])) {
            $data['ownership_url'] = $responseArray['result']['ownership_verification_http']['http_url'];
            $data['ownership_body'] = $responseArray['result']['ownership_verification_http']['http_body'];
        }

        $domain = $this->domainRepository->create($data);

        $domain['dns_server_one'] = $getDns['dns_server_one'];
        $domain['dns_server_two'] = $getDns['dns_server_two'];
        return [
            
            'data' => ['status' => 201,'message' => 'Domain created successfully', 'domain' => $domain]
        ];
    }

   

    public function checkDnsStatus($domain, $comingDns, $getDns)
    {
       $server1 = $getDns['dns_server_one'];
       $server2 = $getDns['dns_server_two'];
       $foundServer1 = false;
       $foundServer2 = false;
       foreach ($comingDns as $record) {
           if ($record['type'] === 'NS') {
               if ($record['target'] === $server1) {
                   $foundServer1 = true;
               }
               if ($record['target'] === $server2) {
                   $foundServer2 = true;
               }
           }
       }

       // Check if both servers are found
       if ($foundServer1 && $foundServer2) {
           return 'Active';
       } else {
           return 'Awaiting';
       }
    }

    public function getDomains($request){
        
        $getDns = $this->staticDnsRepo->getDns();

        $getAllDomains = $this->domainRepository->getAllDomains($request);
        $domains = $getAllDomains->items();
        foreach($domains as $key => $domains){
            $dnsRecords = dns_get_record($domains['name'], DNS_ANY);
            $checkDnsStatus = $this->checkDnsStatus($domains['name'], $dnsRecords, $getDns);
            $getAllDomains[$key]['status'] = $checkDnsStatus;
            $getAllDomains[$key]['dns_server_one'] = $getDns['dns_server_one'];
            $getAllDomains[$key]['dns_server_two'] = $getDns['dns_server_two'];
        }
        return [
            'data' => ['status' => 200, 'message' => 'All domains', 'domain' => $getAllDomains]
        ];
    }

    public function deleteDomain($id){
        $deleteDomain = $this->domainRepository->deleteDomainRecord($id);
        if ($deleteDomain) {
            return [
                'data' => ['status' => 200,'message' => 'Domain deleted successfully']
            ];
        } else {
            return [
                'data' => ['status' => 404,'message' => 'Domain not found.']
            ];
        }
    }
}
