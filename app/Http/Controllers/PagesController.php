<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Pages;


class PagesController extends Controller
{
    //
    public function index(Request $request){
        $page = $request->query('page');

        if($page == "shippingPolicy"){

            return $this->getShippingPolicy();

        }else if($page == 'efundPolicy'){
            
            return $this->getRefundPolicy();

        }else if($page == 'deliveryPolicy'){

            return $this->getDeliveryPolicy();
        }
    }
    public function getShippingPolicy(){
        $cachedData = Cache::get('shippingPolicy');

        if ($cachedData) {
            return response()->json([
                'code' => 'success',
                'message' => 'Shipping policy page successfully retrieved from cache',
                'data' => $cachedData
            ]);
        }

        $feedback = Pages::where("page", 'shippingPolicy')->first();
        if($feedback){
            $pageData = [
                'title' => $feedback->title,
                'firstSection' => $feedback->firstSection,
                'secondSection' => $feedback->secondSection,
                'thirdSection' => $feedback->thirdSection,
            ];

            Cache::put('shippingPolicy', $pageData);
            
            return response()->json([
                'code' => 'success',
                'message' => 'Shipping policy page successfully retrieved from database',
                'data' => $pageData
            ]);
        }
        return response()->json([
            'code' => 'error',
            'message' => 'Page not found in database'
        ]);
    }

    public function getRefundPolicy(){
        $cachedData = Cache::get('refundPolicy');

        if ($cachedData) {
            return response()->json([
                'code' => 'success',
                'message' => 'Refund policy page successfully retrieved from cache',
                'data' => $cachedData
            ]);
        }

        $feedback = Pages::where("page", 'refundPolicy')->first();
        if($feedback){
            $pageData = [
                'title' => $feedback->title,
                'firstSection' => $feedback->firstSection,
                'secondSection' => $feedback->secondSection,
                'thirdSection' => $feedback->thirdSection,
                'fourthSection' => $feedback->fourthSection,
                'fifthSection' => $feedback->fifthSection,
                'sixthSection' => $feedback->sixthSection,
                'seventhSection' => $feedback->seventhSection,
                'eighthSection' => $feedback->eighthSection,

            ];

            Cache::put('refundPolicy', $pageData);
            
            return response()->json([
                'code' => 'success',
                'message' => 'Refund policy page successfully retrieved from database',
                'data' => $pageData
            ]);
        }
        return response()->json([
            'code' => 'error',
            'message' => 'Page not found in database'
        ]);
    }


    public function getDeliveryPolicy(){
        $cachedData = Cache::get('deliveryPolicy');

        if ($cachedData) {
            return response()->json([
                'code' => 'success',
                'message' => 'Delivery policy page successfully retrieved from cache',
                'data' => $cachedData
            ]);
        }

        $feedback = Pages::where("page", 'deliveryPolicy')->first();
        if($feedback){
            $pageData = [
                'title' => $feedback->title,
                'firstSection' => $feedback->firstSection,
                'secondSection' => $feedback->secondSection,
                'thirdSection' => $feedback->thirdSection,
                'fourthSection' => $feedback->fourthSection,
                'fifthSection' => $feedback->fifthSection,
                'sixthSection' => $feedback->sixthSection,
                'seventhSection' => $feedback->seventhSection,
                'eighthSection' => $feedback->eighthSection,
                'ninthSection' => $feedback->ninthSection,
                'tenthSection' => $feedback->tenthSection,
                'eleventhSection' => $feedback->eleventhSection,
                'twelfthSection' => $feedback->twelfthSection,
            ];

            Cache::put('deliveryPolicy', $pageData);
            
            return response()->json([
                'code' => 'success',
                'message' => 'Delivery policy page successfully retrieved from database',
                'data' => $pageData
            ]);
        }
        return response()->json([
            'code' => 'error',
            'message' => 'Page not found in database'
        ]);
    }
}
