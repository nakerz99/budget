<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillResource;
use App\Models\Bill;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BillController extends Controller
{
    /**
     * Display a listing of bills
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Bill::where('user_id', $user->id)->with('category');

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'upcoming') {
                $query->where('due_date', '>=', now())->where('is_paid', false);
            } elseif ($request->status === 'overdue') {
                $query->where('due_date', '<', now())->where('is_paid', false);
            } elseif ($request->status === 'paid') {
                $query->where('is_paid', true);
            }
        }

        $bills = $query->orderBy('due_date')->get();

        return response()->json([
            'success' => true,
            'data' => BillResource::collection($bills)
        ]);
    }

    /**
     * Store a newly created bill
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'frequency' => 'nullable|in:weekly,monthly,yearly',
            'is_recurring' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $bill = Bill::create([
            'user_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'due_date' => $request->due_date,
            'frequency' => $request->frequency,
            'is_recurring' => $request->is_recurring ?? false,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'data' => new BillResource($bill->load('category'))
        ], 201);
    }

    /**
     * Display the specified bill
     */
    public function show(Bill $bill)
    {
        if ($bill->user_id !== request()->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new BillResource($bill->load('category'))
        ]);
    }

    /**
     * Update the specified bill
     */
    public function update(Request $request, Bill $bill)
    {
        if ($bill->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'amount' => 'numeric|min:0',
            'due_date' => 'date',
            'frequency' => 'nullable|in:weekly,monthly,yearly',
            'is_paid' => 'boolean',
            'is_recurring' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $bill->update($request->only(['name', 'amount', 'due_date', 'frequency', 'is_paid', 'is_recurring', 'notes']));

        return response()->json([
            'success' => true,
            'data' => new BillResource($bill->load('category'))
        ]);
    }

    /**
     * Remove the specified bill
     */
    public function destroy(Bill $bill)
    {
        if ($bill->user_id !== request()->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $bill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bill deleted successfully'
        ]);
    }

    /**
     * Mark bill as paid
     */
    public function markPaid(Request $request, Bill $bill)
    {
        if ($bill->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $bill->update(['is_paid' => true]);

        // If it's a recurring bill, create the next occurrence
        if ($bill->is_recurring && $bill->frequency) {
            $nextDueDate = $this->calculateNextDueDate($bill->due_date, $bill->frequency);
            
            Bill::create([
                'user_id' => $bill->user_id,
                'category_id' => $bill->category_id,
                'name' => $bill->name,
                'amount' => $bill->amount,
                'due_date' => $nextDueDate,
                'frequency' => $bill->frequency,
                'is_recurring' => true,
                'notes' => $bill->notes
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $bill->fresh()->load('category')
        ]);
    }

    /**
     * Get upcoming bills
     */
    public function upcoming(Request $request)
    {
        $user = $request->user();
        $days = $request->get('days', 30);
        
        $bills = Bill::where('user_id', $user->id)
            ->where('is_paid', false)
            ->whereBetween('due_date', [now(), now()->addDays($days)])
            ->with('category')
            ->orderBy('due_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => BillResource::collection($bills)
        ]);
    }

    /**
     * Calculate next due date for recurring bills
     */
    private function calculateNextDueDate($currentDueDate, $frequency)
    {
        $date = Carbon::parse($currentDueDate);
        
        switch ($frequency) {
            case 'weekly':
                return $date->addWeek();
            case 'monthly':
                return $date->addMonth();
            case 'yearly':
                return $date->addYear();
            default:
                return $date;
        }
    }
}
