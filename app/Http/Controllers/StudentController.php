<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Student;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;

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
                'wallet_type' => $student->wallet_type,
                'wallet_balance' => $student->assigned_wallet_balance,
                'has_wallet' => $student->hasAssignedWallet(),
                'qr_code_url' => $student->qr_code_url,
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
            'wallet_type' => 'nullable|in:subscribe,non-subscribe',
        ]);

        $student = Student::create($validated);

        // Create the assigned wallet if wallet_type is set
        if ($student->wallet_type) {
            $student->createAssignedWallet();
        }

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
            'wallet_type' => 'nullable|in:subscribe,non-subscribe',
        ]);

        $student->update($validated);

        // Create the assigned wallet if wallet_type is set and wallet doesn't exist
        if ($student->wallet_type && !$student->hasAssignedWallet()) {
            $student->createAssignedWallet();
        }

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

        if (!$student->wallet_type) {
            return back()->with('flash', [
                'message' => 'Student does not have a wallet type assigned.',
                'type' => 'error',
            ]);
        }

        try {
            DB::beginTransaction();

            // Get or create the assigned wallet
            $wallet = $student->createAssignedWallet();

            $wallet->depositFloat(
                $validated['amount'],
                ['description' => $validated['description'] ?? 'Wallet deposit']
            );

            DB::commit();

            $walletName = $student->wallet_type === 'subscribe' ? 'Subscribe' : 'Non-Subscribe';

            return back()->with('flash', [
                'message' => "Deposit successful to {$walletName} Wallet! New balance: ₱".number_format($wallet->balanceFloatNum, 2),
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('flash', [
                'message' => 'Failed to process deposit: '.$e->getMessage(),
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

        if (!$student->wallet_type) {
            return back()->with('flash', [
                'message' => 'Student does not have a wallet type assigned.',
                'type' => 'error',
            ]);
        }

        $wallet = $student->getAssignedWallet();

        if (!$wallet) {
            return back()->with('flash', [
                'message' => 'Wallet does not exist.',
                'type' => 'error',
            ]);
        }

        if ($wallet->balanceFloatNum < $validated['amount']) {
            return back()->with('flash', [
                'message' => 'Insufficient balance.',
                'type' => 'error',
            ]);
        }

        try {
            DB::beginTransaction();

            $wallet->withdrawFloat(
                $validated['amount'],
                ['description' => $validated['description'] ?? 'Wallet withdrawal']
            );

            DB::commit();

            $walletName = $student->wallet_type === 'subscribe' ? 'Subscribe' : 'Non-Subscribe';

            return back()->with('flash', [
                'message' => "Withdrawal successful from {$walletName} Wallet! New balance: ₱".number_format($wallet->balanceFloatNum, 2),
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
        if (!$student->wallet_type) {
            return response()->json([
                'transactions' => [],
                'balance' => 0,
                'wallet_exists' => false,
            ]);
        }

        $wallet = $student->getAssignedWallet();

        if (!$wallet) {
            return response()->json([
                'transactions' => [],
                'balance' => 0,
                'wallet_exists' => false,
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
            'balance' => $wallet->balanceFloatNum,
            'wallet_exists' => true,
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
                'wallet_type' => $student->wallet_type,
                'wallet_balance' => $student->assigned_wallet_balance,
                'has_wallet' => $student->hasAssignedWallet(),
                'qr_code_url' => $student->qr_code_url,
            ]);

        return response()->json(['students' => $students]);
    }

    public function getBalance(Student $student): JsonResponse
    {
        return response()->json([
            'wallet_type' => $student->wallet_type,
            'wallet_balance' => $student->assigned_wallet_balance,
            'has_wallet' => $student->hasAssignedWallet(),
            'student_id' => $student->student_id,
            'full_name' => $student->full_name,
        ]);
    }

    public function getByStudentId(Request $request): JsonResponse
    {
        $studentId = $request->query('student_id');

        if (!$studentId) {
            return response()->json(['error' => 'Student ID is required'], 400);
        }

        $student = Student::where('student_id', $studentId)->active()->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found or inactive'], 404);
        }

        return response()->json([
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'full_name' => $student->full_name,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                'wallet_type' => $student->wallet_type,
                'wallet_balance' => $student->assigned_wallet_balance,
                'has_wallet' => $student->hasAssignedWallet(),
                'qr_code_url' => $student->qr_code_url,
            ],
        ]);
    }

    public function getByQrToken(string $token): JsonResponse
    {
        $student = Student::where('qr_token', $token)->active()->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found or inactive'], 404);
        }

        return response()->json([
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'full_name' => $student->full_name,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                'wallet_type' => $student->wallet_type,
                'wallet_balance' => $student->assigned_wallet_balance,
                'has_wallet' => $student->hasAssignedWallet(),
                'qr_code_url' => $student->qr_code_url,
            ],
        ]);
    }

    public function qrCode(Student $student)
    {
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($student->getQrPayload());

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }
}
