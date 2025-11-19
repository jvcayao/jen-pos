import AppLayout from '@/layouts/app-layout'
import { Head, router, useForm, usePage } from '@inertiajs/react'
import { useEffect, useMemo, useState } from 'react'
import { type BreadcrumbItem } from '@/types'
import { destroy as productsDestroy, index as productsIndex, store as productsStore, update as productsUpdate } from '@/routes/products'
import { Plus, X, Pencil, Trash2, Image as ImageIcon, Search } from 'lucide-react'
// Types coming from the controller payload
export type Product = {
  id: number
  name: string
  description?: string | null
  price: string | number
  image_url?: string | null
    category_parent_id?: string | null
  category_id?: string | null
  category_name?: string | null
}

export type CategoryOption = { id: string; name: string; slug: string }

interface PageProps {
  products: Product[]
  categories: CategoryOption[]
  filters: { search?: string; category?: string }
}

function useQuerySync(initial: { search?: string; category?: string }) {
  const [search, setSearch] = useState(initial.search ?? '')
  const [category, setCategory] = useState(initial.category ?? '')

  // Submit query when user stops typing or changes category
  useEffect(() => {
      const t = setTimeout(() => {
          // Build query string from search and category
          const params: Record<string, string> = {};
          if (search) params.search = search;
          // Use taxonomy slug for category
          if (category) params.category = category;
          const queryString = new URLSearchParams(params).toString();
          // Use pathname only, not full href
          const baseUrl = window.location.pathname;
          const url = queryString ? `${baseUrl}?${queryString}` : baseUrl;
          router.get(
              url,
              {},
              { preserveState: true, preserveScroll: true, replace: true }
          )
      }, 300)
    return () => clearTimeout(t)
  }, [search, category])

  return { search, setSearch, category, setCategory }
}

function CreateProductModal({ open, onClose, categories }: { open: boolean; onClose: () => void; categories: CategoryOption[] }) {
  const form = useForm<{ name: string; description: string; price: number | string; category_id: string | '' ; image: File | null }>({
    name: '',
    description: '',
    price: '',
    category_id: '',
    image: null,
  })

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    form.post(productsStore().url, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        form.reset()
        onClose()
      },
    })
  }

  if (!open) return null

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div className="w-full max-w-lg rounded-lg border border-sidebar-border/60 bg-background p-4 shadow-xl dark:border-sidebar-border">
        <div className="mb-3 flex items-center justify-between">
          <h2 className="text-lg font-semibold">Add product</h2>
          <button onClick={onClose} className="rounded p-1 hover:bg-muted" aria-label="Close">
            <X className="h-5 w-5" />
          </button>
        </div>
        <form onSubmit={submit} className="space-y-3">
          <div className="grid gap-2">
            <label className="text-sm">Name</label>
            <input className="h-9 rounded border border-input bg-background px-3 text-sm" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required />
            {form.errors.name && <p className="text-xs text-red-600">{form.errors.name}</p>}
          </div>
          <div className="grid gap-2">
            <label className="text-sm">Description</label>
            <textarea className="min-h-24 rounded border border-input bg-background px-3 py-2 text-sm" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
            {form.errors.description && <p className="text-xs text-red-600">{form.errors.description}</p>}
          </div>
          <div className="grid gap-2">
            <label className="text-sm">Price</label>
            <input type="number" step="0.01" className="h-9 rounded border border-input bg-background px-3 text-sm" value={form.data.price} onChange={(e) => form.setData('price', e.target.value)} required />
            {form.errors.price && <p className="text-xs text-red-600">{form.errors.price}</p>}
          </div>
          <div className="grid gap-2">
            <label className="text-sm">Category</label>
            <select className="h-9 rounded border border-input bg-background px-3 text-sm" value={form.data.category_id} onChange={(e) => form.setData('category_id', e.target.value)}>
              <option value="">— None —</option>
              {categories.map((c) => (
                <option key={c.id} value={c.id}>{c.name}</option>
              ))}
            </select>
          </div>
          <div className="grid gap-2">
            <label className="text-sm">Image (jpeg, jpg, png)</label>
            <input accept="image/jpeg,image/png,.jpg,.jpeg,.png" type="file" onChange={(e) => form.setData('image', e.target.files?.[0] ?? null)} />
            {form.errors.image && <p className="text-xs text-red-600">{form.errors.image}</p>}
          </div>

          <div className="flex items-center justify-end gap-2 pt-2">
            <button type="button" onClick={onClose} className="h-9 rounded border px-3 text-sm">Cancel</button>
            <button className="h-9 rounded bg-primary px-3 text-sm text-primary-foreground disabled:opacity-50" disabled={form.processing}>Create</button>
          </div>
        </form>
      </div>
    </div>
  )
}

