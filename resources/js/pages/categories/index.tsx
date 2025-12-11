import AppLayout from '@/layouts/app-layout';
import {
    destroy as categoriesDestroy,
    store as categoriesStore,
    update as categoriesUpdate,
} from '@/routes/categories';
import { type BreadcrumbItem } from '@/types';
import type { CategoryNode, CategoryPageProps } from '@/types/category.d';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import {
    ChevronDown,
    ChevronRight,
    FolderTree,
    Link as LinkIcon,
    Pencil,
    Plus,
    Trash2,
} from 'lucide-react';
import { type FormEvent, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Categories', href: '/categories' },
];

function CategoryItem({ node }: { node: CategoryNode }) {
    const [open, setOpen] = useState(true);
    const [addingChild, setAddingChild] = useState(false);
    const [editing, setEditing] = useState(false);
    const hasChildren = (node.children?.length ?? 0) > 0;

    const createChild = useForm({
        name: '',
        slug: '' as string | null,
        parent_id: Number(node.id),
    });
    const editForm = useForm({ name: node.name, slug: node.slug ?? '' });

    const onCreateChild = (e: FormEvent) => {
        e.preventDefault();
        createChild.post(categoriesStore().url, {
            onSuccess: () => {
                setAddingChild(false);
                createChild.reset();
            },
            preserveScroll: true,
        });
    };

    const onUpdate = (e: FormEvent) => {
        e.preventDefault();
        editForm.put(categoriesUpdate(node.id).url, {
            onSuccess: () => setEditing(false),
            preserveScroll: true,
        });
    };

    const onDelete = () => {
        if (!confirm('Delete this category and its sub-categories?')) return;
        editForm.delete(categoriesDestroy(node.id).url, {
            preserveScroll: true,
        });
    };

    return (
        <div className="rounded-lg border border-sidebar-border/60 dark:border-sidebar-border">
            <div className="flex items-center gap-2 p-3">
                <button
                    className={`inline-flex h-6 w-6 items-center justify-center rounded hover:bg-muted ${!hasChildren ? 'pointer-events-none opacity-0' : ''}`}
                    onClick={() => setOpen((o) => !o)}
                    aria-label={open ? 'Collapse' : 'Expand'}
                >
                    {open ? (
                        <ChevronDown className="h-4 w-4" />
                    ) : (
                        <ChevronRight className="h-4 w-4" />
                    )}
                </button>
                <FolderTree className="h-4 w-4 text-muted-foreground" />
                <div className="flex min-w-0 flex-1 items-center justify-between gap-2">
                    <div className="min-w-0">
                        {editing ? (
                            <form
                                onSubmit={onUpdate}
                                className="flex flex-col gap-1 md:flex-row md:items-center md:gap-2"
                            >
                                <input
                                    className="h-8 w-full rounded border border-input bg-background px-2 text-sm md:w-56"
                                    placeholder="Name"
                                    value={editForm.data.name}
                                    onChange={(e) =>
                                        editForm.setData('name', e.target.value)
                                    }
                                    required
                                />
                                <input
                                    className="h-8 w-full rounded border border-input bg-background px-2 text-sm md:w-56"
                                    placeholder="Slug (optional)"
                                    value={editForm.data.slug ?? ''}
                                    onChange={(e) =>
                                        editForm.setData('slug', e.target.value)
                                    }
                                />
                                <button className="h-8 rounded bg-primary px-3 text-xs text-primary-foreground">
                                    Save
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setEditing(false)}
                                    className="h-8 rounded border px-3 text-xs"
                                >
                                    Cancel
                                </button>
                            </form>
                        ) : (
                            <>
                                <div className="truncate font-medium">
                                    {node.name}
                                </div>
                                <div className="truncate text-xs text-muted-foreground">
                                    menu/{node.slug}
                                </div>
                            </>
                        )}
                    </div>
                    <div className="flex items-center gap-2">
                        <button
                            onClick={() => setAddingChild((s) => !s)}
                            className="inline-flex items-center gap-1 rounded border px-2 py-1 text-xs"
                        >
                            <Plus className="h-3 w-3" /> Sub-category
                        </button>
                        <button
                            onClick={() => setEditing((s) => !s)}
                            className="inline-flex items-center gap-1 rounded border px-2 py-1 text-xs"
                        >
                            <Pencil className="h-3 w-3" /> Edit
                        </button>
                        <button
                            onClick={onDelete}
                            className="inline-flex items-center gap-1 rounded border px-2 py-1 text-xs text-red-600"
                        >
                            <Trash2 className="h-3 w-3" /> Delete
                        </button>
                        <Link
                            href={`/menu/${encodeURIComponent(String(node.slug))}`}
                            className="inline-flex items-center gap-1 text-xs text-primary hover:underline"
                            title="View products for this category (placeholder)"
                        >
                            <LinkIcon className="h-3 w-3" /> View Products
                        </Link>
                    </div>
                </div>
            </div>

            {addingChild && (
                <div className="border-t border-sidebar-border/60 p-3 dark:border-sidebar-border">
                    <form
                        onSubmit={onCreateChild}
                        className="flex flex-col gap-2 md:flex-row md:items-center md:gap-2"
                    >
                        <input
                            className="h-8 w-full rounded border border-input bg-background px-2 text-sm md:w-56"
                            placeholder="Sub-category name"
                            value={createChild.data.name}
                            onChange={(e) =>
                                createChild.setData('name', e.target.value)
                            }
                            required
                        />
                        <input
                            className="h-8 w-full rounded border border-input bg-background px-2 text-sm md:w-56"
                            placeholder="Slug (optional)"
                            value={createChild.data.slug ?? ''}
                            onChange={(e) =>
                                createChild.setData('slug', e.target.value)
                            }
                        />
                        <button className="h-8 rounded bg-primary px-3 text-xs text-primary-foreground">
                            Add
                        </button>
                        <button
                            type="button"
                            onClick={() => setAddingChild(false)}
                            className="h-8 rounded border px-3 text-xs"
                        >
                            Cancel
                        </button>
                    </form>
                    {createChild.errors.name && (
                        <div className="mt-1 text-xs text-red-600">
                            {createChild.errors.name}
                        </div>
                    )}
                </div>
            )}

            {hasChildren && open && (
                <div className="border-t border-sidebar-border/60 p-3 dark:border-sidebar-border">
                    <div className="grid gap-2 md:grid-cols-2 lg:grid-cols-3">
                        {node.children!.map((child) => (
                            <div key={child.id} className="">
                                <CategoryLeaf node={child} />
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}

function CategoryLeaf({ node }: { node: CategoryNode }) {
    const [editing, setEditing] = useState(false);
    const hasChildren = (node.children?.length ?? 0) > 0;
    const form = useForm({ name: node.name, slug: node.slug ?? '' });

    const onUpdate = (e: FormEvent) => {
        e.preventDefault();
        form.put(categoriesUpdate(node.id).url, {
            onSuccess: () => setEditing(false),
            preserveScroll: true,
        });
    };

    const onDelete = () => {
        if (!confirm('Delete this sub-category?')) return;
        form.delete(categoriesDestroy(node.id).url, { preserveScroll: true });
    };

    return (
        <div className="rounded-md border border-sidebar-border/60 p-3 dark:border-sidebar-border">
            <div className="mb-2 flex items-center gap-2">
                <FolderTree className="h-4 w-4 text-muted-foreground" />
                {editing ? (
                    <form
                        onSubmit={onUpdate}
                        className="flex flex-1 flex-col gap-1 md:flex-row md:items-center md:gap-2"
                    >
                        <input
                            className="h-8 w-full rounded border border-input bg-background px-2 text-sm md:w-40"
                            placeholder="Name"
                            value={form.data.name}
                            onChange={(e) =>
                                form.setData('name', e.target.value)
                            }
                            required
                        />
                        <input
                            className="h-8 w-full rounded border border-input bg-background px-2 text-sm md:w-40"
                            placeholder="Slug (optional)"
                            value={form.data.slug ?? ''}
                            onChange={(e) =>
                                form.setData('slug', e.target.value)
                            }
                        />
                        <button className="h-8 rounded bg-primary px-3 text-xs text-primary-foreground">
                            Save
                        </button>
                        <button
                            type="button"
                            onClick={() => setEditing(false)}
                            className="h-8 rounded border px-3 text-xs"
                        >
                            Cancel
                        </button>
                    </form>
                ) : (
                    <div className="truncate font-medium">{node.name}</div>
                )}
            </div>
            <div className="flex items-center justify-between gap-2">
                <div className="text-xs text-muted-foreground">
                    /menu?category={node.slug}
                </div>
                <div className="flex items-center gap-2">
                    <button
                        onClick={() => setEditing((s) => !s)}
                        className="inline-flex items-center gap-1 rounded border px-2 py-1 text-xs"
                    >
                        <Pencil className="h-3 w-3" /> Edit
                    </button>
                    <button
                        onClick={onDelete}
                        className="inline-flex items-center gap-1 rounded border px-2 py-1 text-xs text-red-600"
                    >
                        <Trash2 className="h-3 w-3" /> Delete
                    </button>
                    <Link
                        href={`/menu?category=${encodeURIComponent(String(node.slug))}`}
                        className="inline-flex items-center gap-1 text-xs text-primary hover:underline"
                    >
                        <LinkIcon className="h-3 w-3" /> View Products
                    </Link>
                </div>
            </div>
            {hasChildren && (
                <div className="mt-2 border-t border-sidebar-border/60 pt-2 text-xs dark:border-sidebar-border">
                    <div className="mb-1 font-medium">Sub-categories</div>
                    <ul className="ml-4 list-disc space-y-1">
                        {node.children!.map((c) => (
                            <li key={c.id} className="truncate">
                                {c.name}
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}

export default function CategoriesIndex() {
    const { props } = usePage<CategoryPageProps>();
    const categories = props.categories ?? [];
    const createRoot = useForm({ name: '', slug: '' as string | null });

    const submitRoot = (e: FormEvent) => {
        e.preventDefault();
        createRoot.post(categoriesStore().url, {
            preserveScroll: true,
            onSuccess: () => createRoot.reset(),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Categories" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div>
                    <h1 className="text-xl font-semibold">Categories</h1>
                    <p className="text-sm text-muted-foreground">
                        Manage food categories and sub-categories.
                    </p>
                </div>

                <div className="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                    <form
                        onSubmit={submitRoot}
                        className="flex flex-col gap-2 md:flex-row md:items-center md:gap-2"
                    >
                        <input
                            className="h-9 w-full rounded border border-input bg-background px-3 text-sm md:w-72"
                            placeholder="New main category name"
                            value={createRoot.data.name}
                            onChange={(e) =>
                                createRoot.setData('name', e.target.value)
                            }
                            required
                        />
                        <input
                            className="h-9 w-full rounded border border-input bg-background px-3 text-sm md:w-64"
                            placeholder="Slug (optional)"
                            value={createRoot.data.slug ?? ''}
                            onChange={(e) =>
                                createRoot.setData('slug', e.target.value)
                            }
                        />
                        <button className="h-9 rounded bg-primary px-3 text-sm text-primary-foreground">
                            <Plus className="mr-1 inline-block h-4 w-4" /> Add
                            main category
                        </button>
                    </form>
                    {createRoot.errors.name && (
                        <div className="mt-1 text-xs text-red-600">
                            {createRoot.errors.name}
                        </div>
                    )}
                </div>

                {categories.length === 0 ? (
                    <div className="text-sm text-muted-foreground">
                        No categories yet.
                    </div>
                ) : (
                    <div className="grid gap-4">
                        {categories.map((cat) => (
                            <CategoryItem key={cat.id} node={cat} />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
