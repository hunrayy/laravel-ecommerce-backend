<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Pages;
use Illuminate\Support\Facades\Redis;


class EditPagesController extends Controller
{
    //
    public function index(Request $request){
        $page = $request->input('page');

        if($page == "shippingPolicy"){

            return $this->EditShippingPolicy($request);

        }else if($page == 'refundPolicy'){
            
            return $this->EditRefundPolicy($request);

        }else if($page == 'deliveryPolicy'){

            return $this->EditDeliveryPolicy($request);
        }
        
    }

    public function EditShippingPolicy($request){
        try {
            // Find the page by its unique identifier
            $page = Pages::where('page', 'shippingPolicy')->first();

            if (!$page) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Shipping policy page not found',
                ]);
            }

            // Update the corresponding section based on the provided input
            switch ($request->section) {
                case 'title':
                    $page->title = $request->content;
                    break;
                case 'section-0':
                    $page->firstSection = $request->content;
                    break;
                case 'section-1':
                    $page->secondSection = $request->content;
                    break;
                case 'section-2':
                    $page->thirdSection = $request->content;
                    break;
                default:
                    return response()->json([
                        'code' => 'error',
                        'message' => 'Invalid section specified',
                    ]);
            }

            // Save the updated page
            $page->save();

            // Update the cache with the new data
            Cache::put('shippingPolicy', json_encode([
                'title' => $page->title,
                'firstSection' => $page->firstSection,
                'secondSection' => $page->secondSection,
                'thirdSection' => $page->thirdSection,
            ], true));

            return response()->json([
                'code' => 'success',
                'message' => 'Shipping policy page successfully updated',
            ]);

        } catch (\Exception $error) {
            return response()->json([
                'code' => 'error',
                'message' => 'An error occurred while updating the page',
                'reason' => $error->getMessage(),
            ]);
        }
    }


    public function editRefundPolicy($request){
        try {
            // Find the page by its unique identifier
            $page = Pages::where('page', 'refundPolicy')->first();

            if (!$page) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Refund policy page not found',
                ]);
            }

            // Update the corresponding section based on the provided input
            switch ($request->section) {
                case 'title':
                    $page->title = $request->content;
                    break;
                case 'section-0':
                    $page->firstSection = $request->content;
                    break;
                case 'section-1':
                    $page->secondSection = $request->content;
                    break;
                case 'section-2':
                    $page->thirdSection = $request->content;
                    break;
                case 'section-3':
                    $page->fourthSection = $request->content;
                    break;
                case 'section-4':
                    $page->fifthSection = $request->content;
                    break;
                case 'section-5':
                    $page->sixthSection = $request->content;
                    break;
                case 'section-6':
                    $page->seventhSection = $request->content;
                    break;
                case 'section-7':
                    $page->eighthSection = $request->content;
                    break;
                default:
                    return response()->json([
                        'code' => 'error',
                        'message' => 'Invalid section specified',
                    ]);
            }

            // Save the updated page
            $page->save();

            // Update the cache with the new data
            Cache::put('refundPolicy', json_encode([
                'title' => $page->title,
                'firstSection' => $page->firstSection,
                'secondSection' => $page->secondSection,
                'thirdSection' => $page->thirdSection,
                'fourthSection' => $page->fourthSection,
                'fifthSection' => $page->fifthSection,
                'sixthSection' => $page->sixthSection,
                'seventhSection' => $page->seventhSection,
                'eighthSection' => $page->eighthSection,
            ], true));

            return response()->json([
                'code' => 'success',
                'message' => 'Refund policy page successfully updated',
            ]);

        } catch (\Exception $error) {
            return response()->json([
                'code' => 'error',
                'message' => 'An error occurred while updating the page',
                'reason' => $error->getMessage(),
            ]);
        }
    }


    public function editDeliveryPolicy($request){
        try {
            // Find the page by its unique identifier
            $page = Pages::where('page', 'deliveryPolicy')->first();

            if (!$page) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Delivery policy page not found',
                ]);
            }

            // Update the corresponding section based on the provided input
            switch ($request->section) {
                case 'title':
                    $page->title = $request->content;
                    break;
                case 'section-0':
                    $page->firstSection = $request->content;
                    break;
                case 'section-1':
                    $page->secondSection = $request->content;
                    break;
                case 'section-2':
                    $page->thirdSection = $request->content;
                    break;
                case 'section-3':
                    $page->fourthSection = $request->content;
                    break;
                case 'section-4':
                    $page->fifthSection = $request->content;
                    break;
                case 'section-5':
                    $page->sixthSection = $request->content;
                    break;
                case 'section-6':
                    $page->seventhSection = $request->content;
                    break;
                case 'section-7':
                    $page->eighthSection = $request->content;
                    break;  
                case 'section-8':
                    $page->eninthSection = $request->content;
                    break;
                case 'section-9':
                    $page->tenthSection = $request->content;
                    break;
                case 'section-10':
                    $page->eleventhSection = $request->content;
                    break;
                case 'section-11':
                    $page->twelfthSection = $request->content;
                    break;
                default:
                    return response()->json([
                        'code' => 'error',
                        'message' => 'Invalid section specified',
                    ]);
            }

            // Save the updated page
            $page->save();

            // Update the cache with the new data
            Cache::put('deliveryPolicy', json_encode([
                'title' => $page->title,
                'firstSection' => $page->firstSection,
                'secondSection' => $page->secondSection,
                'thirdSection' => $page->thirdSection,
                'fourthSection' => $page->fourthSection,
                'fifthSection' => $page->fifthSection,
                'sixthSection' => $page->sixthSection,
                'seventhSection' => $page->seventhSection,
                'eighthSection' => $page->eighthSection,
                'ninthSection' => $page->ninthSection,
                'tenthSection' => $page->tenthSection,
                'eleventhSection' => $page->eighthSection,
                'twelfthSection' => $page->eighthSection,
            ], true));

            return response()->json([
                'code' => 'success',
                'message' => 'Delivery policy page successfully updated',
            ]);

        } catch (\Exception $error) {
            return response()->json([
                'code' => 'error',
                'message' => 'An error occurred while updating the page',
                'reason' => $error->getMessage(),
            ]);
        }
    }
}
