import AlertDialog from '@/components/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import {
    ArrowDownCircle,
    ArrowUpCircle,
    ChevronLeft,
    ChevronRight,
    History,
    Pencil,
    Plus,
    Search,
    Trash2,
    User,
    Wallet,
    X,
} from 'lucide-react';
import { type FormEvent, useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Students', href: '/students' },
];

interface Student {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
    full_name: string;
    email: string | null;
    phone: string | null;
    grade_level: string | null;
    section: string | null;
    guardian_name: string | null;
    guardian_phone: string | null;
    address: string | null;
    is_active: boolean;
    wallet_balance: number;
    created_at: string;
}

interface Transaction {
    id: number;
    type: string;
    amount: number;
    meta: { description?: string } | null;
    created_at: string;
}

interface PaginatedStudents {
    data: Student[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface StudentPageProps {
    students: PaginatedStudents;
    filters: {
        search?: string;
        status?: string;
    };
}

interface StudentFormData {
    student_id: string;
    first_name: string;
    last_name: string;
    email: string;
    phone: string;
    grade_level: string;
    section: string;
    guardian_name: string;
    guardian_phone: string;
    address: string;
    is_active: boolean;
}

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(value);
};

function getDefaultFormData(): StudentFormData {
    return {
        student_id: '',
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        grade_level: '',
        section: '',
        guardian_name: '',
        guardian_phone: '',
        address: '',
        is_active: true,
    };
}

function getFormDataFromStudent(student: Student): StudentFormData {
    return {
        student_id: student.student_id,
        first_name: student.first_name,
        last_name: student.last_name,
        email: student.email ?? '',
        phone: student.phone ?? '',
        grade_level: student.grade_level ?? '',
        section: student.section ?? '',
        guardian_name: student.guardian_name ?? '',
        guardian_phone: student.guardian_phone ?? '',
        address: student.address ?? '',
        is_active: student.is_active,
    };
}

function StudentFormFields({
    form,
    isEdit = false,
}: {
    form: ReturnType<typeof useForm<StudentFormData>>;
    isEdit?: boolean;
}) {
    return (
        <div className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
                <div className="grid gap-2">
                    <Label>Student ID *</Label>
                    <Input
                        value={form.data.student_id}
                        onChange={(e) =>
                            form.setData('student_id', e.target.value)
                        }
                        placeholder="e.g., 2024-0001"
                        required
                    />
                    {form.errors.student_id && (
                        <p className="text-xs text-red-600">
                            {form.errors.student_id}
                        </p>
                    )}
                </div>
                <div className="grid gap-2">
                    <Label>Grade Level</Label>
                    <Input
                        value={form.data.grade_level}
                        onChange={(e) =>
                            form.setData('grade_level', e.target.value)
                        }
                        placeholder="e.g., Grade 10"
                    />
                </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
                <div className="grid gap-2">
                    <Label>First Name *</Label>
                    <Input
                        value={form.data.first_name}
                        onChange={(e) =>
                            form.setData('first_name', e.target.value)
                        }
                        required
                    />
                    {form.errors.first_name && (
                        <p className="text-xs text-red-600">
                            {form.errors.first_name}
                        </p>
                    )}
                </div>
                <div className="grid gap-2">
                    <Label>Last Name *</Label>
                    <Input
                        value={form.data.last_name}
                        onChange={(e) =>
                            form.setData('last_name', e.target.value)
                        }
                        required
                    />
                    {form.errors.last_name && (
                        <p className="text-xs text-red-600">
                            {form.errors.last_name}
                        </p>
                    )}
                </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
                <div className="grid gap-2">
                    <Label>Section</Label>
                    <Input
                        value={form.data.section}
                        onChange={(e) =>
                            form.setData('section', e.target.value)
                        }
                        placeholder="e.g., Section A"
                    />
                </div>
                <div className="grid gap-2">
                    <Label>Email</Label>
                    <Input
                        type="email"
                        value={form.data.email}
                        onChange={(e) => form.setData('email', e.target.value)}
                    />
                </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
                <div className="grid gap-2">
                    <Label>Phone</Label>
                    <Input
                        value={form.data.phone}
                        onChange={(e) => form.setData('phone', e.target.value)}
                    />
                </div>
                <div className="grid gap-2">
                    <Label>Guardian Name</Label>
                    <Input
                        value={form.data.guardian_name}
                        onChange={(e) =>
                            form.setData('guardian_name', e.target.value)
                        }
                    />
                </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
                <div className="grid gap-2">
                    <Label>Guardian Phone</Label>
                    <Input
                        value={form.data.guardian_phone}
                        onChange={(e) =>
                            form.setData('guardian_phone', e.target.value)
                        }
                    />
                </div>
                {isEdit && (
                    <div className="grid gap-2">
                        <Label>Status</Label>
                        <Select
                            value={form.data.is_active ? 'active' : 'inactive'}
                            onValueChange={(value) =>
                                form.setData('is_active', value === 'active')
                            }
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">
                                    Inactive
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                )}
            </div>

            <div className="grid gap-2">
                <Label>Address</Label>
                <textarea
                    className="min-h-20 rounded border border-input bg-background px-3 py-2 text-sm"
                    value={form.data.address}
                    onChange={(e) => form.setData('address', e.target.value)}
                />
            </div>
        </div>
    );
}

function CreateStudentModal({
    open,
    onClose,
}: {
    open: boolean;
    onClose: () => void;
}) {
    const form = useForm<StudentFormData>(getDefaultFormData());
    const [confirmCreateOpen, setConfirmCreateOpen] = useState(false);

    const doCreate = () => {
        form.post('/students', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onClose();
            },
        });
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        setConfirmCreateOpen(true);
    };

    const handleClose = () => {
        form.reset();
        onClose();
    };

    if (!open) return null;

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="max-h-[90vh] max-w-2xl overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Add Student</DialogTitle>
                    <DialogDescription>
                        Create a new student account with wallet
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={submit}>
                    <StudentFormFields form={form} />
                    <DialogFooter className="mt-6">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={handleClose}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            Create Student
                        </Button>
                    </DialogFooter>
                </form>
                <AlertDialog
                    open={confirmCreateOpen}
                    title="Create student?"
                    description="This will create a new student account with an associated wallet."
                    confirmLabel="Create"
                    cancelLabel="Cancel"
                    onCancel={() => setConfirmCreateOpen(false)}
                    onConfirm={() => {
                        setConfirmCreateOpen(false);
                        doCreate();
                    }}
                />
            </DialogContent>
        </Dialog>
    );
}

