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

        if (plan.method) {
            items.push(`Voting method: ${plan.method}`);
        } else {
            items.push('Choose a voting method');
        }

        if (plan.date) {
            items.push(`Vote on: ${plan.date}`);
        } else {
            items.push('Pick a date');
        }

        if (plan.notes.trim()) {
            items.push(`Notes: ${plan.notes.trim()}`);
        } else {
            items.push('Add any notes (ID, stamp, ride, reminders)');
        }

        return items;
    }, [plan]);

    return (
        <section className="mx-auto max-w-xl py-10">
            <h2 className="mb-2 text-2xl font-semibold">Make Your Voting Plan</h2>
            <p className="mb-6 text-sm opacity-80">A simple plan reduces stress and increases follow-through.</p>

            <div className="space-y-4">
                <select
                    className="w-full rounded border p-2"
                    value={plan.method}
                    onChange={(e) => setPlan((p) => ({ ...p, method: e.target.value }))}
                >
                    <option value="">Voting method</option>
                    <option value="Early voting">Early voting</option>
                    <option value="Mail ballot">Mail ballot</option>
                    <option value="Election day in person">Election day in person</option>
                </select>

                <input
                    type="date"
                    className="w-full rounded border p-2"
                    value={plan.date}
                    onChange={(e) => setPlan((p) => ({ ...p, date: e.target.value }))}
                />

                <textarea
                    placeholder="Notes (ID, stamp, transportation, reminders)"
                    className="w-full rounded border p-2"
                    rows={4}
                    value={plan.notes}
                    onChange={(e) => setPlan((p) => ({ ...p, notes: e.target.value }))}
                />

                <div className="rounded-xl p-4 shadow">
                    <h3 className="mb-2 font-semibold">Your Plan Snapshot</h3>
                    <ul className="ml-5 list-disc space-y-1">
                        {checklist.map((line) => (
                            <li key={line}>{line}</li>
                        ))}
                    </ul>
                </div>
            </div>
        </section>
    );
}
