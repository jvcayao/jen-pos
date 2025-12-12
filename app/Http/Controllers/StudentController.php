<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;

class StudentController extends Controller
{
    public function index(Request $request): Response
    {
        $students = Student::query()
            ->search($request->search)
            ->when($request->status === 'active', fn ($q) => $q->active())
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->through(fn ($student) => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'full_name' => $student->full_name,
                'email' => $student->email,
                'phone' => $student->phone,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                'guardian_name' => $student->guardian_name,
                'guardian_phone' => $student->guardian_phone,
                'address' => $student->address,
                'is_active' => $student->is_active,
                'wallet_balance' => $student->wallet_balance,
                'created_at' => $student->created_at->format('M d, Y'),
            ]);

        return Inertia::render('students/index', [
            'students' => $students,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|string|unique:students,student_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'grade_level' => 'nullable|string|max:50',
            'section' => 'nullable|string|max:50',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $student = Student::create($validated);

        // Access wallet to auto-create it (bavix/laravel-wallet creates on first access)
        $student->wallet;

        return back()->with('flash', [
            'message' => 'Student created successfully!',
            'type' => 'success',
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|string|unique:students,student_id,'.$student->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'grade_level' => 'nullable|string|max:50',
            'section' => 'nullable|string|max:50',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $student->update($validated);

        return back()->with('flash', [
            'message' => 'Student updated successfully!',
            'type' => 'success',
        ]);
    }

    public function destroy(Student $student): RedirectResponse
    {
        // Check if student has any orders
        if ($student->orders()->exists()) {
            return back()->with('flash', [
                'message' => 'Cannot delete student with existing orders.',
                'type' => 'error',
            ]);
        }

        $student->delete();

        return back()->with('flash', [
            'message' => 'Student deleted successfully!',
            'type' => 'success',
        ]);
    }

    public function deposit(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $student->depositFloat(
                $validated['amount'],
                ['description' => $validated['description'] ?? 'Wallet deposit']
            );

            DB::commit();

            // Refresh to get updated balance
            $student->refresh();

            return back()->with('flash', [
                'message' => 'Deposit successful! New balance: ₱'.number_format($student->balanceFloatNum, 2),
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('flash', [
                'message' => 'Failed to process deposit.',
                'type' => 'error',
            ]);
        }
    }

    public function withdraw(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        if ($student->balanceFloatNum < $validated['amount']) {
            return back()->with('flash', [
                'message' => 'Insufficient balance.',
                'type' => 'error',
            ]);
        }

        try {
            DB::beginTransaction();

            $student->withdrawFloat(
                $validated['amount'],
                ['description' => $validated['description'] ?? 'Wallet withdrawal']
            );

            DB::commit();

            // Refresh to get updated balance
            $student->refresh();

            return back()->with('flash', [
                'message' => 'Withdrawal successful! New balance: ₱'.number_format($student->balanceFloatNum, 2),
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('flash', [
                'message' => 'Failed to process withdrawal: '.$e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    public function transactions(Student $student): JsonResponse
    {
        $wallet = $student->wallet;
        if (!$wallet) {
            return response()->json([
                'transactions' => [],
                'balance' => 0,
            ]);
        }

        $transactions = $wallet->transactions()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'type' => $t->type,
                'amount' => (float) $t->amountFloat,
                'meta' => $t->meta,
                'created_at' => $t->created_at->format('M d, Y h:i A'),
            ]);

        return response()->json([
            'transactions' => $transactions,
            'balance' => $student->balanceFloatNum,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $students = Student::query()
            ->active()
            ->search($request->q)
            ->limit(10)
            ->get()
            ->map(fn ($student) => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'full_name' => $student->full_name,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                'wallet_balance' => $student->wallet_balance,
            ]);

        return response()->json(['students' => $students]);
    }

    public function getBalance(Student $student): JsonResponse
    {
        return response()->json([
            'balance' => $student->balanceFloatNum,
            'student_id' => $student->student_id,
            'full_name' => $student->full_name,
        ]);
    }
}