function EditStudentModal({
    student,
    open,
    onClose,
}: {
    student: Student;
    open: boolean;
    onClose: () => void;
}) {
    const form = useForm<StudentFormData>(getFormDataFromStudent(student));
    const [confirmSaveOpen, setConfirmSaveOpen] = useState(false);

    useEffect(() => {
        if (open) {
            form.setData(getFormDataFromStudent(student));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [student.id, open]);

    const doUpdate = () => {
        form.put(`/students/${student.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                onClose();
            },
        });
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        setConfirmSaveOpen(true);
    };

    if (!open) return null;

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="max-h-[90vh] max-w-2xl overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Edit Student</DialogTitle>
                    <DialogDescription>
                        Update student information
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={submit}>
                    <StudentFormFields form={form} isEdit />
                    <DialogFooter className="mt-6">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            Save Changes
                        </Button>
                    </DialogFooter>
                </form>
                <AlertDialog
                    open={confirmSaveOpen}
                    title="Save changes?"
                    description="Apply these changes to the student?"
                    confirmLabel="Save"
                    cancelLabel="Cancel"
                    onCancel={() => setConfirmSaveOpen(false)}
                    onConfirm={() => {
                        setConfirmSaveOpen(false);
                        doUpdate();
                    }}
                />
            </DialogContent>
        </Dialog>
    );
}

function WalletModal({
    student,
    open,
    onClose,
}: {
    student: Student;
    open: boolean;
    onClose: () => void;
}) {
    const [activeTab, setActiveTab] = useState<
        'deposit' | 'withdraw' | 'history'
    >('deposit');
    const [amount, setAmount] = useState('');
    const [description, setDescription] = useState('');
    const [loading, setLoading] = useState(false);
    const [transactions, setTransactions] = useState<Transaction[]>([]);
    const [balance, setBalance] = useState(student.wallet_balance);

    useEffect(() => {
        if (open) {
            setBalance(student.wallet_balance);
            fetchTransactions();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, student.id]);

    const fetchTransactions = async () => {
        try {
            const response = await fetch(`/students/${student.id}/transactions`);
            const data = await response.json();
            setTransactions(data.transactions);
            setBalance(data.balance);
        } catch (error) {
            console.error('Failed to fetch transactions:', error);
        }
    };

    const handleDeposit = () => {
        if (!amount || parseFloat(amount) <= 0) return;
        setLoading(true);
        router.post(
            `/students/${student.id}/deposit`,
            { amount: parseFloat(amount), description },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setAmount('');
                    setDescription('');
                    fetchTransactions();
                },
                onFinish: () => setLoading(false),
            },
        );
    };

    const handleWithdraw = () => {
        if (!amount || parseFloat(amount) <= 0) return;
        setLoading(true);
        router.post(
            `/students/${student.id}/withdraw`,
            { amount: parseFloat(amount), description },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setAmount('');
                    setDescription('');
                    fetchTransactions();
                },
                onFinish: () => setLoading(false),
            },
        );
    };

    if (!open) return null;

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <Wallet className="h-5 w-5" />
                        {student.full_name}'s Wallet
                    </DialogTitle>
                    <DialogDescription>
                        Student ID: {student.student_id}
                    </DialogDescription>
                </DialogHeader>

                <div className="rounded-lg bg-gradient-to-r from-primary/10 to-primary/5 p-4">
                    <div className="text-sm text-muted-foreground">
                        Current Balance
                    </div>
                    <div className="text-3xl font-bold">
                        {formatCurrency(balance)}
                    </div>
                </div>

                <div className="flex gap-2">
                    <Button
                        variant={activeTab === 'deposit' ? 'default' : 'outline'}
                        size="sm"
                        onClick={() => setActiveTab('deposit')}
                        className="flex-1"
                    >
                        <ArrowDownCircle className="mr-1 h-4 w-4" />
                        Deposit
                    </Button>
                    <Button
                        variant={
                            activeTab === 'withdraw' ? 'default' : 'outline'
                        }
                        size="sm"
                        onClick={() => setActiveTab('withdraw')}
                        className="flex-1"
                    >
                        <ArrowUpCircle className="mr-1 h-4 w-4" />
                        Withdraw
                    </Button>
                    <Button
                        variant={activeTab === 'history' ? 'default' : 'outline'}
                        size="sm"
                        onClick={() => setActiveTab('history')}
                        className="flex-1"
                    >
                        <History className="mr-1 h-4 w-4" />
                        History
                    </Button>
                </div>

                {(activeTab === 'deposit' || activeTab === 'withdraw') && (
                    <div className="space-y-4">
                        <div className="grid gap-2">
                            <Label>Amount</Label>
                            <Input
                                type="number"
                                min="1"
                                step="0.01"
                                value={amount}
                                onChange={(e) => setAmount(e.target.value)}
                                placeholder="Enter amount"
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label>Description (optional)</Label>
                            <Input
                                value={description}
                                onChange={(e) => setDescription(e.target.value)}
                                placeholder="e.g., Weekly allowance"
                            />
                        </div>
                        <Button
                            className="w-full"
                            onClick={
                                activeTab === 'deposit'
                                    ? handleDeposit
                                    : handleWithdraw
                            }
                            disabled={loading || !amount}
                        >
                            {loading
                                ? 'Processing...'
                                : activeTab === 'deposit'
                                  ? 'Deposit'
                                  : 'Withdraw'}
                        </Button>
                    </div>
                )}

                {activeTab === 'history' && (
                    <div className="max-h-64 space-y-2 overflow-y-auto">
                        {transactions.length === 0 ? (
                            <div className="py-8 text-center text-muted-foreground">
                                No transactions yet
                            </div>
                        ) : (
                            transactions.map((t) => (
                                <div
                                    key={t.id}
                                    className="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div>
                                        <div className="flex items-center gap-2">
                                            {t.type === 'deposit' ? (
                                                <ArrowDownCircle className="h-4 w-4 text-green-600" />
                                            ) : (
                                                <ArrowUpCircle className="h-4 w-4 text-red-600" />
                                            )}
                                            <span className="font-medium capitalize">
                                                {t.type}
                                            </span>
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            {t.meta?.description || t.created_at}
                                        </div>
                                    </div>
                                    <div
                                        className={`font-semibold ${t.type === 'deposit' ? 'text-green-600' : 'text-red-600'}`}
                                    >
                                        {t.type === 'deposit' ? '+' : '-'}
                                        {formatCurrency(Math.abs(t.amount))}
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}

function StudentCard({ student }: { student: Student }) {
    const [editing, setEditing] = useState(false);
    const [walletOpen, setWalletOpen] = useState(false);
    const [confirmDeleteOpen, setConfirmDeleteOpen] = useState(false);
    const form = useForm({});

    const doDelete = () => {
        form.delete(`/students/${student.id}`, {
            preserveScroll: true,
        });
    };

    return (
        <>
            <Card className="group">
                <CardHeader className="pb-3">
                    <div className="flex items-start justify-between">
                        <div className="flex items-center gap-3">
                            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                                <User className="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <CardTitle className="text-base">
                                    {student.full_name}
                                </CardTitle>
                                <CardDescription>
                                    {student.student_id}
                                </CardDescription>
                            </div>
                        </div>
                        <Badge
                            variant={
                                student.is_active ? 'default' : 'destructive'
                            }
                        >
                            {student.is_active ? 'Active' : 'Inactive'}
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent className="space-y-3">
                    <div className="grid grid-cols-2 gap-2 text-sm">
                        {student.grade_level && (
                            <div>
                                <span className="text-muted-foreground">
                                    Grade:{' '}
                                </span>
                                {student.grade_level}
                            </div>
                        )}
                        {student.section && (
                            <div>
                                <span className="text-muted-foreground">
                                    Section:{' '}
                                </span>
                                {student.section}
                            </div>
                        )}
                    </div>

                    <div className="rounded-lg bg-muted/50 p-3">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <Wallet className="h-4 w-4 text-muted-foreground" />
                                <span className="text-sm text-muted-foreground">
                                    Wallet Balance
                                </span>
                            </div>
                            <span className="font-semibold">
                                {formatCurrency(student.wallet_balance)}
                            </span>
                        </div>
                    </div>

                    <div className="flex items-center justify-between gap-2 pt-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => setWalletOpen(true)}
                        >
                            <Wallet className="mr-1 h-4 w-4" />
                            Manage Wallet
                        </Button>
                        <div className="flex gap-1">
                            <Button
                                variant="ghost"
                                size="icon"
                                onClick={() => setEditing(true)}
                            >
                                <Pencil className="h-4 w-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="icon"
                                className="text-red-600 hover:text-red-700"
                                onClick={() => setConfirmDeleteOpen(true)}
                            >
                                <Trash2 className="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <EditStudentModal
                student={student}
                open={editing}
                onClose={() => setEditing(false)}
            />
            <WalletModal
                student={student}
                open={walletOpen}
                onClose={() => setWalletOpen(false)}
            />
            <AlertDialog
                open={confirmDeleteOpen}
                title="Delete student?"
                description="This action cannot be undone. Students with existing orders cannot be deleted."
                confirmLabel="Delete"
                cancelLabel="Cancel"
                destructive
                onCancel={() => setConfirmDeleteOpen(false)}
                onConfirm={() => {
                    setConfirmDeleteOpen(false);
                    doDelete();
                }}
            />
        </>
    );
}

export default function StudentsIndex({
    students,
    filters,
}: StudentPageProps) {
    const [search, setSearch] = useState(filters?.search ?? '');
    const [status, setStatus] = useState(filters?.status ?? '');
    const [openCreate, setOpenCreate] = useState(false);

    useEffect(() => {
        const t = setTimeout(() => {
            const params: Record<string, string> = {};
            if (search) params.search = search;
            if (status) params.status = status;
            const queryString = new URLSearchParams(params).toString();
            const baseUrl = window.location.pathname;
            const url = queryString ? `${baseUrl}?${queryString}` : baseUrl;
            router.get(
                url,
                {},
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 300);
        return () => clearTimeout(t);
    }, [search, status]);

    const handlePageChange = (url: string | null) => {
        if (url) {
            router.visit(url, { preserveState: true, preserveScroll: true });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Students" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
                <div className="flex items-center justify-between gap-2">
                    <div>
                        <h1 className="text-xl font-semibold">Students</h1>
                        <p className="text-sm text-muted-foreground">
                            Manage student accounts and wallets
                        </p>
                    </div>
                    <Button onClick={() => setOpenCreate(true)}>
                        <Plus className="mr-1 h-4 w-4" />
                        Add Student
                    </Button>
                </div>

                <div className="flex flex-col gap-2 rounded-lg border p-3 md:flex-row md:items-center md:justify-between">
                    <div className="relative w-full md:w-80">
                        <Search className="pointer-events-none absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search by name or ID..."
                            className="pl-8"
                        />
                    </div>
                    <Select
                        value={status || 'all'}
                        onValueChange={(value) =>
                            setStatus(value === 'all' ? '' : value)
                        }
                    >
                        <SelectTrigger className="w-[150px]">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                {students.data.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-12 text-center">
                        <User className="mb-4 h-12 w-12 text-muted-foreground" />
                        <p className="text-sm text-muted-foreground">
                            No students found.
                        </p>
                    </div>
                ) : (
                    <>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            {students.data.map((student) => (
                                <StudentCard
                                    key={student.id}
                                    student={student}
                                />
                            ))}
                        </div>

                        {students.last_page > 1 && (
                            <div className="flex items-center justify-between">
                                <p className="text-sm text-muted-foreground">
                                    Showing{' '}
                                    {(students.current_page - 1) *
                                        students.per_page +
                                        1}{' '}
                                    to{' '}
                                    {Math.min(
                                        students.current_page *
                                            students.per_page,
                                        students.total,
                                    )}{' '}
                                    of {students.total} students
                                </p>
                                <div className="flex gap-1">
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        disabled={students.current_page === 1}
                                        onClick={() =>
                                            handlePageChange(
                                                students.links[0]?.url,
                                            )
                                        }
                                    >
                                        <ChevronLeft className="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        disabled={
                                            students.current_page ===
                                            students.last_page
                                        }
                                        onClick={() =>
                                            handlePageChange(
                                                students.links[
                                                    students.links.length - 1
                                                ]?.url,
                                            )
                                        }
                                    >
                                        <ChevronRight className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        )}
                    </>
                )}
            </div>

            <CreateStudentModal
                open={openCreate}
                onClose={() => setOpenCreate(false)}
            />
        </AppLayout>
    );
}
