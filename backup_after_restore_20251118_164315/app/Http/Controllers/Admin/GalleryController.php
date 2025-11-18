<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Repository\Eloquent\DesignCollageRepository;
use App\Repository\Admin\{CollectionRepository,TagRepository};

class GalleryController extends Controller
{
    protected $DesignCollageRepository, $TagRepository, $CollectionRepository;
    public function __construct(TagRepository $TagRepository, CollectionRepository $CollectionRepository, DesignCollageRepository $DesignCollageRepository)
    {
        $this->DesignCollageRepository = $DesignCollageRepository;
        $this->CollectionRepository = $CollectionRepository;
        $this->TagRepository = $TagRepository;
    }


    public function index(Request $request)
    {
        try {
            return view('admin.gallery-list');
        } catch (Exception $e) {
            Log::error('Error in GalleryController@index: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }


    public function getGalleryList(Request $request)
    {
        try {
            $galleryData = $this->DesignCollageRepository->getByWhereMaster(['user_type' => 'admin']);
            return DataTables::of($galleryData)
                ->addIndexColumn()

                ->editColumn('image', function ($row) {
                    return asset('/storage/' . $row->image_path) ?? '';
                })

                ->editColumn('image_size', function ($row) {
                    return $row->height . ' x ' . $row->width . ' cm';
                })

                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format(' d M Y');
                })

                ->addColumn('action', function ($row) {
                    
                    $viewUrl = route('admin.view-design-collage', ['unique_id' => $row->unique_id, 'type' => 'admin']);
                    $deleteUrl = route('front.deleteCollage', ['unique_id' => $row->unique_id]);

                    return '
                         <a href="' . $viewUrl . '" class="btn btn-sm btn-primary">View</a>
                        <button class="btn btn-sm btn-danger delete-collage" data-url="' . $deleteUrl . '">Delete</button>
                    ';
                })

                ->rawColumns(['action'])

                ->make(true);
        } catch (Exception $e) {
            Log::error('Error in GalleryController@getGalleryList: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    // upload image
    public function uploadPhotos(Request $request)
    {
        try {
            $collections = $this->CollectionRepository->getAll();
            $tags = $this->TagRepository->getAll();
          
            return view('admin.upload-photos', compact('collections', 'tags'));
        } catch (Exception $e) {
            Log::error('Error in GalleryController@uploadPhotos: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }
}
