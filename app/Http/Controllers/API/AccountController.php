<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts = Account::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => AccountResource::collection($accounts)]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:checking,savings,credit,cash',
            'balance' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|size:7',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $data = $request->only(['name', 'type', 'balance', 'color']);
        $data['user_id'] = $request->user()->id;
        $data['balance'] = $data['balance'] ?? 0;
        $data['color'] = $data['color'] ?? '#10B981';

        $account = Account::create($data);

        return response()->json(['success' => true, 'message' => 'Account created successfully', 'data' => new AccountResource($account)], 201);
    }

    public function show(Request $request, $id)
    {
        $account = Account::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Account not found'], 404);
        }

        return response()->json(['success' => true, 'data' => new AccountResource($account)]);
    }

    public function update(Request $request, $id)
    {
        $account = Account::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Account not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:checking,savings,credit,cash',
            'balance' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|size:7',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $account->update($request->only(['name', 'type', 'balance', 'color', 'is_active']));

        return response()->json(['success' => true, 'message' => 'Account updated successfully', 'data' => new AccountResource($account)]);
    }

    public function destroy(Request $request, $id)
    {
        $account = Account::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Account not found'], 404);
        }

        if ($account->transactions()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete account with existing transactions'], 400);
        }

        $account->delete();

        return response()->json(['success' => true, 'message' => 'Account deleted successfully']);
    }
}
