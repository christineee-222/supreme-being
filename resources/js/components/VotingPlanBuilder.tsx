import { useMemo, useState } from 'react';

type VotingPlan = {
    method: string;
    date: string;
    notes: string;
};

export default function VotingPlanBuilder() {
    const [plan, setPlan] = useState<VotingPlan>({
        method: '',
        date: '',
        notes: '',
    });

    const checklist = useMemo(() => {
        const items: string[] = [];

        items.push(plan.method ? `Voting method: ${plan.method}` : 'Choose a voting method');
        items.push(plan.date ? `Vote on: ${plan.date}` : 'Pick a date');
        items.push(plan.notes.trim() ? `Notes: ${plan.notes.trim()}` : 'Add any notes (ID, stamp, ride, reminders)');

        return items;
    }, [plan]);

    return (
        <div className="space-y-4">
            {/* Accessible (screen-reader only) label */}
            <label htmlFor="voting-method" className="sr-only">
                Voting method
            </label>

            <div className="relative">
                <select
                    id="voting-method"
                    className="w-full rounded-xl border p-2 pr-12 appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-black/10"
                    value={plan.method}
                    onChange={(e) => setPlan((p) => ({ ...p, method: e.target.value }))}
                >
                    <option value="">Voting method</option>
                    <option value="Early voting">Early voting</option>
                    <option value="Mail ballot">Mail ballot</option>
                    <option value="Election day in person">Election day in person</option>
                </select>

                <svg
                    aria-hidden="true"
                    className="pointer-events-none absolute inset-y-0 right-4 my-auto h-5 w-5 opacity-60"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fillRule="evenodd"
                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 011.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                        clipRule="evenodd"
                    />
                </svg>
            </div>

            <label htmlFor="voting-date" className="sr-only">
                Voting date
            </label>
            <input
                id="voting-date"
                type="date"
                className="w-full rounded-xl border p-2 focus:outline-none focus:ring-2 focus:ring-black/10"
                value={plan.date}
                onChange={(e) => setPlan((p) => ({ ...p, date: e.target.value }))}
            />

            <label htmlFor="voting-notes" className="sr-only">
                Notes
            </label>
            <textarea
                id="voting-notes"
                placeholder="Notes (ID, stamp, transportation, reminders)"
                className="w-full rounded-xl border p-2 focus:outline-none focus:ring-2 focus:ring-black/10"
                rows={4}
                value={plan.notes}
                onChange={(e) => setPlan((p) => ({ ...p, notes: e.target.value }))}
            />

            <div className="rounded-xl border p-4 shadow">
                <h3 className="mb-2 font-semibold">Your Plan Snapshot</h3>
                <ul className="ml-5 list-disc space-y-1">
                    {checklist.map((line) => (
                        <li key={line}>{line}</li>
                    ))}
                </ul>
            </div>
        </div>
    );
}






