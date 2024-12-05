<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RedirectController extends Controller
{
    public function fetch(Request $request){
        try {
            $url = $request->query('url');

            $response = Http::get($url);
            if ($response->successful()) {
                // print_r($response);die();
                $content = $response->body();
                // Modify the content to handle relative URLs
                $baseUrl = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);
                $content = str_replace('src="/', 'src="' . $baseUrl . '/', $content);
                $content = str_replace('href="/', 'href="' . $baseUrl . '/', $content);
                // return response($content)
                // ->header('Content-Type', 'text/html');
                return view('redirectPage', ['content' => $content]);
            } else {
                // return response('Error fetching the page', 500);
                return response( ['error' => 'Error fetching the page: ' . $response]);
            }
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500);
        }
    }
}



