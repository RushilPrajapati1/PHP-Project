import { Head, Link } from '@inertiajs/react';
import CreateAccountForm from '@/components/ledger/create-account-form';
import { Badge } from '@/components/ui/badge';
import { formatCents } from '@/lib/money';
import { cn } from '@/lib/utils';
import { index, show } from '@/routes/accounts';
import type { Account, AccountType } from '@/types';

type Props = {
    accounts: Account[];
    accountTypes: AccountType[];
};

export default function AccountsIndex({ accounts, accountTypes }: Props) {
    const totalCents = accounts.reduce(
        (sum, account) => sum + account.balance_cents,
        0,
    );

    return (
        <>
            <Head title="Accounts" />

            <div className="flex flex-1 flex-col gap-6 p-4">
                <header className="flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h1 className="text-xl font-semibold tracking-tight">
                            Accounts
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {accounts.length}{' '}
                            {accounts.length === 1 ? 'account' : 'accounts'} ·
                            net balance{' '}
                            <span className="font-medium text-foreground tabular-nums">
                                {formatCents(totalCents)}
                            </span>
                        </p>
                    </div>
                </header>

                <div className="grid items-start gap-6 lg:grid-cols-[1fr_20rem]">
                    <section aria-label="Your accounts">
                        {accounts.length === 0 ? (
                            <div className="rounded-xl border border-dashed p-10 text-center text-sm text-muted-foreground">
                                No accounts yet. Create your first one to start
                                tracking.
                            </div>
                        ) : (
                            <ul className="grid gap-4 sm:grid-cols-2">
                                {accounts.map((account) => (
                                    <li key={account.id}>
                                        <Link
                                            href={show(account.id)}
                                            prefetch
                                            className="block rounded-xl border bg-card p-5 transition-colors hover:bg-accent/50 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                        >
                                            <div className="flex items-start justify-between gap-3">
                                                <span className="font-medium">
                                                    {account.name}
                                                </span>
                                                <Badge
                                                    variant="secondary"
                                                    className="capitalize"
                                                >
                                                    {account.type}
                                                </Badge>
                                            </div>
                                            <p
                                                className={cn(
                                                    'mt-4 text-2xl font-semibold tabular-nums',
                                                    account.balance_cents < 0 &&
                                                        'text-red-600 dark:text-red-400',
                                                )}
                                            >
                                                {formatCents(
                                                    account.balance_cents,
                                                )}
                                            </p>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                {account.transactions_count ??
                                                    0}{' '}
                                                transactions
                                            </p>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>

                    <CreateAccountForm accountTypes={accountTypes} />
                </div>
            </div>
        </>
    );
}

AccountsIndex.layout = {
    breadcrumbs: [{ title: 'Accounts', href: index() }],
};
