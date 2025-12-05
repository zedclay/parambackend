<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanningItem;
use App\Models\Planning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminPlanningItemsController extends Controller
{
    public function index(Request $request, $planningId)
    {
        $planning = Planning::findOrFail($planningId);
        $items = $planning->items()
            ->with(['module', 'group'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        
        return response()->json(['success' => true, 'data' => $items]);
    }

    public function store(Request $request, $planningId)
    {
        $planning = Planning::findOrFail($planningId);
        
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:modules,id',
            'group_id' => 'nullable|exists:groups,id',
            'day_of_week' => 'required|integer|min:1|max:7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:100',
            'teacher_name' => 'nullable|string|max:255',
            'teacher_email' => 'nullable|email|max:255',
            'course_type' => 'required|in:cours,td,tp,examen',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $item = PlanningItem::create([
            'planning_id' => $planning->id,
            'module_id' => $request->module_id,
            'group_id' => $request->group_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'room' => $request->room,
            'teacher_name' => $request->teacher_name,
            'teacher_email' => $request->teacher_email,
            'course_type' => $request->course_type,
            'order' => $request->order ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'data' => $item->load(['module', 'group']),
            'message' => 'Planning item created successfully.'
        ], 201);
    }

    public function show($id)
    {
        $item = PlanningItem::with(['planning.semester', 'module', 'group'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = PlanningItem::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'module_id' => 'sometimes|exists:modules,id',
            'group_id' => 'nullable|exists:groups,id',
            'day_of_week' => 'sometimes|integer|min:1|max:7',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:100',
            'teacher_name' => 'nullable|string|max:255',
            'teacher_email' => 'nullable|email|max:255',
            'course_type' => 'sometimes|in:cours,td,tp,examen',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $item->update($request->only([
            'module_id', 'group_id', 'day_of_week', 'start_time', 'end_time',
            'room', 'teacher_name', 'teacher_email', 'course_type', 'order'
        ]));

        return response()->json([
            'success' => true,
            'data' => $item->fresh()->load(['module', 'group']),
            'message' => 'Planning item updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        $item = PlanningItem::findOrFail($id);
        $item->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Planning item deleted successfully.'
        ]);
    }
}

