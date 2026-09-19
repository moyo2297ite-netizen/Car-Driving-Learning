<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->whereIn('role', [UserRole::Supervisor, UserRole::Instructor])
            ->latest()
            ->get();

        return UserResource::collection($users);
    }

    public function store(StoreStaffRequest $request)
    {
        $user = User::query()->create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'teaches_manual' => $request->boolean('teaches_manual'),
            'teaches_automatic' => $request->boolean('teaches_automatic'),
        ]);

        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        if (! in_array($user->role, [UserRole::Supervisor, UserRole::Instructor], true)) {
            return response()->json(['message' => 'لا يمكن حذف هذا النوع من المستخدمين من هنا.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'تم الحذف.']);
    }
}
