<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class UserController extends Controller
{
    //
    public function getAllUsers(Request $request){
        try{
            //check if data exists in cache
            $cachedUsers = Cache::get('allUsers');
            if($cachedUsers){
                return response()->json([
                    'message' => 'All users successfully retrieved from cache',
                    'code' => 'success',
                    'data' => json_decode($cachedUsers)
                ]);
            }
            //no data exists in cache. query the database for list of users and store in cache
            $allUsers = User::all()->toArray();
            Cache::put('allUsers', json_encode($allUsers));
            return response()->json([
                'message' => 'All users successfully retrieved from database',
                'code' => 'success',
                'data' => $allUsers
            ]);
        }catch(Exception $e){
            return response()->json([
                'message' => 'All users successfully retrieved from database',
                'code' => 'error',
                'data' => 'An error occured while fetching all users. ' . $e->getMessage()
            ]);
        }
    }
}
