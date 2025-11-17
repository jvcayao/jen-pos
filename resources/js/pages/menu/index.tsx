import { BreadcrumbItem } from '@/types';
import { useMemo } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';


export default function OrdersIndex(){
    const breadcrumbs: BreadcrumbItem[] = useMemo(() => [
        { title: 'Menu', href: '/menu'},
    ], [])


    return (
        <AppLayout breadcrumbs={breadcrumbs} >
            <Head title="Orders" />
        </AppLayout>
    );
}
