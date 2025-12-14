<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Student;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use App\Events\StudentCreated;
use App\Services\CacheService;
use App\Events\WalletDeposited;
use App\Events\WalletWithdrawn;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;

class StudentController extends Controller
{
    public function __construct(
        protected CacheService $cacheService,
    ) {}

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

        // Dispatch student created event
        StudentCreated::dispatch($student, $student->wallet_type);

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
            $newBalance = $wallet->balanceFloatNum;

            // Dispatch wallet deposited event
            WalletDeposited::dispatch(
                $student,
                (float) $validated['amount'],
                $student->wallet_type,
                $newBalance,
                $validated['description'] ?? 'Wallet deposit'
            );

            return back()->with('flash', [
                'message' => "Deposit successful to {$walletName} Wallet! New balance: ₱".number_format($newBalance, 2),
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
            $newBalance = $wallet->balanceFloatNum;

            // Dispatch wallet withdrawn event
            WalletWithdrawn::dispatch(
                $student,
                (float) $validated['amount'],
                $student->wallet_type,
                $newBalance,
                $validated['description'] ?? 'Wallet withdrawal'
            );

            return back()->with('flash', [
                'message' => "Withdrawal successful from {$walletName} Wallet! New balance: ₱".number_format($newBalance, 2),
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

        // Cache transactions (short TTL since balance changes frequently)
        $transactions = $this->cacheService->remember(
            $this->cacheService->getStudentTransactionsKey($student->id),
            CacheService::TTL_SHORT,
            fn () => $wallet->transactions()
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'type' => $t->type,
                    'amount' => (float) $t->amountFloat,
                    'meta' => $t->meta,
                    'created_at' => $t->created_at->format('M d, Y h:i A'),
                ])
                ->toArray()
        );

        return response()->json([
            'transactions' => $transactions,
            'balance' => $wallet->balanceFloatNum,
            'wallet_exists' => true,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->q ?? '';
        $storeId = session('current_store_id', 0);

        // Cache search results (short TTL for fresh data)
        $students = $this->cacheService->remember(
            $this->cacheService->getStudentSearchKey($storeId, $query),
            CacheService::TTL_SHORT,
            fn () => Student::query()
                ->active()
                ->search($query)
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
                ])
                ->toArray()
        );

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
        // Use student_id as fallback if qr_token is empty
        $payload = $student->qr_token
            ? 'student:'.$student->qr_token
            : 'student-id:'.$student->student_id;

        $size = 300;
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($payload);

        // Make SVG responsive by adding viewBox and removing fixed dimensions
        $svg = preg_replace(
            '/<svg([^>]*)width="[^"]*"([^>]*)height="[^"]*"/',
            '<svg$1$2viewBox="0 0 '.$size.' '.$size.'" preserveAspectRatio="xMidYMid meet"',
            $svg
        );

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=3600')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    /**
     * Export multiple students to PDF (bulk export)
     */
    public function exportBulkPdf(Request $request)
    {
        if ($request->has('ids')) {
            // Multiple students export
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $students = Student::whereIn('id', $ids)->orderBy('last_name')->get();
        } else {
            // Export all (with current filters)
            $students = Student::query()
                ->search($request->search)
                ->when($request->status === 'active', fn ($q) => $q->active())
                ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
                ->orderBy('last_name')
                ->get();
        }

        if ($students->isEmpty()) {
            return back()->with('error', 'No students to export');
        }

        return $this->generateStudentsPdf($students, $request->input('view', 'table'));
    }

    /**
     * Export single student to PDF
     */
    public function exportSinglePdf(Student $student, Request $request)
    {
        return $this->generateStudentsPdf(collect([$student]), $request->input('view', 'card'));
    }

    /**
     * Generate PDF for students collection
     */
    private function generateStudentsPdf($students, string $viewType = 'table')
    {
        $studentsData = $students->map(fn ($s) => [
            'id' => $s->id,
            'student_id' => $s->student_id,
            'first_name' => $s->first_name,
            'last_name' => $s->last_name,
            'full_name' => $s->full_name,
            'email' => $s->email,
            'phone' => $s->phone,
            'grade_level' => $s->grade_level,
            'section' => $s->section,
            'guardian_name' => $s->guardian_name,
            'guardian_phone' => $s->guardian_phone,
            'address' => $s->address,
            'is_active' => $s->is_active,
            'wallet_type' => $s->wallet_type,
            'wallet_balance' => $s->hasAssignedWallet() ? $s->assigned_wallet_balance : 0,
        ])->toArray();

        // Use table for multiple students
        $viewType = count($studentsData) > 5 ? 'table' : $viewType;

        $pdf = Pdf::loadView('exports.students-list-pdf', [
            'students' => $studentsData,
            'view_type' => $viewType,
        ]);

        $pdf->setPaper('a4', count($studentsData) > 5 ? 'landscape' : 'portrait');

        // Generate filename
        if (count($studentsData) === 1) {
            $filename = 'student-'.$studentsData[0]['student_id'].'.pdf';
        } else {
            $filename = 'students-export-'.now()->format('Y-m-d').'.pdf';
        }

        return $pdf->download($filename);
    }

    /**
     * Export multiple students QR codes to PDF (bulk export)
     */
    public function exportBulkQrPdf(Request $request)
    {
        if ($request->has('ids')) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $students = Student::whereIn('id', $ids)->orderBy('last_name')->get();
        } else {
            $students = Student::query()
                ->search($request->search)
                ->when($request->status === 'active', fn ($q) => $q->active())
                ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
                ->orderBy('last_name')
                ->get();
        }

        if ($students->isEmpty()) {
            return back()->with('error', 'No students to export');
        }

        return $this->generateQrPdf($students, $request->input('columns', 4));
    }

    /**
     * Export single student QR code to PDF
     */
    public function exportSingleQrPdf(Student $student, Request $request)
    {
        return $this->generateQrPdf(collect([$student]), 1);
    }

    /**
     * Generate QR codes PDF for students collection
     */
    private function generateQrPdf($students, int $columns = 4)
    {
        // Limit columns to reasonable values
        $columns = max(1, min(4, $columns));

        // QR size based on columns (increased sizes)
        $qrSizes = [1 => 300, 2 => 200, 3 => 160, 4 => 140];
        $qrSize = $qrSizes[$columns];

        // Rows per page based on columns
        $rowsPerPage = $columns <= 2 ? 3 : 4;

        $studentsData = $students->map(function ($s) use ($qrSize) {
            return [
                'id' => $s->id,
                'student_id' => $s->student_id,
                'full_name' => $s->full_name,
                'grade_level' => $s->grade_level,
                'section' => $s->section,
                'qr_svg' => $this->generateQrSvg($s, $qrSize, true),
            ];
        })->toArray();

        $pdf = Pdf::loadView('exports.students-qr-pdf', [
            'students' => $studentsData,
            'columns' => $columns,
            'qr_size' => $qrSize,
            'rows_per_page' => $rowsPerPage,
        ]);

        $pdf->setPaper('a4', 'portrait');

        // Generate filename
        if (count($studentsData) === 1) {
            $filename = 'qr-'.$studentsData[0]['student_id'].'.pdf';
        } else {
            $filename = 'student-qr-codes-'.now()->format('Y-m-d').'.pdf';
        }

        return $pdf->download($filename);
    }

    /**
     * Generate QR code for a student
     *
     * @param  bool  $forPdf  If true, returns base64 PNG for DomPDF compatibility
     */
    private function generateQrSvg(Student $student, int $size = 100, bool $forPdf = false): string
    {
        $payload = $student->qr_token
            ? 'student:'.$student->qr_token
            : 'student-id:'.$student->student_id;

        if ($forPdf) {
            // For PDF: generate PNG image as base64 (better DomPDF compatibility)
            $renderer = new ImageRenderer(
                new RendererStyle($size),
                new ImagickImageBackEnd
            );

            $writer = new Writer($renderer);
            $png = $writer->writeString($payload);

            return 'data:image/png;base64,'.base64_encode($png);
        }

        // For browser: generate responsive SVG
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($payload);

        // Make SVG responsive by removing fixed dimensions
        $svg = preg_replace(
            '/<svg([^>]*)width="[^"]*"([^>]*)height="[^"]*"/',
            '<svg$1$2viewBox="0 0 '.$size.' '.$size.'" preserveAspectRatio="xMidYMid meet"',
            $svg
        );

        return $svg;
    }
}
