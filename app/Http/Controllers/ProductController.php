<?php

// namespace App\Http\Controllers;
// use Illuminate\Support\Facades\Validator;
// // use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
// use Cloudinary\Cloudinary;

// use App\Models\Product; 

// use Illuminate\Http\Request;

// class ProductController extends Controller
// {
//     //

//     public function createProduct(Request $request){
//         // Validate request input
//         $validator = Validator::make($request->all(), [
//             'productName' => 'required|string|max:255',
//             'productPrice' => 'required|numeric',
//             'productImage' => 'image|mimes:jpeg,png,jpg,gif',
//         ]);
//         if ($validator->fails()) {
//             return response()->json([
//                 'message' => 'All fields are required.',
//                 'code' => 'error',
//                 'errors' => $validator->errors()
//             ]);
//         }

//         try{
//             // Handle file uploads
//             $uploadedProductImage = $this->uploadToCloudinary($request->file('productImage'));
//             $uploadedSubImage1 = $this->uploadToCloudinary($request->file('subImage1'));
//             $uploadedSubImage2 = $this->uploadToCloudinary($request->file('subImage2'));
//             $uploadedSubImage3 = $this->uploadToCloudinary($request->file('subImage3'));

//             // Create new product
//             Product::create([
//                 // 'id' => 
//                 'productName' => $request->input('productName'),
//                 'productPriceInNaira' => $request->input('productPrice'),
//                 'productImage' => $uploadedProductImage,
//                 'subImage1' => $uploadedSubImage1,
//                 'subImage2' => $uploadedSubImage2,
//                 'subImage3' => $uploadedSubImage3
//             ]);
//             return response()->json([
//                 'message' => 'Product created successfully.',
//                 'code' => 'success',
//             ]);
//         }catch(\Exception $e){
//             return response()->json([
//                 'message' => 'Error creating product.',
//                 'code' => 'error',
//                 'reason' => $e->getMessage()
//             ]);
//         }
//     }
//     /**
//      * Upload file to Cloudinary
//      *
//      * @param \Illuminate\Http\UploadedFile $file
//      * @return string $url - The uploaded image URL
//      */
//     // public function uploadToCloudinary($file){
//     //     if (!$file) {
//     //         return null; // If no file is uploaded, return null
//     //     }

//     //     $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
//     //         'folder' => env('FOLDER_FOR_IMAGES_IN_CLOUDINARY'), // Optional: Set a folder name in Cloudinary for organizing images
//     //         'resource_type' => 'image'
//     //     ])->getSecurePath(); // Fetch the secure URL of the uploaded image

//     //     return $uploadedFileUrl;
//     // }




//     public function uploadToCloudinary($file)
//     {
    
//         $cloudinary = new Cloudinary();
    
//         // Upload the image
//         $uploadedImage = $cloudinary->upload($file->getRealPath(), [
//             'folder' => env('FOLDER_FOR_IMAGES_IN_CLOUDINARY'),
//             'resource_type' => 'image',
//         ]);
    
//         // Get the secure URL of the uploaded image
//         $imageUrl = $uploadedImage->getSecurePath();
    
//         // You can now save $imageUrl to your database or return it in your response
//         return $image;
//     }
// }












































namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
// use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Cloudinary\Cloudinary;

use App\Models\Product; 

use Illuminate\Http\Request;

class ProductController extends Controller
{
    //
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);
    }


    public function createProduct(Request $request){
        // Validate request input
        $validator = Validator::make($request->all(), [
            'productName' => 'required|string|max:255',
            'productPrice' => 'required|numeric',
            'productImage' => 'image|mimes:jpeg,png,jpg,gif',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'All fields are required.',
                'code' => 'error',
                'errors' => $validator->errors()
            ]);
        }

        try{
            // Handle file uploads
            $uploadedProductImage = $this->uploadToCloudinary($request->file('productImage'));
            $uploadedSubImage1 = $this->uploadToCloudinary($request->file('subImage1'));
            $uploadedSubImage2 = $this->uploadToCloudinary($request->file('subImage2'));
            $uploadedSubImage3 = $this->uploadToCloudinary($request->file('subImage3'));
            return $uploadedProductImage;

            // Create new product
            Product::create([
                // 'id' => 
                'productName' => $request->input('productName'),
                'productPriceInNaira' => $request->input('productPrice'),
                'productImage' => $uploadedProductImage,
                'subImage1' => $uploadedSubImage1,
                'subImage2' => $uploadedSubImage2,
                'subImage3' => $uploadedSubImage3
            ]);
            return response()->json([
                'message' => 'Product created successfully.',
                'code' => 'success',
            ]);
        }catch(\Exception $e){
            Log::error('Error occurred: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error creating product.',
                'code' => 'error',
                'reason' => $e->getMessage()
            ]);
        }
    }
    /**
     * Upload file to Cloudinary
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string $url - The uploaded image URL
     */
    // public function uploadToCloudinary($file){
    //     if (!$file) {
    //         return null; // If no file is uploaded, return null
    //     }

    //     $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
    //         'folder' => env('FOLDER_FOR_IMAGES_IN_CLOUDINARY'), // Optional: Set a folder name in Cloudinary for organizing images
    //         'resource_type' => 'image'
    //     ])->getSecurePath(); // Fetch the secure URL of the uploaded image

    //     return $uploadedFileUrl;
    // }




    public function uploadToCloudinary($file)
    {
        if(!$file){
            return null;
        }
    
        try {
            $response = $this->cloudinary->uploadApi()->upload($file);
            return $response['secure_url']; // Return or handle the response as needed
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
