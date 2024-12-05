<?php

namespace App\Services;

use App\Contracts\PagesInterface;
use App\Models\PagesModel;
use Illuminate\Support\Facades\Validator;
use App\Repositories\PagesRepository;

class PagesService implements PagesInterface
{

    public function __construct(PagesRepository $pagesRepository){
        $this->pagesRepository = $pagesRepository;
     }

     public function createPage($data){
        $validator = Validator::make($data, [
            // 'name' => 'required|string|max:255',
            'domain_id' => 'required',
            'sub_domain' => 'required',
            'redirect_url' => 'required',
        ]);

        if ($validator->fails()) {
            return [
                'data' => ['status' => 422,'errors' => $validator->errors()]
            ];
        }
        $checkDomainExist = $this->pagesRepository->checkIfDomainExist($data['domain_id']);
        if($checkDomainExist){
            $createdPage = $this->pagesRepository->create($data);
            // print_r($createdPage);die();
            return [
                'data' => ['status' => 200,'message' => 'Page Added successfully','data' => $createdPage],
            ];
        }else{
            return [
                'data' => ['status' => 404,'message' => 'Domain not found'],
            ];
        }
     }

     public function updatePage($data, $id){
        $page = $this->pagesRepository->checkPageExist($id);
        
        if(!$page){
            return [
                'data' => ['status' => 404,'message' => 'Page not found'],
            ];
        }
        
        $pageSetting = null;
        // Check if page setting exists
        if ($id) {
            $pageSetting = $this->pagesRepository->checkPageSettingsExist($id);
        }

        // Prepare data for page_settings table
        $pageSettingsData = [
            'country_code' => $data['country_code'],
            'telephone' => $data['telephone'],
            'initial_message' => $data['initial_message'],
            'telegram_link' => $data['telegram_link'],
            'messenger_link' => $data['messenger_link'],
            'instagram_link' => $data['instagram_link'],
            'cookies' => $data['cookies'],
            'tag_head' => $data['tag_head'],
            'tag_body' => $data['tag_body'],
            'page_id' => $id,
        ];
        // print_r($pageSetting);die();

        // If page setting doesn't exist, create a new one
        if (!$pageSetting) {
            $pageSetting = $this->pagesRepository->createPageSetting($pageSettingsData);
            $id = $pageSetting->id;
            // die(';l,lk');
        } else {
            // Update the existing page setting
            $pageSetting = $this->pagesRepository->updatePageSetting($id, $pageSettingsData);
            // die('cgfngh');
        }

        if ($pageSetting) {
            // Delete existing pixel and fit records for this setting
            $this->pagesRepository->deleteExistingPixels($pageSetting->id);
            $this->pagesRepository->deleteExistingFits($pageSetting->id);

            // Handle pixel setup
            if (isset($data['pixel_setup'])) {
                foreach ($data['pixel_setup'] as $type => $pixels) {
                    foreach ($pixels as $pixel) {
                        $this->pagesRepository->createPixel($pageSetting->id, $pixel['pixel_id'], $type);
                    }
                }
            }

            // Handle page fit setup
            if (isset($data['pageFit'])) {
                foreach ($data['pageFit'] as $fit) {
                    $this->pagesRepository->createFit($pageSetting->id, $fit['newElementCode'], $fit['pageElementCode']);
                }
            }
        }

        return [
            'data' => ['status' => 200,'message' => 'Page settings saved successfully','data' => $pageSetting],
        ];
    }

    public function getAllPages($request){
        $page = $this->pagesRepository->getAllPages($request);
        return [
            'data' => ['status' => 200,'message' => 'success', 'pages' => $page],
        ];
    }

    public function delPage($id){
            $deletePage = $this->pagesRepository->deletePage($id);
            if ($deletePage) {
                return [
                    'data' => ['status' => 200,'message' => 'Page deleted successfully']
                ];
            } else {
                return [
                    'data' => ['status' => 404,'message' => 'Page not found.']
                ];
            }
    }

    public function getPageSetting($id){
        $pageSetting = $this->pagesRepository->getPageSetting($id);
        if ($pageSetting) {
            return [
                'data' => ['status' => 200,'message' => 'success', 'data' => $pageSetting]
            ];
        } else {
            return [
                'data' => ['status' => 404,'message' => 'Page not found.']
            ];
        }
    }
    
}