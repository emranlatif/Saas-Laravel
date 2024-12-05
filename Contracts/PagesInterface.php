<?php

namespace App\Contracts;

interface PagesInterface
{
    public function createPage(array $data);
    public function updatePage( array $data , int $id,);
    public function delPage(int $id);
    public function getAllPages($request);
    public function getPageSetting($id);
}
