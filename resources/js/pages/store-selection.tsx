import { Head, router } from '@inertiajs/react';
import { Building2 } from 'lucide-react';

import AppLoginLogoIcon from '@/components/app-login-logo-icon';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

interface Store {
    id: number;
    name: string;
    code: string;
    address: string | null;
}

interface Props {
    stores: Store[];
}

export default function StoreSelection({ stores }: Props) {
    const selectStore = (storeId: number) => {
        router.post('/store/select', { store_id: storeId });
    };

    return (
        <>
            <Head title="Select Store" />
            <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
                <div className="w-full max-w-2xl">
                    <div className="flex flex-col gap-8">
                        <div className="flex flex-col items-center gap-4">
                            <AppLoginLogoIcon className="h-auto w-full max-w-xs object-contain" />
                            <h1 className="text-xl font-medium">
                                Select a Store
                            </h1>
                            <p className="text-center text-sm text-muted-foreground">
                                Choose the store you want to access
                            </p>
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            {stores.map((store) => (
                                <Card
                                    key={store.id}
                                    className="cursor-pointer transition-colors hover:border-primary hover:bg-accent"
                                    onClick={() => selectStore(store.id)}
                                >
                                    <CardHeader>
                                        <div className="flex items-center gap-3">
                                            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                                <Building2 className="h-5 w-5 text-primary" />
                                            </div>
                                            <div>
                                                <CardTitle className="text-base">
                                                    {store.name}
                                                </CardTitle>
                                                <CardDescription>
                                                    {store.code}
                                                </CardDescription>
                                            </div>
                                        </div>
                                    </CardHeader>
                                    {store.address && (
                                        <CardContent>
                                            <p className="text-sm text-muted-foreground">
                                                {store.address}
                                            </p>
                                        </CardContent>
                                    )}
                                </Card>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
