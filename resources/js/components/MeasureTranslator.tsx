import { useMemo, useState } from 'react';

type SimplifiedOutput = {
    tldr: string;
    whatChanges: string[];
    whoIsAffected: string[];
    moneyQuestions: string[];
    glossary: { term: string; plain: string }[];
    note: string;
};

const GLOSSARY = [
    { term: 'shall', plain: 'must' },
    { term: 'pursuant to', plain: 'under' },
    { term: 'herein', plain: 'in this document' },
    { term: 'thereof', plain: 'of it' },
    { term: 'notwithstanding', plain: 'even if other rules say something else' },
    { term: 'appropriation', plain: 'money set aside for a specific purpose' },
    { term: 'levy', plain: 'a tax or fee charged' },
    { term: 'bond', plain: 'money borrowed now and paid back later' },
    { term: 'revenue', plain: 'money the government collects' },
    { term: 'exemption', plain: 'a rule that says someone doesn’t have to follow a requirement' },
    { term: 'penalty', plain: 'a punishment or fine for breaking the rule' },
    { term: 'enforcement', plain: 'how the rule is made real (who checks and what happens)' },
];

function normalizeWhitespace(input: string) {
    return input.replace(/\s+/g, ' ').trim();
}

function splitSentences(input: string) {
    return input
        .split(/(?<=[.!?])\s+/)
        .map((s) => s.trim())
        .filter(Boolean);
}

function applyPlainLanguageReplacements(input: string) {
    let out = input;

    for (const { term, plain } of GLOSSARY) {
        const re = new RegExp(`\\b${term}\\b`, 'gi');
        out = out.replace(re, plain);
    }

    return out;
}

function dedupe(items: string[]) {
    return Array.from(new Set(items.map((x) => x.trim()).filter(Boolean)));
}

function glossaryUsed(original: string) {
    const lower = original.toLowerCase();
    return GLOSSARY.filter(({ term }) => lower.includes(term.toLowerCase())).slice(0, 8);
}

function guessHighlights(text: string): SimplifiedOutput {
    const clean = normalizeWhitespace(text);
    const replaced = applyPlainLanguageReplacements(clean);
    const sentences = splitSentences(replaced);

    const whatChanges: string[] = [];
    const whoIsAffected: string[] = [];
    const moneyQuestions: string[] = [];

    const moneyHints = /(tax|levy|fee|bond|fund|budget|appropriat|revenue|cost|spend|million|billion|\$)/i;
    const whoHints = /(voter|resident|tenant|landlord|student|parent|business|employer|worker|patients|drivers|homeowner|property owner|citizen)/i;
    const changeHints = /(create|establish|require|ban|allow|increase|decrease|expand|limit|replace|amend|repeal|authorize|prohibit|mandate)/i;

    for (const s of sentences) {
        if (moneyHints.test(s)) moneyQuestions.push(s);
        if (whoHints.test(s)) whoIsAffected.push(s);
        if (changeHints.test(s)) whatChanges.push(s);
    }

    return {
        tldr: sentences[0] ?? 'Paste the measure text to generate a plain-language overview.',
        whatChanges: dedupe(whatChanges).slice(0, 5),
        whoIsAffected: dedupe(whoIsAffected).slice(0, 5),
        moneyQuestions: dedupe(moneyQuestions).slice(0, 5),
        glossary: glossaryUsed(clean),
        note:
            'This is an MVP plain-language draft. Always verify details with official sources.',
    };
}

export default function MeasureTranslator() {
    const [text, setText] = useState('');
    const [hasRun, setHasRun] = useState(false);

    const output = useMemo(() => {
        if (!hasRun) {
            return {
                tldr: '',
                whatChanges: [],
                whoIsAffected: [],
                moneyQuestions: [],
                glossary: [],
                note: '',
            };
        }

        return guessHighlights(text);
    }, [text, hasRun]);

    const canRun = normalizeWhitespace(text).length >= 40;

    return (
        <div className="space-y-4">
            <textarea
                className="w-full rounded-xl border p-3 text-sm focus:outline-none focus:ring-2 focus:ring-black/10"
                rows={8}
                placeholder="Paste the ballot measure summary or full text here..."
                value={text}
                onChange={(e) => setText(e.target.value)}
            />

            <div className="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    className="rounded-xl border bg-white px-4 py-2 text-sm font-semibold shadow-sm hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                    disabled={!canRun}
                    onClick={() => setHasRun(true)}
                >
                    Make it easier to read
                </button>

                {!canRun && (
                    <div className="text-xs opacity-70">
                        Tip: paste at least a few sentences (≈ 40+ characters).
                    </div>
                )}
            </div>

            {hasRun && (
                <div className="space-y-3">
                    <div className="rounded-xl border bg-white p-4">
                        <div className="text-sm font-semibold">TL;DR</div>
                        <p className="mt-2 text-sm opacity-80">{output.tldr}</p>
                    </div>

                    <div className="grid gap-3 sm:grid-cols-2">
                        <div className="rounded-xl border bg-white p-4">
                            <div className="text-sm font-semibold">What changes</div>
                            <ul className="mt-2 ml-5 list-disc space-y-1 text-sm opacity-80">
                                {(output.whatChanges.length
                                    ? output.whatChanges
                                    : ['Try pasting more text.']).map((x) => (
                                    <li key={x}>{x}</li>
                                ))}
                            </ul>
                        </div>

                        <div className="rounded-xl border bg-white p-4">
                            <div className="text-sm font-semibold">Who is affected</div>
                            <ul className="mt-2 ml-5 list-disc space-y-1 text-sm opacity-80">
                                {(output.whoIsAffected.length
                                    ? output.whoIsAffected
                                    : ['Include who the rule applies to.']).map((x) => (
                                    <li key={x}>{x}</li>
                                ))}
                            </ul>
                        </div>
                    </div>

                    <div className="rounded-xl border bg-white p-4">
                        <div className="text-sm font-semibold">Money questions</div>
                        <ul className="mt-2 ml-5 list-disc space-y-1 text-sm opacity-80">
                            {(output.moneyQuestions.length
                                ? output.moneyQuestions
                                : ['Look for cost, funding source, and who pays.']).map((x) => (
                                <li key={x}>{x}</li>
                            ))}
                        </ul>
                    </div>

                    {output.glossary.length > 0 && (
                        <div className="rounded-xl border bg-white p-4">
                            <div className="text-sm font-semibold">Glossary</div>
                            <ul className="mt-2 space-y-2 text-sm">
                                {output.glossary.map((g) => (
                                    <li key={g.term} className="rounded-lg border p-3">
                                        <div className="font-medium">{g.term}</div>
                                        <div className="mt-1 opacity-80">{g.plain}</div>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    <div className="rounded-xl border bg-white p-4 text-xs opacity-70">
                        {output.note}
                    </div>
                </div>
            )}
        </div>
    );
}


