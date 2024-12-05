<?php

namespace App\Repositories;

use App\Models\PagesModel;
use App\Models\DomainsModel;
use App\Models\PagePixelSetupModel;
use App\Models\PageFitModel;
use App\Models\PageSettingModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
class PagesRepository
{
    public function create(array $data)
    {
        $userId = auth()->id();
        $getDomainName = DomainsModel::where('id', $data['domain_id'])->first();
        $data['user_id'] = $userId;
        $data['name'] = $getDomainName['name'];
        // $a = 'https://invozone.com/hire-saas-developers/';

        // try {
        //     $response = Http::get($a);
        //     if ($response->successful()) {
        //         $content = $response->body();
        //         // Modify the content to handle relative URLs
        //         $baseUrl = parse_url($a, PHP_URL_SCHEME) . '://' . parse_url($a, PHP_URL_HOST);
        //         $content = str_replace('src="/', 'src="' . $baseUrl . '/', $content);
        //         $content = str_replace('href="/', 'href="' . $baseUrl . '/', $content);
        //         return response($content)
        //         ->header('Content-Type', 'text/html');
        //     } else {
        //         return response('Error fetching the page', 500);
        //     }
        // } catch (\Exception $e) {
        //     return response('Error: ' . $e->getMessage(), 500);
        // }
        // die();
        $page = PagesModel::create($data);
// print_r($page->uuid);die();
        // Create the associated settings
        $page->setting()->create([
            'uuid' => $page->uuid,
            'page_url' => $data['name'].'/' . $data['sub_domain'],
            'presell_url' => $data['redirect_url'].'/' . $data['sub_domain'].'/'.'presell',
        ]);

        return $page;
    }
   
    public function createPageSetting($data)
    {
        return PageSettingModel::create($data);
    }

    public function checkIfDomainExist($id){
        return DomainsModel::where('id', $id)->exists();
    }
    public function checkPageExist($id){
        $userId = auth()->id();
        return PagesModel::where('uuid', $id)->where('user_id',$userId )->exists();
    }

    public function checkPageSettingsExist($id){
        return PageSettingModel::where('page_id', $id)->exists();
    }
    
    public function deletePage($id)
    {
        return PagesModel::findOrFail($id)->delete();
    }
    
    public function updatePageSetting($id, $data)
    {
        $pageSetting = PageSettingModel::where('page_id', $id)->first();
        // print_r($pageSetting);die();
        if ($pageSetting) {
            $pageSetting->update($data);
        }
        return $pageSetting;
    }

    public function deleteExistingPixels($settingId)
    {
        PagePixelSetupModel::where('setting_id', $settingId)->delete();
    }

    public function deleteExistingFits($settingId)
    {
        PageFitModel::where('setting_id', $settingId)->delete();
    }

    public function createPixel($settingId, $pixelId, $type)
    {
        return PagePixelSetupModel::create([
            'setting_id' => $settingId,
            'pixel_id' => $pixelId,
            'type' => $type
        ]);
    }

    public function createFit($settingId, $newElementCode, $pageElementCode)
    {
        return PageFitModel::create([
            'setting_id' => $settingId,
            'new_element_code' => $newElementCode,
            'page_element_code' => $pageElementCode
        ]);
    }

    // public function updatePageSetting($id, array $data)
    // {
    //     // Find the PagesModel instance by ID
    //     $page = PagesModel::findOrFail($id);
    //     $originalAttributes = $page->setting ? $page->setting->getOriginal() : [];
    //     // Update the associated PageSettingModel
    //     if ($page->setting) {
    //         // If the PageSettingModel exists, update it
    //         $page->setting->update([
    //             'country_code' => $data['country_code'],
    //             'telephone' => $data['telephone'],
    //             'initial_message' => $data['initial_message'],
    //             'telegram_link' => $data['telegram_link'],
    //             'messenger_link' => $data['messenger_link'],
    //             'instagram_link' => $data['instagram_link'],
    //             'cookies' => $data['cookies'],
    //             'tag_head' => $data['tag_head'],
    //             'tag_body' => $data['tag_body'],
    //         ]);
    //     } else {
    //         // If the PageSettingModel doesn't exist, create a new one
    //         $page->setting()->create([
    //             'country_code' => $data['country_code'],
    //             'telephone' => $data['telephone'],
    //             'initial_message' => $data['initial_message'],
    //             'telegram_link' => $data['telegram_link'],
    //             'messenger_link' => $data['messenger_link'],
    //             'instagram_link' => $data['instagram_link'],
    //             'cookies' => $data['cookies'],
    //             'tag_head' => $data['tag_head'],
    //             'tag_body' => $data['tag_body'],
    //         ]);
    //         $page->load('setting');
    //     }
    
    //    return $page;
    // }

    // public function updateOrCreatePixel($settingId, $pixelId, $type)
    // {
    //     return PagePixelSetupModel::updateOrCreate(
    //         ['setting_id' => $settingId, 'pixel_id' => $pixelId],
    //         ['type' => $type]
    //     );
    // }

    // // In PageFitRepository
    // public function updateOrCreateFit($settingId, $newElementCode, $pageElementCode)
    // {
    //     return PageFitModel::updateOrCreate(
    //         ['setting_id' => $settingId, 'new_element_code' => $newElementCode],
    //         ['page_element_code' => $pageElementCode]
    //     );
    // }
    public function getAllPages($request){
        // $alldomains = DomainsModel::get()->toArray();
        // $allPages = PagesModel::with(['setting', 'domain', 'setting.pixel', 'setting.pageFit'])->get();
        // $allPagesArray = $allPages->toArray();
        // // foreach ($allPagesArray as &$page) {
        //     $allPagesArray['allDomains'] = $alldomains;
        // // }
        // // return
        // $response = [
        //     'pages' => $allPages->toArray(),
        //     'allDomains' => $alldomains
        // ];
        $perPage = $request->per_page ?? 10;
        $userId = auth()->id(); // Get the ID of the authenticated user

        $alldomains = DomainsModel::where('user_id', $userId)->paginate(); // Fetch domains associated with the authenticated user

        $allPages = PagesModel::whereHas('domain', function ($query) use ($userId) {
            $query->where('user_id', $userId); // Ensure the domain belongs to the user
        })
        ->withoutTrashed() // Exclude soft-deleted pages
        ->with(['setting', 'domain', 'setting.pixel', 'setting.pageFit'])
        ->paginate(); // Fetch pages associated with domains of the authenticated user

        $allPagesArray = $allPages->toArray();

        $response = [
            'pages' => $allPagesArray,
            'allDomains' => $alldomains
        ];
    
        return $response;
    }

    public function getPageSetting($id){
        $userId = auth()->id();
        $getPage = PagesModel::whereHas('domain', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('uuid', $id)->with(['setting', 'domain', 'setting.pixel', 'setting.pageFit'])->first(); 

        $allPagesArray = $getPage->toArray();
        // $newPixelFormat = [];
        // foreach($allPagesArray as $allPage){
        //     foreach ($allPage['setting']['pixel'] as $pixel) {
        //         $type = strtolower($pixel['type']);
        //         $newPixelFormat[$type][] = [
        //             'pixel_id' => $pixel['pixel_id'],
        //             'type' => $pixel['type']
        //         ];
        //     }
        // }
        // $allPagesArray['pixel'] = $newPixelFormat;
        
        $response = [
            'page' => $allPagesArray,
        ];
        return $response;

    }
}
