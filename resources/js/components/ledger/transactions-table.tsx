import { Link, router } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatCents, formatDate } from '@/lib/money';
import { cn } from '@/lib/utils';
import { destroy } from '@/routes/transactions';
import type { Paginated, Transaction } from '@/types';

export default function TransactionsTable({
    transactions,
}: {
    transactions: Paginated<Transaction>;
}) {
    const { data, links, meta } = transactions;

    function remove(transaction: Transaction) {
        if (
            confirm(
                `Delete this ${formatCents(transaction.amount_cents)} ${transaction.category} transaction?`,
            )
        ) {
            router.delete(destroy.url(transaction.id), {
                preserveScroll: true,
            });
        }
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle>Transactions</CardTitle>
            </CardHeader>
            <CardContent>
                {data.length === 0 ? (
                    <p className="py-8 text-center text-sm text-muted-foreground">
                        No transactions yet. Add one to get started.
                    </p>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b text-left text-xs text-muted-foreground">
                                    <th
                                        scope="col"
                                        className="py-2 pr-4 font-medium"
                                    >
                                        Date
                                    </th>
                                    <th
                                        scope="col"
                                        className="py-2 pr-4 font-medium"
                                    >
                                        Category
                                    </th>
                                    <th
                                        scope="col"
                                        className="py-2 pr-4 font-medium"
                                    >
                                        Description
                                    </th>
                                    <th
                                        scope="col"
                                        className="py-2 pr-4 text-right font-medium"
                                    >
                                        Amount
                                    </th>
                                    <th scope="col" className="py-2">
                                        <span className="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.map((transaction) => (
                                    <tr
                                        key={transaction.id}
                                        className="border-b last:border-0"
                                    >
                                        <td className="py-2.5 pr-4 whitespace-nowrap text-muted-foreground">
                                            {formatDate(
                                                transaction.occurred_on,
                                            )}
                                        </td>
                                        <td className="py-2.5 pr-4 whitespace-nowrap">
                                            {transaction.category}
                                        </td>
                                        <td className="max-w-64 truncate py-2.5 pr-4 text-muted-foreground">
                                            {transaction.description ?? '—'}
                                        </td>
                                        <td
                                            className={cn(
                                                'py-2.5 pr-4 text-right font-medium whitespace-nowrap tabular-nums',
                                                transaction.type === 'credit' &&
                                                    'text-emerald-600 dark:text-emerald-400',
                                            )}
                                        >
                                            {transaction.type === 'credit'
                                                ? '+'
                                                : '−'}
                                            {formatCents(
                                                transaction.amount_cents,
                                            )}
                                        </td>
                                        <td className="py-1 text-right">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="size-8 text-muted-foreground hover:text-red-600"
                                                onClick={() =>
                                                    remove(transaction)
                                                }
                                                aria-label={`Delete ${transaction.category} transaction from ${formatDate(transaction.occurred_on)}`}
                                            >
                                                <Trash2 className="size-4" />
                                            </Button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {meta.last_page > 1 && (
                    <nav
                        className="mt-4 flex items-center justify-between text-sm"
                        aria-label="Pagination"
                    >
                        <span className="text-muted-foreground">
                            {meta.from}–{meta.to} of {meta.total}
                        </span>
                        <div className="flex gap-2">
                            <PageLink href={links.prev}>Previous</PageLink>
                            <PageLink href={links.next}>Next</PageLink>
                        </div>
                    </nav>
                )}
            </CardContent>
        </Card>
    );
}

function PageLink({
    href,
    children,
}: {
    href: string | null;
    children: string;
}) {
    if (href === null) {
        return (
            <Button variant="outline" size="sm" disabled>
                {children}
            </Button>
        );
    }

    return (
        <Button asChild variant="outline" size="sm">
            <Link
                href={href}
                only={['transactions']}
                preserveScroll
                preserveState
            >
                {children}
            </Link>
        </Button>
    );
}
