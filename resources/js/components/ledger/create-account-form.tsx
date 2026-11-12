import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { store } from '@/routes/accounts';
import type { AccountType } from '@/types';

export default function CreateAccountForm({
    accountTypes,
}: {
    accountTypes: AccountType[];
}) {
    // useForm tracks the values, the in-flight state and the Form Request's validation errors.
    const form = useForm({
        name: '',
        type: accountTypes[0],
    });

    function submit(event: FormEvent) {
        event.preventDefault();

        form.post(store.url(), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle>New account</CardTitle>
                <CardDescription>
                    Add a checking, savings or credit account.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-2">
                        <Label htmlFor="account-name">Name</Label>
                        <Input
                            id="account-name"
                            value={form.data.name}
                            onChange={(e) =>
                                form.setData('name', e.target.value)
                            }
                            placeholder="e.g. Everyday Checking"
                            aria-invalid={Boolean(form.errors.name)}
                        />
                        <InputError message={form.errors.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="account-type">Type</Label>
                        <Select
                            value={form.data.type}
                            onValueChange={(value) =>
                                form.setData('type', value as AccountType)
                            }
                        >
                            <SelectTrigger
                                id="account-type"
                                className="w-full capitalize"
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {accountTypes.map((type) => (
                                    <SelectItem
                                        key={type}
                                        value={type}
                                        className="capitalize"
                                    >
                                        {type}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={form.errors.type} />
                    </div>

                    <Button
                        type="submit"
                        disabled={form.processing}
                        className="w-full"
                    >
                        {form.processing ? 'Creating…' : 'Create account'}
                    </Button>
                </form>
            </CardContent>
        </Card>
    );
}