function ProductCard({ product, categories }: { product: Product; categories: CategoryOption[] }) {
  const [editing, setEditing] = useState(false)
  const form = useForm<{ name: string; description: string; price: number | string; category_id: string | ''; image: File | null }>({
    name: product.name,
    description: product.description ?? '',
    price: product.price,
    category_id: product.category_id ?? '',
    image: null,
  })

  const onUpdate = (e: React.FormEvent) => {
    e.preventDefault()
    form.post(productsUpdate(product.id).url, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => setEditing(false),
    })
  }

  const onDelete = () => {
    if (!confirm('Delete this product?')) return
    form.delete(productsDestroy(product.id).url, { preserveScroll: true })
  }

  return (
    <div className="group relative flex flex-col overflow-hidden rounded-lg border border-sidebar-border/60 bg-background shadow-sm transition hover:shadow-md dark:border-sidebar-border">
      {product.image_url ? (
        <img src={product.image_url} alt={product.name} className="aspect-[4/3] w-full object-cover" />
      ) : (
        <div className="aspect-[4/3] w-full items-center justify-center bg-muted/40 text-muted-foreground">
          <div className="flex h-full w-full items-center justify-center gap-2 text-sm">
            <ImageIcon className="h-5 w-5" /> No image
          </div>
        </div>
      )}
      <div className="flex flex-1 flex-col gap-2 p-3">
        {editing ? (
          <form onSubmit={onUpdate} className="flex flex-col gap-2">
            <input className="h-8 rounded border border-input bg-background px-2 text-sm" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required />
            <textarea className="min-h-20 rounded border border-input bg-background px-2 py-1 text-sm" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
            <div className="grid grid-cols-2 gap-2">
              <input type="number" step="0.01" className="h-8 rounded border border-input bg-background px-2 text-sm" value={form.data.price} onChange={(e) => form.setData('price', e.target.value)} required />
              <select className="h-8 rounded border border-input bg-background px-2 text-sm" value={form.data.category_id} onChange={(e) => form.setData('category_id', e.target.value)}>
                <option value="">— None —</option>
                {categories.map((c) => (
                  <option key={c.id} value={c.slug}>{c.name}</option>
                ))}
              </select>
            </div>
            <input accept="image/jpeg,image/png,.jpg,.jpeg,.png" type="file" onChange={(e) => form.setData('image', e.target.files?.[0] ?? null)} />
            {Object.values(form.errors).length > 0 && (
              <div className="text-xs text-red-600">{Object.values(form.errors)[0]}</div>
            )}
            <div className="mt-1 flex items-center gap-2">
              <button className="h-8 rounded bg-primary px-3 text-xs text-primary-foreground disabled:opacity-50" disabled={form.processing}>Save</button>
              <button type="button" onClick={() => setEditing(false)} className="h-8 rounded border px-3 text-xs">Cancel</button>
            </div>
          </form>
        ) : (
          <>
            <div className="flex items-start justify-between gap-2">
              <div className="min-w-0">
                <div className="truncate text-sm font-medium">{product.name}</div>
                <div className="text-xs text-muted-foreground">{product.category_name ?? 'Uncategorized'}</div>
              </div>
              <div className="shrink-0 rounded bg-muted px-2 py-0.5 text-xs font-semibold">₱{Number(product.price).toFixed(2)}</div>
            </div>
            {product.description && (
              <div className="line-clamp-3 text-xs text-muted-foreground">{product.description}</div>
            )}
            <div className="mt-auto flex items-center justify-end gap-2 pt-1">
              <button onClick={() => setEditing(true)} className="inline-flex items-center gap-1 rounded border px-2 py-1 text-xs"><Pencil className="h-3 w-3" /> Edit</button>
              <button onClick={onDelete} className="inline-flex items-center gap-1 rounded border px-2 py-1 text-xs text-red-600"><Trash2 className="h-3 w-3" /> Delete</button>
            </div>
          </>
        )}
      </div>
    </div>
  )
}

export default function ProductsIndex() {
  const { props } = usePage<PageProps>()
  const products = props.products ?? []
  const categories = props.categories ?? []
  const { search, setSearch, category, setCategory } = useQuerySync(props.filters ?? {})
  const [openCreate, setOpenCreate] = useState(false)


  const breadcrumbs: BreadcrumbItem[] = useMemo(() => [
    { title: 'Products', href: productsIndex().url },
  ], [])

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Products" />
      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="flex items-center justify-between gap-2">
          <div>
            <h1 className="text-xl font-semibold">Products</h1>
            <p className="text-sm text-muted-foreground">Browse and manage your food products.</p>
          </div>
          <button onClick={() => setOpenCreate(true)} className="inline-flex items-center gap-1 rounded bg-primary px-3 py-2 text-sm text-primary-foreground">
            <Plus className="h-4 w-4" /> Add product
          </button>
        </div>

        <div className="flex flex-col gap-2 rounded-lg border border-sidebar-border/60 p-3 md:flex-row md:items-center md:justify-between dark:border-sidebar-border">
          <div className="flex w-full items-center gap-2 md:w-auto">
            <div className="relative w-full md:w-80">
              <Search className="pointer-events-none absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
              <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search products..." className="h-9 w-full rounded border border-input bg-background pl-8 pr-3 text-sm" />
            </div>
          </div>
          <div className="flex items-center gap-2">
            <select value={category} onChange={(e) => setCategory(e.target.value)} className="h-9 rounded border border-input bg-background px-3 text-sm">
              <option value="">All categories</option>
              {categories.map((c) => (
                  <option key={c.id} value={c.slug}>{c.name}</option>
              ))}
            </select>
          </div>
        </div>

        {products.length === 0 ? (
          <div className="text-sm text-muted-foreground">No products found.</div>
        ) : (
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {products.map((p) => (
              <ProductCard key={p.id} product={p} categories={categories} />
            ))}
          </div>
        )}
      </div>

      <CreateProductModal open={openCreate} onClose={() => setOpenCreate(false)} categories={categories} />
    </AppLayout>
  )
}
