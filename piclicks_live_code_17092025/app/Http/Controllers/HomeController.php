<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Repository\Eloquent\{DesignCollageRepository};
use App\Repository\Admin\{CollectionRepository, TagRepository};
use App\Models\{DesignCollageMaster, ArtGalleryFavourite};
use DB;

class HomeController extends Controller
{
    protected $DesignCollageRepository, $CollectionRepository, $TagRepository;
    public function __construct(DesignCollageRepository $DesignCollageRepository, CollectionRepository $CollectionRepository, TagRepository $TagRepository)
    {
        $this->DesignCollageRepository = $DesignCollageRepository;
        $this->CollectionRepository = $CollectionRepository;
        $this->TagRepository = $TagRepository;
    }


    public function index()
    {
        try {
            return view('front.index');
        } catch (\Exception $e) {
            Log::error('Error in HomeController/index :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }


    // wallart page
    public function artGallery()
    {
        try {
            $designCollages = $this->DesignCollageRepository->getByWhereMaster(['user_type' => 'admin', 'status' => 0, 'user_id' => 1]);


            $collections = $this->CollectionRepository->getAll();
            $tags = $this->TagRepository->getAll();

            return view('front.art-gallery', compact('designCollages', 'collections', 'tags'));
        } catch (\Exception $e) {
            Log::error('Error in HomeController/artGallery :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function refreshToken()
    {
        return response()->json([
            'csrf_token' => csrf_token()
        ]);
    }

    public function getFilteredArtGallery(Request $request)
    {
        try {
            $user_id = auth()->check() ? auth()->id() : null;
            $guest_id = getUserId();
            $collections = $request->collections ?? [];

            $query = DesignCollageMaster::select(
                'design_collage_master.*',
                'admin_data.tag_id',
                'admin_data.collection_id',
                'admin_data.title',
                'admin_data.short_description',
                'admin_data.designer_name',
                'admin_data.amount'
            )
                ->join('design_collage_admins as admin_data', 'design_collage_master.unique_id', '=', 'admin_data.unique_id')
                ->with(['favorites_data' => function ($query) use ($user_id, $guest_id) {
                    $query->when($user_id, fn($q) => $q->where('user_id', $user_id))
                        ->when(!$user_id, fn($q) => $q->where('guest_id', $guest_id));
                }])
                ->where('user_type', 'admin')
                ->where('status', 0)
                ->where('design_collage_master.user_id', 1);

            if (in_array(0, $collections)) {
                $query->join('artgallery_favorites as fav', 'design_collage_master.unique_id', '=', 'fav.unique_id')
                    ->when($user_id, fn($q) => $q->where('fav.user_id', $user_id))
                    ->when(!$user_id, fn($q) => $q->where('fav.guest_id', $guest_id));
            } else {
                // Normal collection filter
                if (!empty($collections)) {
                    $query->whereIn('admin_data.collection_id', $collections);
                }
            }

            if ($request->filled('tags')) {
                $query->where(function ($subQuery) use ($request) {
                    foreach ($request->tags as $tagId) {
                        $subQuery->orWhereJsonContains('tag_id', $tagId);
                    }
                });
            }

            if ($request->filled('search')) {

                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {

                    $q->where('title', 'like', '%' . $searchTerm . '%')
                        ->orWhere('designer_name', 'like', '%' . $searchTerm . '%');


                    // Tag name search using tag_id JSON and tags table
                    $q->orWhere(function ($subQ) use ($searchTerm) {
                        $subQ->whereExists(function ($tagQuery) use ($searchTerm) {
                            $tagQuery->select(DB::raw(1))
                                ->from('tags')
                                ->whereRaw('JSON_CONTAINS(admin_data.tag_id, JSON_QUOTE(CAST(tags.id AS CHAR)))')
                                ->where('tags.name', 'like', '%' . $searchTerm . '%');
                        });
                    });
                });
            }

            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('admin_data.amount', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('admin_data.amount', 'desc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('design_collage_master.created_at', 'desc');
            }

            $designCollages = $query->get();
            // dd($designCollages);

            $html = view('front.partials.art-gallery-grid', compact('designCollages'))->render();
            return response()->json(['status' => 1, 'data' => $html]);
        } catch (\Exception $e) {
            Log::error('Error in HomeController/getFilteredArtGallery :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => 0, 'data' => '', 'message' => __('message.statusZero')]);
        }
    }

    public function toggleFavorite(Request $request)
    {
        $unique_id = $request->input('unique_id');
        $user_id = auth()->check() ? auth()->id() : null;
        $guest_id = getUserId();

        $favorite = ArtGalleryFavourite::where('unique_id', $unique_id)
            ->when($user_id, fn($q) => $q->where('user_id', $user_id))
            ->when(!$user_id, fn($q) => $q->where('guest_id', $guest_id))
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['status' => 'removed']);
        } else {
            ArtGalleryFavourite::create([
                'unique_id' => $unique_id,
                'user_id' => $user_id,
                'guest_id' => !$user_id ? $guest_id : null
            ]);
            return response()->json(['status' => 'added']);
        }
    }
}
