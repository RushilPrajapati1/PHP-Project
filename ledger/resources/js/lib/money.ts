const usd = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
});

/** Format integer cents as dollars, e.g. 123456 -> "$1,234.56". */
export function formatCents(cents: number): string {
    return usd.format(cents / 100);
}

/** Parse a dollar string typed by the user into integer cents; null if it isn't a number. */
export function dollarsToCents(input: string): number | null {
    const value = Number(input.replace(/[$,\s]/g, ''));

    return input.trim() === '' || Number.isNaN(value)
        ? null
        : Math.round(value * 100);
}

/** "2026-10-05" -> "Oct 5, 2026" without timezone shifting. */
export function formatDate(isoDate: string): string {
    const [year, month, day] = isoDate.split('-').map(Number);

    return new Date(year, month - 1, day).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
