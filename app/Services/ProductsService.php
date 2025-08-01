<?php

namespace App\Services;

use App\Http\Repositories\ProductRepository;
use App\Http\Repositories\ShopRepository;
use App\Models\ProductImage;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Throwable;

class ProductsService
{

    public function __construct(public ProductRepository $repository)
    {
    }

    /**
     * @param $inputs
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function index($inputs, array $relations = []): Collection|LengthAwarePaginator
    {
        $sizes = [];
        $colors = [];
        $filterPrices = [];
        if (isset(request()->userShop)) {
            $inputs["shop_id"] = Auth::id();
        }

        if (empty($relations)) {
            $relations = ['category', 'images'];
        }

        if(isset($inputs['sizes'])){
            $sizes = $inputs['sizes'];
            unset($inputs['sizes']);
        }

        if(isset($inputs['colors'])){
            $colors = $inputs['colors'];
            unset($inputs['colors']);
        }

        if(isset($inputs['from_price']) && isset($inputs['to_price'])){
            $filterPrices = [$inputs['from_price'], $inputs['to_price']];
            unset($inputs['from_price']);
            unset($inputs['to_price']);
        }

        $query = $this->repository->queryFull(inputs: $inputs, relations: $relations);

        if (!empty($sizes)) {
            $query->where(function ($query) use ($sizes) {
                foreach ($sizes as $size) {
                    $query->orWhereJsonContains('sizes', $size);
                }
            });
        }

        if (!empty($colors)) {
            $query->whereIn('color', $colors);
        }

        if (!empty($filterPrices)) {
            $query->whereBetween('price', $filterPrices);
        }


        return $this->repository->resolve_paginate(query: $query);
    }


    /**
     * @param $inputs
     * @return array
     */
    public function searchAll($inputs): array
    {
        $contentSearch = $inputs["content_search"];

        $productInputs = $inputs;
        $productInputs["title"] = $contentSearch;

        $shopInputs = $inputs;
        $shopInputs["name"] = $contentSearch;
        $shopRepo = new ShopRepository();

        $products = $this->repository->resolve_paginate(inputs: $productInputs);

        $shops = $shopRepo->resolve_paginate(inputs: $shopInputs);


        return [
            "products"=>$products,
            "shops"=>$shops,
        ];
    }

    /**
     * @param $productId
     * @return object|null
     * @throws Exception
     */
    public function show($productId): ?object
    {
        $item = $this->repository->findWithRelations($productId);
        if ($item == null) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        if (isset(request()->userShop) && $item->shop_id != Auth::id()) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        }

        $item->related_products = $item->relatedProducts()->get();

        return $item;
    }

    /**
     * @param $inputs
     * @return mixed
     * @throws Exception
     */
    public function create($inputs): mixed
    {
        $inputs["sizes"] = $inputs["sizes"] ?? [];


        DB::beginTransaction();
        try {
            $createdItem = $this->repository->create($inputs);

            if (isset($inputs["images"])) {
                $this->saveProductImages($inputs["images"], $createdItem);
            }

            $product = $this->repository->findWithRelations($createdItem->id);
            DB::commit();
            return $product;
        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.store_failed"));
        }
    }

    /**
     * @param $inputs
     * @param $productId
     * @return mixed
     * @throws Exception
     */
    public function update($inputs, $productId): mixed
    {
        $item = $this->repository->find($productId);
        if (!$item) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        if (!isset(request()->userAdmin) && $item->shop_id != Auth::id()) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        }


        $inputs["sizes"] = $inputs["sizes"] ?? [];


        if (isset($inputs["deleted_image_ids"])) {
            $item->images()->whereIn("id", $inputs["deleted_image_ids"])->delete();
        }

        DB::beginTransaction();
        try {
            $this->repository->update($item, $inputs);

            if (isset($inputs["images"])) {
                $this->saveProductImages($inputs["images"], $item);
            }

            $product = $this->repository->findWithRelations($productId);

            DB::commit();
            return $product;
        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.update_failed"));
        }
    }

    /**
     * @param $productId
     * @return bool
     * @throws Exception
     */
    public function delete($productId): bool
    {
        $item = $this->repository->find($productId);
        if (!$item) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        if (!isset(request()->userAdmin) && $item->shop_id != Auth::id()) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        }

        DB::beginTransaction();
        try {
            $this->repository->delete($item);
            DB::commit();
            return true;
        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.delete_failed"));
        }
    }


    /**
     * @throws Exception
     */
    public function saveProductImages($images, $product): void
    {
        foreach ($images as $image) {
            try {
                $imageUrl = $this->saveImage($image['file'], $product->shop_id);
                ProductImage::query()->create([
                    'product_id' => $product->id,
                    'image' => $imageUrl,
                    'is_selected' => $image['is_selected'],
                    'is_processed' => $image['is_processed'],
                ]);
            } catch (Throwable $exception) {
                throw new Exception($exception->getMessage());
            }
        }
    }


    public function saveImage($imageFile, $folder): ?string
    {
        try {
            $extension = $imageFile->getClientOriginalExtension();
            if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'wep' || $extension == 'webp' || $extension == 'svg') {

                $newFilePath = $imageFile->getPath() . '/' . time() . rand(1, 99) . '.' . $extension;
                $imageEncoded = Image::make($imageFile->getRealPath())->save($newFilePath, 50);

                $image = new UploadedFile($imageEncoded->basePath(), $imageFile->getFilename());


                $imageName = sha1(md5(Auth::id())) . time() . rand(100, 999) . '.' . $extension;
                $path = 'products/' . $folder;
                $fullPath = Storage::putFileAs(path: $path, file: $image, name: $imageName, options: ['visibility' => 'public', 'directory_visibility' => 'public']);

                try {
                    unlink($newFilePath);
                } catch (Exception) {
                }

                return "/storage/" . $fullPath;
            }

            return null;

        } catch (Exception) {
            return null;
        }
    }


}
