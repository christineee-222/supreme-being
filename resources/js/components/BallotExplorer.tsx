import { useState } from 'react';

type BallotItem = {
    title: string;
    description: string;
    details: string[];
};

const ballotItems: BallotItem[] = [
    {
        title: 'Executive Office',
        description: 'Leads implementation of policy and administration.',
        details: ['Sets priorities and budgets', 'Appoints officials', 'Signs or vetoes legislation'],
    },
    {
        title: 'Legislative Office',
        description: 'Creates laws and oversees government activity.',
        details: ['Drafts legislation', 'Votes on budgets', 'Represents constituents'],
    },
    {
        title: 'Judicial Role',
        description: 'Interprets law and resolves disputes.',
        details: ['Decides legal cases', 'Shapes legal precedent', 'Protects constitutional rights'],
    },
];

export default function BallotExplorer() {
    const [active, setActive] = useState<number>(0);
    const item = ballotItems[active];

    return (
        <div className="grid gap-6 md:grid-cols-2">
            <div className="space-y-3">
                {ballotItems.map((b, i) => (
                    <button
                        key={b.title}
                        type="button"
                        onClick={() => setActive(i)}
                        className={[
                            'block w-full rounded-xl border bg-white p-4 text-left transition',
                            i === active ? 'bg-gray-50' : 'hover:bg-gray-50',
                        ].join(' ')}
                    >
                        <div className="font-medium">{b.title}</div>
                        <div className="mt-1 text-sm opacity-80">{b.description}</div>
                    </button>
                ))}
            </div>

            <div className="rounded-xl border bg-white p-5">
                <h3 className="mb-2 text-xl font-semibold">{item.title}</h3>
                <p className="mb-4 opacity-80">{item.description}</p>

                <ul className="ml-5 list-disc space-y-1">
                    {item.details.map((d) => (
                        <li key={d}>{d}</li>
                    ))}
                </ul>
            </div>
        </div>
    );
}







