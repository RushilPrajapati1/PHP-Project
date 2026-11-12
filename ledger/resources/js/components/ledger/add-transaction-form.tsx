import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { dollarsToCents } from '@/lib/money';
import { store } from '@/routes/accounts/transactions';
import type { TransactionType } from '@/types';

type Props = {
    accountId: number;
    transactionTypes: TransactionType[];
    categories: string[];
};

const today = (): string => new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD in local time

export default function AddTransactionForm({
    accountId,
    transactionTypes,
    categories,
}: Props) {
    const form = useForm({
        amount: '', // dollars, as typed
        type: transactionTypes[0],
        category: '',
        description: '',
        occurred_on: today(),
    });

    // The server only speaks integer cents, so convert right before sending.
    form.transform(({ amount, ...data }) => ({
        ...data,
        amount_cents: dollarsToCents(amount),
    }));

    // Validation errors come back keyed by the server's field name (amount_cents), not ours (amount).
    const errors = form.errors as Partial<
        Record<keyof typeof form.data | 'amount_cents', string>
    >;

    function submit(event: FormEvent) {
        event.preventDefault();

        form.post(store.url(accountId), {
            preserveScroll: true,
            onSuccess: () => form.reset('amount', 'category', 'description'),
        });
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle>Add transaction</CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-2">
                        <Label>Type</Label>
                        <ToggleGroup
                            type="single"
                            variant="outline"
                            value={form.data.type}
                            onValueChange={(value) =>
                                value &&
                                form.setData('type', value as TransactionType)
                            }
                            className="w-full"
                        >
                            {transactionTypes.map((type) => (
                                <ToggleGroupItem
                                    key={type}
                                    value={type}
                                    className="flex-1 capitalize"
                                >
                                    {type === 'debit'
                                        ? 'Expense (debit)'
                                        : 'Income (credit)'}
                                </ToggleGroupItem>
                            ))}
                        </ToggleGroup>
                        <InputError message={errors.type} />
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="txn-amount">Amount ($)</Label>
                            <Input
                                id="txn-amount"
                                inputMode="decimal"
                                placeholder="0.00"
                                value={form.data.amount}
                                onChange={(e) =>
                                    form.setData('amount', e.target.value)
                                }
                                aria-invalid={Boolean(errors.amount_cents)}
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="txn-date">Date</Label>
                            <Input
                                id="txn-date"
                                type="date"
                                value={form.data.occurred_on}
                                onChange={(e) =>
                                    form.setData('occurred_on', e.target.value)
                                }
                                aria-invalid={Boolean(errors.occurred_on)}
                            />
                        </div>
                        <InputError
                            className="col-span-2 -mt-2"
                            message={errors.amount_cents ?? errors.occurred_on}
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="txn-category">Category</Label>
                        <Input
                            id="txn-category"
                            list="txn-categories"
                            placeholder="e.g. Groceries"
                            value={form.data.category}
                            onChange={(e) =>
                                form.setData('category', e.target.value)
                            }
                            aria-invalid={Boolean(errors.category)}
                        />
                        <datalist id="txn-categories">
                            {categories.map((category) => (
                                <option key={category} value={category} />
                            ))}
                        </datalist>
                        <InputError message={errors.category} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="txn-description">
                            Description{' '}
                            <span className="font-normal text-muted-foreground">
                                (optional)
                            </span>
                        </Label>
                        <Input
                            id="txn-description"
                            value={form.data.description}
                            onChange={(e) =>
                                form.setData('description', e.target.value)
                            }
                        />
                        <InputError message={errors.description} />
                    </div>

                    <Button
                        type="submit"
                        disabled={form.processing}
                        className="w-full"
                    >
                        {form.processing ? 'Adding…' : 'Add transaction'}
                    </Button>
                </form>
            </CardContent>
        </Card>
    );
}
