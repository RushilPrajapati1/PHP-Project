// Shapes produced by app/Http/Resources/*Resource.php

export type AccountType = 'checking' | 'savings' | 'credit';
export type TransactionType = 'debit' | 'credit';

export type Account = {
    id: number;
    name: string;
    type: AccountType;
    balance_cents: number;
    balance: number;
    transactions_count?: number;
    created_at: string | null;
};

export type Transaction = {
    id: number;
    account_id: number;
    type: TransactionType;
    amount_cents: number;
    amount: number;
    category: string;
    description: string | null;
    occurred_on: string; // YYYY-MM-DD
    created_at: string | null;
};

export type CategoryTotal = {
    category: string;
    type: TransactionType;
    total_cents: number;
    total: number;
    count: number;
};

export type MonthlySummary = {
    month: string; // YYYY-MM
    income_cents: number;
    spending_cents: number;
    net_cents: number;
    income: number;
    spending: number;
    net: number;
    categories: CategoryTotal[];
};

/** Laravel's paginated resource collection ({ data, links, meta }). */
export type Paginated<T> = {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
        per_page: number;
    };
};
