import AlertDialog from '@/components/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
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
import { Head, Link, router, useForm } from '@inertiajs/react';
import {
    ChevronLeft,
    ChevronRight,
    Pencil,
    Plus,
    Search,
    Trash2,
    Users,
} from 'lucide-react';
import { type FormEvent, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Users', href: '/users' }];

interface Store {
    id: number;
    name: string;
    code: string;
}

interface Role {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    stores: Store[];
    roles: Role[];
    created_at: string;
}

interface PaginatedUsers {
    data: User[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface UsersPageProps {
    users: PaginatedUsers;
    stores: Store[];
    roles: Role[];
    filters: {
        search?: string;
    };
}

interface UserFormData {
    name: string;
    email: string;
    password: string;
    store_ids: number[];
    role: string;
}

function getDefaultFormData(): UserFormData {
    return {
        name: '',
        email: '',
        password: '',
        store_ids: [],
        role: 'cashier',
    };
}

function getFormDataFromUser(user: User): UserFormData {
    return {
        name: user.name,
        email: user.email,
        password: '',
        store_ids: user.stores.map((s) => s.id),
        role: user.roles[0]?.name ?? 'cashier',
    };
}

function UserFormFields({
    form,
    stores,
    roles,
    isEdit = false,
}: {
    form: ReturnType<typeof useForm<UserFormData>>;
    stores: Store[];
    roles: Role[];
    isEdit?: boolean;
}) {
    const toggleStore = (storeId: number) => {
        const current = form.data.store_ids;
        if (current.includes(storeId)) {
            form.setData(
                'store_ids',
                current.filter((id) => id !== storeId),
            );
        } else {
            form.setData('store_ids', [...current, storeId]);
        }
    };

    return (
        <div className="space-y-4">
            <div className="grid gap-2">
                <Label>Name *</Label>
                <Input
                    value={form.data.name}
                    onChange={(e) => form.setData('name', e.target.value)}
                    placeholder="Full name"
                    required
                />
                {form.errors.name && (
                    <p className="text-xs text-red-600">{form.errors.name}</p>
                )}
            </div>

            <div className="grid gap-2">
                <Label>Email *</Label>
                <Input
                    type="email"
                    value={form.data.email}
                    onChange={(e) => form.setData('email', e.target.value)}
                    placeholder="email@example.com"
                    required
                />
                {form.errors.email && (
                    <p className="text-xs text-red-600">{form.errors.email}</p>
                )}
            </div>

            <div className="grid gap-2">
                <Label>{isEdit ? 'Password (leave blank to keep)' : 'Password *'}</Label>
                <Input
                    type="password"
                    value={form.data.password}
                    onChange={(e) => form.setData('password', e.target.value)}
                    placeholder={isEdit ? 'Leave blank to keep current' : 'Password'}
                    required={!isEdit}
                />
                {form.errors.password && (
                    <p className="text-xs text-red-600">{form.errors.password}</p>
                )}
            </div>

            <div className="grid gap-2">
                <Label>Role *</Label>
                <Select
                    value={form.data.role}
                    onValueChange={(value) => form.setData('role', value)}
                >
                    <SelectTrigger>
                        <SelectValue placeholder="Select role" />
                    </SelectTrigger>
                    <SelectContent>
                        {roles.map((role) => (
                            <SelectItem key={role.id} value={role.name}>
                                {role.name
                                    .replace(/-/g, ' ')
                                    .replace(/\b\w/g, (l) => l.toUpperCase())}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {form.errors.role && (
                    <p className="text-xs text-red-600">{form.errors.role}</p>
                )}
            </div>

            <div className="grid gap-2">
                <Label>Store Access *</Label>
                <div className="rounded-md border p-3">
                    <div className="space-y-3">
                        {stores.map((store) => (
                            <div
                                key={store.id}
                                className="flex items-center gap-3"
                            >
                                <Checkbox
                                    id={`store-${store.id}`}
                                    checked={form.data.store_ids.includes(store.id)}
                                    onCheckedChange={() => toggleStore(store.id)}
                                />
                                <Label
                                    htmlFor={`store-${store.id}`}
                                    className="cursor-pointer font-normal"
                                >
                                    {store.name}{' '}
                                    <span className="text-muted-foreground">
                                        ({store.code})
                                    </span>
                                </Label>
                            </div>
                        ))}
                    </div>
                </div>
                {form.errors.store_ids && (
                    <p className="text-xs text-red-600">{form.errors.store_ids}</p>
                )}
            </div>
        </div>
    );
}

export default function UsersIndex({
    users,
    stores,
    roles,
    filters,
}: UsersPageProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [showAddDialog, setShowAddDialog] = useState(false);
    const [showEditDialog, setShowEditDialog] = useState(false);
    const [showDeleteDialog, setShowDeleteDialog] = useState(false);
    const [selectedUser, setSelectedUser] = useState<User | null>(null);

    const addForm = useForm<UserFormData>(getDefaultFormData());
    const editForm = useForm<UserFormData>(getDefaultFormData());

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        router.get('/users', { search }, { preserveState: true });
    };

    const handleAdd = (e: FormEvent) => {
        e.preventDefault();
        addForm.post('/users', {
            onSuccess: () => {
                setShowAddDialog(false);
                addForm.reset();
            },
        });
    };

    const handleEdit = (e: FormEvent) => {
        e.preventDefault();
        if (!selectedUser) return;
        editForm.put(`/users/${selectedUser.id}`, {
            onSuccess: () => {
                setShowEditDialog(false);
                setSelectedUser(null);
            },
        });
    };

    const handleDelete = () => {
        if (!selectedUser) return;
        router.delete(`/users/${selectedUser.id}`, {
            onSuccess: () => {
                setShowDeleteDialog(false);
                setSelectedUser(null);
            },
        });
    };

    const openEditDialog = (user: User) => {
        setSelectedUser(user);
        editForm.setData(getFormDataFromUser(user));
        setShowEditDialog(true);
    };

    const openDeleteDialog = (user: User) => {
        setSelectedUser(user);
        setShowDeleteDialog(true);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Users" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>Users</CardTitle>
                                <CardDescription>
                                    Manage user accounts and their store access
                                </CardDescription>
                            </div>
                            <Button onClick={() => setShowAddDialog(true)}>
                                <Plus className="mr-2 h-4 w-4" />
                                Add User
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSearch} className="mb-4">
                            <div className="flex gap-2">
                                <div className="relative flex-1">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        placeholder="Search users..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="pl-9"
                                    />
                                </div>
                                <Button type="submit" variant="secondary">
                                    Search
                                </Button>
                            </div>
                        </form>

                        {users.data.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Users className="mb-4 h-12 w-12 text-muted-foreground" />
                                <h3 className="text-lg font-medium">No users found</h3>
                                <p className="text-sm text-muted-foreground">
                                    Get started by adding a new user
                                </p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b text-left text-sm text-muted-foreground">
                                            <th className="p-3 font-medium">Name</th>
                                            <th className="p-3 font-medium">Email</th>
                                            <th className="p-3 font-medium">Role</th>
                                            <th className="p-3 font-medium">Stores</th>
                                            <th className="p-3 font-medium">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {users.data.map((user) => (
                                            <tr
                                                key={user.id}
                                                className="border-b last:border-0"
                                            >
                                                <td className="p-3 font-medium">
                                                    {user.name}
                                                </td>
                                                <td className="p-3 text-muted-foreground">
                                                    {user.email}
                                                </td>
                                                <td className="p-3">
                                                    <span className="rounded-full bg-primary/10 px-2 py-1 text-xs font-medium text-primary">
                                                        {user.roles[0]?.name
                                                            ?.replace(/-/g, ' ')
                                                            .replace(/\b\w/g, (l) =>
                                                                l.toUpperCase(),
                                                            ) ?? 'No Role'}
                                                    </span>
                                                </td>
                                                <td className="p-3">
                                                    <div className="flex flex-wrap gap-1">
                                                        {user.stores.map((store) => (
                                                            <span
                                                                key={store.id}
                                                                className="rounded bg-secondary px-2 py-0.5 text-xs"
                                                            >
                                                                {store.code}
                                                            </span>
                                                        ))}
                                                    </div>
                                                </td>
                                                <td className="p-3">
                                                    <div className="flex gap-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            onClick={() =>
                                                                openEditDialog(user)
                                                            }
                                                        >
                                                            <Pencil className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            onClick={() =>
                                                                openDeleteDialog(user)
                                                            }
                                                        >
                                                            <Trash2 className="h-4 w-4 text-destructive" />
                                                        </Button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}

                        {users.last_page > 1 && (
                            <div className="mt-4 flex items-center justify-between">
                                <p className="text-sm text-muted-foreground">
                                    Showing {users.data.length} of {users.total} users
                                </p>
                                <div className="flex gap-1">
                                    {users.links.map((link, index) => {
                                        if (link.label.includes('Previous')) {
                                            return (
                                                <Button
                                                    key={index}
                                                    variant="outline"
                                                    size="icon"
                                                    disabled={!link.url}
                                                    asChild={!!link.url}
                                                >
                                                    {link.url ? (
                                                        <Link href={link.url}>
                                                            <ChevronLeft className="h-4 w-4" />
                                                        </Link>
                                                    ) : (
                                                        <ChevronLeft className="h-4 w-4" />
                                                    )}
                                                </Button>
                                            );
                                        }
                                        if (link.label.includes('Next')) {
                                            return (
                                                <Button
                                                    key={index}
                                                    variant="outline"
                                                    size="icon"
                                                    disabled={!link.url}
                                                    asChild={!!link.url}
                                                >
                                                    {link.url ? (
                                                        <Link href={link.url}>
                                                            <ChevronRight className="h-4 w-4" />
                                                        </Link>
                                                    ) : (
                                                        <ChevronRight className="h-4 w-4" />
                                                    )}
                                                </Button>
                                            );
                                        }
                                        return (
                                            <Button
                                                key={index}
                                                variant={link.active ? 'default' : 'outline'}
                                                size="icon"
                                                asChild={!!link.url}
                                            >
                                                {link.url ? (
                                                    <Link href={link.url}>{link.label}</Link>
                                                ) : (
                                                    link.label
                                                )}
                                            </Button>
                                        );
                                    })}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Add User Dialog */}
            <Dialog open={showAddDialog} onOpenChange={setShowAddDialog}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Add User</DialogTitle>
                        <DialogDescription>
                            Create a new user account
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleAdd}>
                        <UserFormFields form={addForm} stores={stores} roles={roles} />
                        <DialogFooter className="mt-4">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setShowAddDialog(false)}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                disabled={
                                    addForm.processing ||
                                    addForm.data.store_ids.length === 0
                                }
                            >
                                Create User
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Edit User Dialog */}
            <Dialog open={showEditDialog} onOpenChange={setShowEditDialog}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit User</DialogTitle>
                        <DialogDescription>
                            Update user account details
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleEdit}>
                        <UserFormFields
                            form={editForm}
                            stores={stores}
                            roles={roles}
                            isEdit
                        />
                        <DialogFooter className="mt-4">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setShowEditDialog(false)}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                disabled={
                                    editForm.processing ||
                                    editForm.data.store_ids.length === 0
                                }
                            >
                                Save Changes
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Delete User Dialog */}
            <AlertDialog
                open={showDeleteDialog}
                onOpenChange={setShowDeleteDialog}
                title="Delete User"
                description={`Are you sure you want to delete "${selectedUser?.name}"? This action cannot be undone.`}
                onConfirm={handleDelete}
                confirmText="Delete"
                variant="destructive"
            />
        </AppLayout>
    );
}
