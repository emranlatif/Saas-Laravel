<?php

namespace App\Repositories;

use App\Models\DomainsModel;
use Illuminate\Support\Facades\Auth;
class DomainRepository
{
    public function create(array $data)
    {
        $userId = auth()->id();
        // Add the user_id to the data array
        $data['user_id'] = $userId;
        return DomainsModel::create($data);
    }
    public function getAllDomains($request){
        $userId = Auth::id();
        $perPage = $request->per_page ?? 10;
        return DomainsModel::where('user_id', $userId)->withoutTrashed()->paginate($request->per_page);
    }
    public function deleteDomainRecord($id){
        $domain = DomainsModel::find($id);
        if ($domain) {
            $domain->delete();
            return true;
        } else {
            return false; 
        }
    }

}
