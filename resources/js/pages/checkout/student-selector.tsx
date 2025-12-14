import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import type { CheckoutStudent } from '@/types/checkout.d';
import { Camera, ScanLine, Search, User, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

declare global {
    interface BarcodeDetectorConstructor {
        new (options?: { formats?: string[] }): BarcodeDetectorInstance;
    }

    interface BarcodeDetectorInstance {
        detect(
            source:
                | CanvasImageSource
                | HTMLVideoElement
                | HTMLCanvasElement
                | ImageBitmap
                | ImageData,
        ): Promise<Array<{ rawValue: string }>>;
    }

    interface Window {
        BarcodeDetector?: BarcodeDetectorConstructor;
    }
}

interface StudentSelectorProps {
    selectedStudent: CheckoutStudent | null;
    onSelect: (student: CheckoutStudent | null) => void;
    disabled?: boolean;
}

export function StudentSelector({
    selectedStudent,
    onSelect,
    disabled = false,
}: StudentSelectorProps) {
    const [studentSearch, setStudentSearch] = useState('');
    const [studentSearchResults, setStudentSearchResults] = useState<
        CheckoutStudent[]
    >([]);
    const [studentSearchLoading, setStudentSearchLoading] = useState(false);
    const [showStudentDropdown, setShowStudentDropdown] = useState(false);
    const [scannerOpen, setScannerOpen] = useState(false);
    const [scannerError, setScannerError] = useState('');
    const [scannerStatus, setScannerStatus] = useState('Idle');
    const videoRef = useRef<HTMLVideoElement>(null);
    const streamRef = useRef<MediaStream | null>(null);
    const scanFrame = useRef<number>();

    useEffect(() => {
        if (studentSearch.length < 2) {
            setStudentSearchResults([]);
            return;
        }

        const searchStudents = async () => {
            setStudentSearchLoading(true);
            try {
                const response = await fetch(
                    `/students/search?q=${encodeURIComponent(studentSearch)}`,
                );
                const data = await response.json();
                setStudentSearchResults(data.students);
                setShowStudentDropdown(true);
            } catch (error) {
                console.error('Failed to search students:', error);
            }
            setStudentSearchLoading(false);
        };

        const debounce = setTimeout(searchStudents, 300);
        return () => clearTimeout(debounce);
    }, [studentSearch]);

    useEffect(() => {
        if (!scannerOpen) {
            stopScanner();
            return;
        }

        setScannerStatus('Initializing camera…');
        startScanner();

        return () => {
            stopScanner();
        };
    }, [scannerOpen]);

    const stopScanner = () => {
        if (scanFrame.current) {
            cancelAnimationFrame(scanFrame.current);
            scanFrame.current = undefined;
        }

        if (streamRef.current) {
            streamRef.current.getTracks().forEach((track) => track.stop());
            streamRef.current = null;
        }

        if (videoRef.current) {
            videoRef.current.srcObject = null;
        }

        setScannerStatus('Idle');
    };

    const handleSelectStudent = (student: CheckoutStudent) => {
        onSelect(student);
        setStudentSearch('');
        setStudentSearchResults([]);
        setShowStudentDropdown(false);
    };

    const handleClearStudent = () => {
        onSelect(null);
        setStudentSearch('');
    };

    const handleScanValue = async (rawValue: string) => {
        stopScanner();
        setScannerOpen(false);

        const token = rawValue.startsWith('student:')
            ? rawValue.replace('student:', '')
            : rawValue;

        try {
            const response = await fetch(`/students/scan/${token}`);
            if (!response.ok) {
                throw new Error('Student not found');
            }

            const data = await response.json();
            handleSelectStudent(data.student);
        } catch (error) {
            console.error('Failed to load student from QR:', error);
        }
    };

    const startScanner = async () => {
        setScannerError('');

        if (!window.BarcodeDetector) {
            setScannerError(
                'Live QR scanning is not supported on this device/browser.',
            );
            setScannerStatus('Unavailable');
            return;
        }

        try {
            const detector = new window.BarcodeDetector({
                formats: ['qr_code'],
            });
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' },
                audio: false,
            });
            streamRef.current = stream;
            if (videoRef.current) {
                videoRef.current.srcObject = stream;
                await videoRef.current.play();
            }

            setScannerStatus('Scanning…');

            const scan = async () => {
                if (!videoRef.current) {
                    return;
                }

                try {
                    const barcodes = await detector.detect(videoRef.current);
                    if (barcodes.length > 0) {
                        await handleScanValue(barcodes[0].rawValue);
                        return;
                    }
                } catch (error) {
                    console.error('Failed to detect barcode', error);
                }

                scanFrame.current = requestAnimationFrame(scan);
            };

            scan();
        } catch (error) {
            console.error('Failed to start scanner', error);
            setScannerError('Unable to access the device camera.');
            setScannerStatus('Error');
        }
    };

    const renderSelected = () => {
        if (!selectedStudent) return null;

        return (
            <div className="rounded-lg bg-primary/10 p-3">
                <div className="flex items-center justify-between">
                    <div>
                        <div className="font-medium">
                            {selectedStudent.full_name}
                        </div>
                        <div className="text-sm text-muted-foreground">
                            {selectedStudent.student_id}
                            {selectedStudent.grade_level &&
                                ` | ${selectedStudent.grade_level}`}
                            {selectedStudent.section &&
                                ` - ${selectedStudent.section}`}
                        </div>
                        {selectedStudent.has_wallet ? (
                            <div className="mt-2 rounded bg-primary/20 p-2 text-sm">
                                <div className="text-xs text-muted-foreground">
                                    {selectedStudent.wallet_type === 'subscribe'
                                        ? 'Subscribe'
                                        : 'Non-Subscribe'}{' '}
                                    Wallet Balance
                                </div>
                                <div className="font-semibold">
                                    {formatCurrency(
                                        selectedStudent.wallet_balance,
                                    )}
                                </div>
                            </div>
                        ) : (
                            <div className="mt-2 rounded bg-orange-100 p-2 text-sm text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">
                                No wallet assigned
                            </div>
                        )}
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={handleClearStudent}
                        disabled={disabled}
                    >
                        <X className="h-4 w-4" />
                    </Button>
                </div>
            </div>
        );
    };

    return (
        <div className="space-y-4 rounded-lg border bg-card p-4">
            <div className="flex items-center justify-between">
                <div>
                    <div className="flex items-center gap-2">
                        <User className="h-4 w-4" />
                        <h3 className="font-medium">Assign Student</h3>
                    </div>
                    <p className="mt-1 text-xs text-muted-foreground">
                        Link every order to a student. Wallet payments require a
                        student selection.
                    </p>
                </div>
                <Badge variant="outline" className="text-xs">
                    {selectedStudent ? 'Selected' : 'Required'}
                </Badge>
            </div>

            <div className="flex items-center gap-2">
                <div className="relative flex-1">
                    <Search className="pointer-events-none absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                    <Input
                        placeholder="Search student name or ID"
                        value={studentSearch}
                        onChange={(e) => setStudentSearch(e.target.value)}
                        disabled={disabled}
                        onFocus={() =>
                            studentSearchResults.length > 0 &&
                            setShowStudentDropdown(true)
                        }
                        className="pl-8"
                    />
                    {showStudentDropdown && studentSearchResults.length > 0 && (
                        <div className="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border bg-background shadow-lg">
                            {studentSearchResults.map((student) => (
                                <div
                                    key={student.id}
                                    className="cursor-pointer p-3 hover:bg-muted"
                                    onClick={() => handleSelectStudent(student)}
                                >
                                    <div className="font-medium">
                                        {student.full_name}
                                    </div>
                                    <div className="flex items-center justify-between text-sm text-muted-foreground">
                                        <span>{student.student_id}</span>
                                        {student.has_wallet ? (
                                            <span
                                                className={
                                                    student.wallet_balance >= 0
                                                        ? 'text-green-600'
                                                        : 'text-red-600'
                                                }
                                            >
                                                {formatCurrency(
                                                    student.wallet_balance,
                                                )}
                                            </span>
                                        ) : (
                                            <span className="text-orange-600">
                                                No wallet
                                            </span>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                    {studentSearchLoading && (
                        <div className="absolute z-10 mt-1 w-full rounded-lg border bg-background p-3 text-center text-sm text-muted-foreground shadow-lg">
                            Searching...
                        </div>
                    )}
                    {studentSearch.length >= 2 &&
                        !studentSearchLoading &&
                        studentSearchResults.length === 0 && (
                            <div className="absolute z-10 mt-1 w-full rounded-lg border bg-background p-3 text-center text-sm text-muted-foreground shadow-lg">
                                No students found
                            </div>
                        )}
                </div>
                <Button
                    variant="outline"
                    type="button"
                    onClick={() => setScannerOpen(true)}
                    disabled={disabled}
                    className="flex items-center gap-2"
                >
                    <ScanLine className="h-4 w-4" />
                    Scan QR
                </Button>
            </div>

            {renderSelected()}

            <Dialog open={scannerOpen} onOpenChange={setScannerOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Camera className="h-4 w-4" />
                            Scan Student QR
                        </DialogTitle>
                        <DialogDescription>
                            Align the QR code within the frame to capture the
                            student information.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="space-y-3">
                        <div className="overflow-hidden rounded-xl border bg-black/80">
                            <video
                                ref={videoRef}
                                className="h-64 w-full object-cover"
                                autoPlay
                                muted
                                playsInline
                            />
                        </div>
                        <div className="text-sm text-muted-foreground">
                            Status: {scannerStatus}
                        </div>
                        {scannerError && (
                            <p className="text-sm text-red-600">
                                {scannerError}
                            </p>
                        )}
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    );
}

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(value);
};
