<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Transaction::where('user_id', $user->id)
            ->with(['account', 'category']);

        // Apply filters
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->has('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        if ($request->has('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'transaction_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => TransactionResource::collection($transactions)
        ]);
    }

    /**
     * Store a newly created transaction
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense,transfer',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'is_recurring' => 'boolean',
            'recurring_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify account belongs to user
        $account = Account::where('id', $request->account_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found'
            ], 404);
        }

        // Verify category belongs to user
        $category = Category::where('id', $request->category_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $data = $request->only([
            'account_id', 'category_id', 'amount', 'type', 'description',
            'transaction_date', 'location', 'is_recurring', 'recurring_data'
        ]);

        $data['user_id'] = $request->user()->id;

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('receipts', $filename, 'public');
            $data['receipt_path'] = $path;
        }

        $transaction = Transaction::create($data);

        // Update account balance
        $this->updateAccountBalance($account, $transaction);

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully',
            'data' => new TransactionResource($transaction->load(['account', 'category']))
        ], 201);
    }

    /**
     * Display the specified transaction
     */
    public function show(Request $request, $id)
    {
        $transaction = Transaction::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with(['account', 'category'])
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new TransactionResource($transaction)
        ]);
    }

    /**
     * Update the specified transaction
     */
    public function update(Request $request, $id)
    {
        $transaction = Transaction::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'account_id' => 'sometimes|exists:accounts,id',
            'category_id' => 'sometimes|exists:categories,id',
            'amount' => 'sometimes|numeric|min:0.01',
            'type' => 'sometimes|in:income,expense,transfer',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'sometimes|date',
            'location' => 'nullable|string|max:255',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'is_recurring' => 'boolean',
            'recurring_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldAmount = $transaction->amount;
        $oldType = $transaction->type;
        $oldAccountId = $transaction->account_id;

        $data = $request->only([
            'account_id', 'category_id', 'amount', 'type', 'description',
            'transaction_date', 'location', 'is_recurring', 'recurring_data'
        ]);

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            // Delete old receipt
            if ($transaction->receipt_path) {
                Storage::disk('public')->delete($transaction->receipt_path);
            }

            $file = $request->file('receipt');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('receipts', $filename, 'public');
            $data['receipt_path'] = $path;
        }

        $transaction->update($data);

        // Update account balances
        $this->updateAccountBalanceAfterEdit($oldAccountId, $oldAmount, $oldType, $transaction);

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully',
            'data' => new TransactionResource($transaction->load(['account', 'category']))
        ]);
    }

    /**
     * Remove the specified transaction
     */
    public function destroy(Request $request, $id)
    {
        $transaction = Transaction::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        // Delete receipt file
        if ($transaction->receipt_path) {
            Storage::disk('public')->delete($transaction->receipt_path);
        }

        // Update account balance
        $this->updateAccountBalanceAfterDelete($transaction);

        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully'
        ]);
    }

    /**
     * Export transactions
     */
    public function export(Request $request, $format)
    {
        $user = $request->user();
        $transactions = Transaction::where('user_id', $user->id)
            ->with(['account', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->get();

        if ($format === 'csv') {
            return $this->exportToCsv($transactions);
        } elseif ($format === 'pdf') {
            return $this->exportToPdf($transactions);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unsupported export format'
        ], 400);
    }

    private function updateAccountBalance($account, $transaction)
    {
        if ($transaction->type === 'income') {
            $account->increment('balance', $transaction->amount);
        } elseif ($transaction->type === 'expense') {
            $account->decrement('balance', $transaction->amount);
        }
        // Transfer logic would be handled separately
    }

    private function updateAccountBalanceAfterEdit($oldAccountId, $oldAmount, $oldType, $transaction)
    {
        // Revert old transaction effect
        $oldAccount = Account::find($oldAccountId);
        if ($oldAccount) {
            if ($oldType === 'income') {
                $oldAccount->decrement('balance', $oldAmount);
            } elseif ($oldType === 'expense') {
                $oldAccount->increment('balance', $oldAmount);
            }
        }

        // Apply new transaction effect
        $this->updateAccountBalance($transaction->account, $transaction);
    }

    private function updateAccountBalanceAfterDelete($transaction)
    {
        $account = $transaction->account;
        if ($transaction->type === 'income') {
            $account->decrement('balance', $transaction->amount);
        } elseif ($transaction->type === 'expense') {
            $account->increment('balance', $transaction->amount);
        }
    }

    private function exportToCsv($transactions)
    {
        $filename = 'transactions_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Date', 'Type', 'Description', 'Amount', 'Account', 'Category', 'Location'
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_date->format('Y-m-d'),
                    ucfirst($transaction->type),
                    $transaction->description,
                    number_format($transaction->amount, 2),
                    $transaction->account->name,
                    $transaction->category->name,
                    $transaction->location
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToPdf($transactions)
    {
        // This would require a PDF library like dompdf or tcpdf
        // For now, return a placeholder response
        return response()->json([
            'success' => false,
            'message' => 'PDF export not implemented yet'
        ], 501);
    }
}
