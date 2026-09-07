<?php

namespace App\Http\Controllers\Admin;

use App\Actions\GenerateEmployeeCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    public function index()
    {
        return EmployeeResource::collection(
            Employee::query()->latest('id')->paginate(50)
        )->additional(['message' => 'ok']);
    }

    public function store(StoreEmployeeRequest $request, GenerateEmployeeCode $generateEmployeeCode): JsonResponse
    {
        $data = $request->validated();
        $data['code'] = $data['code'] ?? $generateEmployeeCode->handle();
        $data['is_active'] = $data['is_active'] ?? true;

        $employee = Employee::query()->create($data);

        return EmployeeResource::make($employee)
            ->additional(['message' => 'Created.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Employee $employee): EmployeeResource
    {
        return EmployeeResource::make($employee)
            ->additional(['message' => 'ok']);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): EmployeeResource
    {
        $employee->fill($request->validated())->save();

        return EmployeeResource::make($employee)
            ->additional(['message' => 'Updated.']);
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return response()->json([
            'data' => null,
            'message' => 'Deleted.',
        ]);
    }
}
