<?php

namespace App\Http\Controllers;

use App\Http\Requests\V1\SaveImageRequest;
use App\Http\Resources\V1\ImageResource;
use App\Models\Image;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function index(): ResourceCollection{
        $images = Image::latest()->get();

        return ImageResource::collection($images);
    }

    public function store(SaveImageRequest $request): JsonResponse{
        
        //directorio 'imagenes'
        if(Storage::directoryMissing('images'))
           Storage::createDirectory('images');

        //guardar imagen en el directorio 'images'
        $file = $request->file('image')->store('images');

        //guardar en la base de datos
        $image = new Image($request->validated());
        $image->path = $file;
        $image->save();

        return response()->json([
            'message' => __('Image saved successfully.'),
            'data' => new ImageResource($image)
        ], Response::HTTP_CREATED);
    }

    public function show(string $id): ImageResource{
        $image = Image::findOrFail($id);

        return new ImageResource($image);
    }

    public function destroy(string $id): JsonResponse{
        $image = Image::findOrFail($id);

        //eliminar imagen del directorio 'images'
        if(Storage::fileExists($image->path))
            Storage::delete($image->path);

        //eliminar de la base de datos
        $image->delete();

        return response()->json([
            'message' => __('Image deleted successfully.')
        ], Response::HTTP_OK);
    }
}
