<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('student-dashboard/index');
    }

    public function show(Student $student): Response
    {
        $orders = $student->orders()
            ->with('items.product')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(fn ($order) => [
                'id' => $order->id,
                'uuid' => $order->uuid,
                'total' => $order->total,
                'vat' => $order->vat,
                'discount' => $order->discount,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'is_payed' => $order->is_payed,
                'wallet_type' => $order->wallet_type,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->item,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'total' => $item->total,
                    'image_url' => $item->product?->image_url,
                ]),
                'created_at' => $order->created_at->format('M d, Y h:i A'),
            ]);

        return Inertia::render('student-dashboard/show', [
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'full_name' => $student->full_name,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                'email' => $student->email,
                'phone' => $student->phone,
                'wallet_type' => $student->wallet_type,
                'wallet_balance' => $student->assigned_wallet_balance,
                'has_wallet' => $student->hasAssignedWallet(),
            ],
            'orders' => $orders,
        ]);
    }

    public function searchStudent(Request $request): JsonResponse
    {
        $studentId = $request->query('student_id');

        if (!$studentId) {
            return response()->json(['error' => 'Student ID is required'], 400);
        }

        $student = Student::where('student_id', $studentId)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        if (!$student->is_active) {
            return response()->json(['error' => 'Student account is inactive'], 403);
        }

        return response()->json([
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'full_name' => $student->full_name,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
            ],
        ]);
    }

    public function getOrders(Student $student, Request $request): JsonResponse
    {
        $orders = $student->orders()
            ->with('items.product')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(fn ($order) => [
                'id' => $order->id,
                'uuid' => $order->uuid,
                'total' => $order->total,
                'vat' => $order->vat,
                'discount' => $order->discount,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'is_payed' => $order->is_payed,
                'wallet_type' => $order->wallet_type,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->item,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'total' => $item->total,
                    'image_url' => $item->product?->image_url,
                ]),
                'created_at' => $order->created_at->format('M d, Y h:i A'),
            ]);

        return response()->json([
            'orders' => $orders,
        ]);
    }
}
