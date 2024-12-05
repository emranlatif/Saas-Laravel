<?php

namespace App\Contracts;

interface DomainServiceInterface
{
    public function createDomain(array $data);
    // public function editDomain(int $id, array $data);
    public function deleteDomain(int $id);
    public function getDomains($request);
}
