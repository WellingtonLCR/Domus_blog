<?php

namespace App\Http\Controllers;

use App\Services\AdminService;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class AdminController extends Controller
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('create-admin');
        $perPage = $request->get('per_page', 15);
        $admins = $this->adminService->getAll($perPage);

        return response()->json([
            'message' => 'Admins recuperados com sucesso',
            'data' => AdminResource::collection($admins)
        ], 200);
    }

    public function store(StoreAdminRequest $request): JsonResponse
    {
        Gate::authorize('create-admin');
        $dto = \App\DTOs\AdminDTO::fromArray($request->validated());
        $admin = $this->adminService->create($dto);

        return response()->json([
            'message' => 'Admin criado com sucesso',
            'data' => new AdminResource($admin)
        ], 201);
    }

    public function show(Admin $admin): JsonResponse
    {
        Gate::authorize('create-admin');
        return response()->json([
            'message' => 'Admin recuperado com sucesso',
            'data' => new AdminResource($admin)
        ], 200);
    }

    public function update(UpdateAdminRequest $request, Admin $admin): JsonResponse
    {
        Gate::authorize('create-admin');
        $dto = \App\DTOs\AdminDTO::fromArray($request->validated());
        $admin = $this->adminService->update($admin, $dto);

        return response()->json([
            'message' => 'Admin atualizado com sucesso',
            'data' => new AdminResource($admin)
        ], 200);
    }

    public function destroy(Admin $admin): JsonResponse
    {
        Gate::authorize('create-admin');
        $this->adminService->delete($admin);

        return response()->json([
            'message' => 'Admin excluído com sucesso'
        ], 200);
    }
}
