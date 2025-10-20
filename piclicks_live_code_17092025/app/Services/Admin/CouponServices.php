<?php

namespace App\Services\Admin;

use App\Repository\Admin\CouponRepository;
use Illuminate\Support\Facades\Log;
use Exception;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Models\Coupon;

class CouponServices
{
    protected $CouponRepository;

    public function __construct(CouponRepository $CouponRepository)
    {
        $this->CouponRepository = $CouponRepository;
    }

    public function getCoupons()
    {
        try {
            $coupons = $this->CouponRepository->getAll();

            return DataTables::of($coupons)
                ->addIndexColumn()
                ->editColumn('valid_from', function ($row) {
                    return Carbon::parse($row->valid_from)->format('d M Y');
                })
                ->editColumn('valid_to', function ($row) {
                    return Carbon::parse($row->valid_to)->format('d M Y');
                })
                ->editColumn('discount', function ($row) {
                    return $row->discount . " " . ($row->discount_type == 'percent' ? '%' : config('app.default_currency'));
                })
                ->addColumn('status', function ($row) {
                    return $row->status == 1 ? '<span class="btn btn-sm btn-success">Active</span>' : '<span class="btn btn-sm btn-danger">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                    <a href="' . route('admin.editCoupons', ['id' => $row->id]) . '" class="btn btn-sm btn-primary">Edit</a>
                    <button class="btn btn-sm btn-danger" id="deleteCouponBtn' . $row->id . '" onclick="return deleteCoupons(' . $row->id . ')">Delete</button>
                ';
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch products.']);
        }
    }

    // Store coupon
    public function storeCoupon($request)
    {
        try {
            $data = [
                'coupon_code' => $request->coupon_code,
                'description' => $request->description,
                'discount' => $request->discount,
                'discount_type' => $request->discount_type ?? 'percent',
                'usage_count' => $request->usage_limit ?? 0, // default 0 if not sent
                'min_purchase_amount' => $request->min_purchase_amount ?? null,
                'valid_from' => $request->valid_from,
                'valid_to' => $request->valid_to,
                'status' => $request->status,
            ];

            $createdCoupon = $this->CouponRepository->create($data);

            if ($createdCoupon) {
                return response()->json([
                    'status' => 1,
                    'message' => 'The coupon has been created successfully.',
                    'data' => $createdCoupon
                ]);
            }

            return response()->json([
                'status' => 0,
                'message' => 'Failed to create the coupon.'
            ]);
        } catch (\Exception $e) {
            \Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Something went wrong.'
            ]);
        }
    }

    // update coupon
    public function updateCoupon($request)
    {
        try {
            $data = [
                'description' => $request->description,
                'discount' => $request->discount,
                'discount_type' => $request->discount_type ?? 'percent',
                'usage_count' => $request->usage_limit ?? 0, // default 0 if not sent
                'min_purchase_amount' => $request->min_purchase_amount ?? null,
                'valid_from' => $request->valid_from,
                'valid_to' => $request->valid_to,
                'status' => $request->status,
            ];

            $createdCoupon = $this->CouponRepository->update(['id' => $request->id], $data);

            if ($createdCoupon) {
                return response()->json([
                    'status' => 1,
                    'message' => 'The coupon has been updated successfully.',
                    'data' => $createdCoupon
                ]);
            }

            return response()->json([
                'status' => 0,
                'message' => 'Failed to update the coupon.'
            ]);
        } catch (\Exception $e) {
            \Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Something went wrong.'
            ]);
        }
    }

    // inactive past coupons
    public function inactivePastCoupons($request)
    {
        try {
            $data['status'] = 0;
            $currentDate = Carbon::now()->format('Y-m-d');
            $createdCoupon = Coupon::where('valid_to', '<', $currentDate)->update($data);

            if ($createdCoupon) {
                return response()->json([
                    'status' => 1,
                    'message' => 'The coupon has been inactivated successfully.',
                    'data' => $createdCoupon
                ]);
            }

            return response()->json([
                'status' => 0,
                'message' => 'Failed to deactivate the coupon.'
            ]);
        } catch (\Exception $e) {
            \Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Something went wrong.'
            ]);
        }
    }
}
