import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Search, User } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Student Dashboard', href: '/student-dashboard' },
];

export default function StudentDashboardIndex() {
    const [searchQuery, setSearchQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const handleSearch = async () => {
        const queryValue = searchQuery.trim();
        if (!queryValue) return;

        setLoading(true);
        setError('');

        try {
            const response = await fetch(
                `/student-dashboard/search?student_id=${encodeURIComponent(queryValue)}`,
            );
            const data = await response.json();

            if (response.ok && data.student) {
                router.visit(`/student-dashboard/${data.student.id}`);
            } else {
                setError(data.error || 'Student not found');
            }
        } catch {
            setError('Failed to search for student');
        } finally {
            setLoading(false);
            setSearchQuery('');
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Student Dashboard" />
            <div className="flex h-full flex-1 flex-col items-center justify-center gap-6 p-4">
                <div className="text-center">
                    <h1 className="text-3xl font-bold">Student Dashboard</h1>
                    <p className="mt-2 text-muted-foreground">
                        Search for a student to view their order history and
                        wallet balance
                    </p>
                </div>

                <Card className="w-full max-w-md">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <User className="h-5 w-5" />
                            Find Student
                        </CardTitle>
                        <CardDescription>
                            Enter student ID to view their dashboard
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <div className="relative">
                                <Search className="pointer-events-none absolute top-3 left-3 h-4 w-4 text-muted-foreground" />
                                <Input
                                    type="text"
                                    value={searchQuery}
                                    onChange={(e) =>
                                        setSearchQuery(e.target.value)
                                    }
                                    onKeyDown={(e) =>
                                        e.key === 'Enter' && handleSearch()
                                    }
                                    placeholder="Enter student ID..."
                                    className="pl-9"
                                />
                            </div>
                            <Button
                                className="w-full"
                                onClick={() => handleSearch()}
                                disabled={loading || !searchQuery.trim()}
                            >
                                {loading ? 'Searching...' : 'Search'}
                            </Button>
                        </div>

                        {error && (
                            <div className="rounded-lg bg-destructive/10 p-3 text-center text-sm text-destructive">
                                {error}
                            </div>
                        )}
                    </CardContent>
                </Card>

                <div className="text-center text-sm text-muted-foreground">
                    <Badge variant="outline">Tip</Badge>
                    <span className="ml-2">
                        Students can view their order history and wallet balance
                        here
                    </span>
                </div>
            </div>
        </AppLayout>
    );
}
