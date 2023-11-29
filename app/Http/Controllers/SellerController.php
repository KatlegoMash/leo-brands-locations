<?php

namespace App\Http\Controllers;

use App\Http\Resources\SellersCollection;
use App\Http\Resources\SellersResource;
use App\Placement;
use App\Seller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use File;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;


class SellerController extends Controller
{
    /**
     * @return Factory|Application|View
     */
    public function getIndex()
    {
        return view('sellers.maintainer',[
            'collection'=>Seller::get()
        ]);
    }

    /**
     * @return SellersCollection
     */
        public function getReturnJsonObject()
    {
        return new SellersCollection(SellersResource::collection(Seller::all()));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadJson()
    {
        $fileName = time() . 'seller.json';

        $directoryPath = public_path('/upload/json/');
        if (!File::exists($directoryPath))
        {
            File::makeDirectory($directoryPath, 0755, true);
        }

        $fileStorePath = public_path('/upload/json/'.$fileName);

        File::put($fileStorePath, json_encode(new SellersCollection(SellersResource::collection(Seller::all()))));

        return response()->download($fileStorePath);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadSelectedJson(Request $request)
    {
        if($request->selectedSellers == null)
        {
            return redirect()->to('seller/index')->with('success','Please select a Seller!');
        }
        else
        {
            // Validate the selectedSellerIds
            $validator = Validator::make($request->all(), [
                'selectedSellers' => ['required', 'array', Rule::exists('sellers', 'id')],
            ]);

            if ($validator->fails()) {
                return redirect()->route('sellerjson-maintainer')->with('error','Select a seller first!');
            }
            // Get the selected seller IDs from the request
            $selectedSellerIds = $request->input('selectedSellers');

            // Query the database to retrieve selected sellers by their IDs
            $selectedSellers = Seller::whereIn('id', $selectedSellerIds)->get();

            // Generate a unique filename
            $fileName = time() . 'selected_sellers.json';

            // Define the directory path where the JSON file will be stored
            $directoryPath = public_path('/upload/json/');
            if (!File::exists($directoryPath)) {
                File::makeDirectory($directoryPath, 0755, true);
            }

            // Define the file storage path
            $fileStorePath = public_path('/upload/json/' . $fileName);

            // Create an array to store the transformed seller data
            $sellerData = [];

            // Transform each selected seller and add it to the array
            foreach ($selectedSellers as $seller) {
                $sellerData[] = new SellersResource($seller);
            }

            // Encode the array of transformed sellers to JSON
            $jsonData = json_encode(new SellersCollection(SellersResource::collection(collect($sellerData))));

            // Write the JSON data to the file
            File::put($fileStorePath, $jsonData);

            // Return a response to download the JSON file
            return response()->download($fileStorePath);
        }

    }
    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function getCreate()
    {
        return view('sellers.creator',[
            'seller_type'=>[
                'PUBLISHER' => 'PUBLISHER',
                'INTERMEDIARY' => 'INTERMEDIARY',
                'BOTH' => 'BOTH',
            ],
            'placements'=>Placement::all()
        ]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function postStore(Request $request): RedirectResponse
    {
        request()->validate([
            'seller_id' => 'required',
            'name' => 'required',
            'domain' => 'required',
            'seller_type' => 'required',
            'is_passthrough' => 'required|bool',
            'is_confidential' => 'required|bool',
        ]);

        // Create the Seller
        $seller = Seller::create($request->all());

        // Attach the selected placement(s) to the seller
        if ($request->has('placements')) {
            $seller->placements()->attach($request->input('placements'));
        }

        // Manually create the created_at timestamp on SellerPlacement model
        foreach ($seller->placements as $placement) {
            $placement->pivot->created_at = now();
            $placement->pivot->save();
        }

        return redirect()->to('seller/index')->with('success','Seller created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Factory|Application|View
     */
    public function getEdit($id)
    {
        return view('sellers.edit',[
            'seller'=> Seller::find($id),
            'seller_type'=>[
                'PUBLISHER' => 'PUBLISHER',
                'INTERMEDIARY' => 'INTERMEDIARY',
                'BOTH' => 'BOTH',
            ],
            'placements'=>Placement::all()
        ]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return RedirectResponse
     */
    public function postUpdate(Request $request, $id): RedirectResponse
    {
        request()->validate([
            'seller_id' => 'required',
            'name' => 'required',
            'domain' => 'required',
            'seller_type' => 'required',
            'is_passthrough' => 'required|int',
            'is_confidential' => 'required|bool',
        ]);

        $sellerData = $request->only(['seller_id', 'name', 'domain', 'seller_type', 'is_passthrough']);

        // Update Seller
        Seller::where('id', $id)->update($sellerData);

        // Update placements
        $seller = Seller::find($id);

        // Sync placements, removing any unselected placements
        $seller->placements()->sync($request->input('placements'));
        // Manually create the created_at timestamp on SellerPlacement
        foreach ($seller->placements as $placement) {
            $placement->pivot->updated_at = now();
            $placement->pivot->save();
        }
        return redirect()->to('seller/index')->with('success', 'Seller updated successfully');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function postDestroy($id): RedirectResponse
    {
        Seller::where('id',$id)->delete();

        return redirect()->to('seller/index')
            ->with('success','Seller deleted successfully');
    }
}
