<?php

namespace App\Contracts;

interface StaticDomainInterface
{
    public function createStaticDns(array $data);
    // public function editDomain(int $id, array $data);
    // public function deleteDomain(int $id);
    // public function getDomain(int $id);
}
