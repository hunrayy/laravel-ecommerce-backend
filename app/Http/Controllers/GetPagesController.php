<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Pages;
use Illuminate\Support\Facades\Redis;


class GetPagesController extends Controller
{
    //
    public function index(Request $request){
        $page = $request->query('page');

        if($page == "shippingPolicy"){

            return $this->getShippingPolicy();

        }else if($page == 'refundPolicy'){
            
            return $this->getRefundPolicy();

        }else if($page == 'deliveryPolicy'){

            return $this->getDeliveryPolicy();
        }
    }
    public function getShippingPolicy(){
        $cachedData = Redis::get('shippingPolicy');
        if ($cachedData) {
            return response()->json([
                'code' => 'success',
                'message' => 'Shipping policy page successfully retrieved from cache',
                'data' => json_decode($cachedData, true)
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

            Redis::set('shippingPolicy', json_encode($pageData, true));
            
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
        try{
            $cachedData = Redis::get('refundPolicy');

            if ($cachedData) {
                return response()->json([
                    'code' => 'success',
                    'message' => 'Refund policy page successfully retrieved from cache',
                    'data' => json_decode($cachedData, true)
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
    
                Redis::set('refundPolicy', json_encode($pageData, true));
                
                return response()->json([
                    'code' => 'success',
                    'message' => 'Refund policy page successfully retrieved from database',
                    'data' => $pageData
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'code' => 'error',
                'message' => 'Page not found in database',
                "reason" => $e->getMessage()
            ]);
        }
    }


    public function getDeliveryPolicy(){
        $cachedData = Redis::get('deliveryPolicy');

        if ($cachedData) {
            return response()->json([
                'code' => 'success',
                'message' => 'Delivery policy page successfully retrieved from cache',
                'data' => json_decode($cachedData, true)
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

            Redis::set('deliveryPolicy', json_encode($pageData, true));
            
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
