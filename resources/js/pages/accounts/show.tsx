import { Head, router, setLayoutProps } from '@inertiajs/react';
import AddTransactionForm from '@/components/ledger/add-transaction-form';
import MonthlySummary from '@/components/ledger/monthly-summary';
import TransactionsTable from '@/components/ledger/transactions-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatCents } from '@/lib/money';
import { cn } from '@/lib/utils';
import { destroy, index, show } from '@/routes/accounts';
import type {
    Account,
    MonthlySummary as Summary,
    Paginated,
    Transaction,
    TransactionType,
} from '@/types';

type Props = {
    account: Account;
    transactions: Paginated<Transaction>;
    summary: Summary;
    months: { previous: string; next: string; label: string };
    transactionTypes: TransactionType[];
    categories: string[];
};

export default function AccountShow({
    account,
    transactions,
    summary,
    months,
    transactionTypes,
    categories,
}: Props) {
    setLayoutProps({
        breadcrumbs: [
            { title: 'Accounts', href: index() },
            { title: account.name, href: show(account.id) },
        ],
    });

    function deleteAccount() {
        if (
            confirm(
                `Delete “${account.name}” and all of its transactions? This cannot be undone.`,
            )
        ) {
            router.delete(destroy.url(account.id));
        }
    }

    return (
        <>
            <Head title={account.name} />

            <div className="flex flex-1 flex-col gap-6 p-4">
                <header className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-xl font-semibold tracking-tight">
                                {account.name}
                            </h1>
                            <Badge variant="secondary" className="capitalize">
                                {account.type}
                            </Badge>
                        </div>
                        <p
                            className={cn(
                                'mt-1 text-3xl font-semibold tabular-nums',
                                account.balance_cents < 0 &&
                                    'text-red-600 dark:text-red-400',
                            )}
                        >
                            {formatCents(account.balance_cents)}
                        </p>
                        <p className="text-xs text-muted-foreground">
                            Current balance
                        </p>
                    </div>
                    <Button variant="outline" size="sm" onClick={deleteAccount}>
                        Delete account
                    </Button>
                </header>

                <div className="grid items-start gap-6 lg:grid-cols-[1fr_22rem]">
                    <div className="order-2 min-w-0 lg:order-1">
                        <TransactionsTable transactions={transactions} />
                    </div>
                    <div className="order-1 space-y-6 lg:order-2">
                        <AddTransactionForm
                            accountId={account.id}
                            transactionTypes={transactionTypes}
                            categories={categories}
                        />
                        <MonthlySummary
                            accountId={account.id}
                            summary={summary}
                            months={months}
                        />
                    </div>
                </div>
            </div>
        </>
    );
}
