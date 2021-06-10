<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Traits\apiResponseTrait;
use App\Models\Customer;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Barcode;
use Illuminate\Support\Str;
class ApiController extends Controller
{
    use apiResponseTrait;
    
    public function auth(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'telephone' => 'required|regex:/(01)[0-9]{9}/',
            
        ]);

        if($validator->fails()){
            return $this->setError($validator->errors());
        }
        $customer = Customer::where('telephone',$request->telephone)->first();
        if(!empty($customer)){
            return $this->setSuccess('Fetch User Successfully',$customer);
        }else{
            $newCustomer = Customer::create($request->all());
            return $this->setSuccess('Create User Successfully',$newCustomer);
            
        }
      
    }

    public function addBrand(Request $request)
    {
        $data = $request->except('photo');
        if ($request->hasFile('photo')) {

            $file = $request->file("photo");
            $filename = Str::random(6) . '_' . time() . '_' . $file->getClientOriginalName();
            $path = 'ProjectFiles/BrandPhotos';
            $file->move($path, $filename);
            $data['photo'] = $path . '/' . $filename;
        }
        $brand = Brand::create($data);
        return $this->setSuccess('Create Brand Successfully',$brand);
    }

    public function getBrands()
    {
        $brands = Brand::get();
        if($brands)
        {
            return $this->setSuccess('Fetch Brands Successfully',$brands);
        }else{
            return $this->notFoundResponse();
        }
    }

    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_id' => 'required',
            
        ]);

        if($validator->fails()){
            return $this->setError($validator->errors());
        }
        $brand = Brand::where('id',$request->brand_id )->first();
        if(!$brand)
        {
            return $this->setError("Brand id not found");
        }

        $data = $request->except('photo');
        if ($request->hasFile('photo')) {

            $file = $request->file("photo");
            $filename = Str::random(6) . '_' . time() . '_' . $file->getClientOriginalName();
            $path = 'ProjectFiles/BrandPhotos';
            $file->move($path, $filename);
            $data['photo'] = $path . '/' . $filename;
        }
        $product = Product::create($data);
        return $this->setSuccess('Create Product Successfully',$product);
    }

    public function getProducts()
    {
        $products = Product::with('Brand')->get();
        if($products)
        {
            return $this->setSuccess('Fetch Products Successfully',$products);
        }else
        {
            return $this->notFoundResponse();
        }
    }

    public function addBarcode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|unique:barcodes,code',
            'product_id' => 'required',
            
        ]);

        if($validator->fails()){
            return $this->setError($validator->errors());
        }
        $product = Product::where('id',$request->product_id )->first();
        if(!$product)
        {
            return $this->setError("Product id not found");
        }

        $data = $request->all();
        
        $barcode = Barcode::create($data);
        return $this->setSuccess('Create Barcode Successfully',$barcode);
    }

    public function getBarcodes()
    {
        $barcodes = Barcode::with('Product')->get();
        if($barcodes)
        {
            return $this->setSuccess('Fetch Barcodes Successfully',$barcodes);
        }else
        {
            return $this->notFoundResponse();
        }
    }

    public function searchBarcode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',           
        ]);

        if($validator->fails()){
            return $this->setError($validator->errors());
        }

        $barcode = Barcode::where('code',$request->code)->with('Product')->first();
        if($barcode)
        {
            if($barcode->scan_before == 0 ){
                $update_barcode = Barcode::where('code',$barcode->code)->first();
                $update_barcode->scan_before = 1;
                $update_barcode->save();
                return $this->setSuccess('Barcode available',$barcode);
            }else{
                return $this->setSuccess('Barcode available and scaned before',$barcode);
                
            }
        }else{
            return $this->setError('Barcode not available');
        }
    }

    public function getProductsByBrand(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_id' => 'required',           
        ]);

        if($validator->fails()){
            return $this->setError($validator->errors());
        }
        $brand = Brand::where('id',$request->brand_id )->first();
        if(!$brand)
        {
            return $this->setError("Brand id not found");
        }
        $products = Product::where('brand_id',$request->brand_id)->with('Brand')->get();
        if($products)
        {
            return $this->setSuccess('Fetch Products Successfully',$products);
        }else
        {
            return $this->notFoundResponse();
        }
    }
}
