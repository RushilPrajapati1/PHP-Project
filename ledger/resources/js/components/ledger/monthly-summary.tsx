import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatCents } from '@/lib/money';
import { cn } from '@/lib/utils';
import { show } from '@/routes/accounts';
import type { CategoryTotal, MonthlySummary as Summary } from '@/types';

type Props = {
    accountId: number;
    summary: Summary;
    months: { previous: string; next: string; label: string };
};

export default function MonthlySummary({ accountId, summary, months }: Props) {
    const spending = summary.categories.filter((c) => c.type === 'debit');
    const income = summary.categories.filter((c) => c.type === 'credit');

    // Only re-fetch the summary props when switching month; the table stays put.
    const monthLink = (month: string) => ({
        href: show(accountId, { mergeQuery: { month } }),
        only: ['summary', 'months'],
        preserveScroll: true,
        preserveState: true,
    });

    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0">
                <CardTitle>Monthly totals</CardTitle>
                <div className="flex items-center gap-1">
                    <Button
                        asChild
                        variant="ghost"
                        size="icon"
                        className="size-8"
                    >
                        <Link
                            {...monthLink(months.previous)}
                            aria-label="Previous month"
                        >
                            <ChevronLeft className="size-4" />
                        </Link>
                    </Button>
                    <span className="min-w-28 text-center text-sm font-medium">
                        {months.label}
                    </span>
                    <Button
                        asChild
                        variant="ghost"
                        size="icon"
                        className="size-8"
                    >
                        <Link
                            {...monthLink(months.next)}
                            aria-label="Next month"
                        >
                            <ChevronRight className="size-4" />
                        </Link>
                    </Button>
                </div>
            </CardHeader>
            <CardContent className="space-y-6">
                <dl className="grid grid-cols-3 gap-2 text-sm">
                    <Stat label="Income" cents={summary.income_cents} />
                    <Stat label="Spending" cents={summary.spending_cents} />
                    <Stat label="Net" cents={summary.net_cents} signed />
                </dl>

                {summary.categories.length === 0 ? (
                    <p className="py-6 text-center text-sm text-muted-foreground">
                        No transactions this month.
                    </p>
                ) : (
                    <>
                        <CategoryList
                            title="Spending by category"
                            rows={spending}
                            totalCents={summary.spending_cents}
                        />
                        <CategoryList
                            title="Income by category"
                            rows={income}
                            totalCents={summary.income_cents}
                        />
                    </>
                )}
            </CardContent>
        </Card>
    );
}

function Stat({
    label,
    cents,
    signed = false,
}: {
    label: string;
    cents: number;
    signed?: boolean;
}) {
    return (
        <div className="min-w-0 rounded-lg border p-2.5">
            <dt className="text-xs text-muted-foreground">{label}</dt>
            <dd
                className={cn(
                    'mt-1 font-semibold tabular-nums',
                    signed && cents < 0 && 'text-red-600 dark:text-red-400',
                    signed &&
                        cents > 0 &&
                        'text-emerald-600 dark:text-emerald-400',
                )}
            >
                {signed && cents > 0 ? '+' : ''}
                {formatCents(cents)}
            </dd>
        </div>
    );
}

function CategoryList({
    title,
    rows,
    totalCents,
}: {
    title: string;
    rows: CategoryTotal[];
    totalCents: number;
}) {
    if (rows.length === 0) {
        return null;
    }

    return (
        <section>
            <h3 className="mb-2 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                {title}
            </h3>
            <ul className="space-y-2.5">
                {rows.map((row) => {
                    const share =
                        totalCents > 0
                            ? (row.total_cents / totalCents) * 100
                            : 0;

                    return (
                        <li
                            key={`${row.type}-${row.category}`}
                            className="text-sm"
                        >
                            <div className="flex items-baseline justify-between gap-3">
                                <span>
                                    {row.category}{' '}
                                    <span className="text-xs text-muted-foreground">
                                        × {row.count}
                                    </span>
                                </span>
                                <span className="tabular-nums">
                                    {formatCents(row.total_cents)}{' '}
                                    <span className="text-xs text-muted-foreground">
                                        {Math.round(share)}%
                                    </span>
                                </span>
                            </div>
                            <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-muted">
                                <div
                                    className={cn(
                                        'h-full rounded-full',
                                        row.type === 'debit'
                                            ? 'bg-foreground/70'
                                            : 'bg-emerald-500',
                                    )}
                                    style={{ width: `${share}%` }}
                                />
                            </div>
                        </li>
                    );
                })}
            </ul>
        </section>
    );
}
