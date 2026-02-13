import { Head, Link } from '@inertiajs/react';
import MeasureTranslator from '../components/MeasureTranslator';

export default function BallotMeasures101() {
    return (
        <>
            <Head title="Ballot Measures 101" />

            <main className="mx-auto max-w-5xl px-6 py-10">
                <header className="mb-10">
                    <div className="mb-4">
                        <Link href="/" className="text-sm opacity-70 hover:opacity-100">
                            ← Back
                        </Link>
                    </div>

                    <h1 className="text-3xl font-semibold">Ballot Measures 101</h1>
                    <p className="mt-3 max-w-2xl text-base opacity-80">
                        Ballot measures let voters decide policies directly. This page helps you understand what a measure does, what it costs, who
                        it affects, and how to evaluate it calmly.
                    </p>

                    <div className="mt-6 flex flex-wrap gap-3">
                        <a href="#anatomy" className="rounded-lg border px-3 py-2 text-sm shadow-sm hover:shadow">
                            Understand the anatomy
                        </a>
                        <a href="#evaluate" className="rounded-lg border px-3 py-2 text-sm shadow-sm hover:shadow">
                            Evaluate a measure
                        </a>
                        <a href="#translate" className="rounded-lg border px-3 py-2 text-sm shadow-sm hover:shadow">
                            Translate it
                        </a>
                    </div>
                </header>

                {/* Quick map */}
                <section className="mb-12 max-w-3xl">
                    <h2 className="mb-2 text-2xl font-semibold">How ballot measures work (quick map)</h2>
                    <p className="mb-4 text-sm opacity-80">Measures vary by state, but the decision pattern is usually the same.</p>

                    <ol className="space-y-3">
                        <li className="rounded-xl border p-4">
                            <div className="font-medium">1) A proposal is written</div>
                            <div className="mt-1 text-sm opacity-80">
                                It could be a law, funding question, constitutional amendment, or local rule change.
                            </div>
                        </li>
                        <li className="rounded-xl border p-4">
                            <div className="font-medium">2) It gets on the ballot</div>
                            <div className="mt-1 text-sm opacity-80">
                                Through the legislature, a city/county process, or a signature petition (depending on your state).
                            </div>
                        </li>
                        <li className="rounded-xl border p-4">
                            <div className="font-medium">3) Voters see a summary</div>
                            <div className="mt-1 text-sm opacity-80">
                                The ballot title/summary can be incomplete or biased—so it helps to dig one level deeper.
                            </div>
                        </li>
                        <li className="rounded-xl border p-4">
                            <div className="font-medium">4) You vote Yes/No</div>
                            <div className="mt-1 text-sm opacity-80">
                                Your “Yes” or “No” triggers a specific legal change (sometimes with funding impacts).
                            </div>
                        </li>
                        <li className="rounded-xl border p-4">
                            <div className="font-medium">5) If it passes, implementation begins</div>
                            <div className="mt-1 text-sm opacity-80">
                                Agencies write rules, budgets adjust, timelines start, and sometimes lawsuits happen.
                            </div>
                        </li>
                    </ol>
                </section>

                {/* Anatomy */}
                <section id="anatomy" className="mb-12 max-w-3xl">
                    <h2 className="mb-3 text-2xl font-semibold">What a ballot measure usually contains</h2>

                    <div className="space-y-3 text-sm">
                        <div className="rounded-xl border p-4">
                            <div className="font-medium">Title & summary</div>
                            <div className="mt-1 opacity-80">The ballot-facing description. Useful, but not enough to decide.</div>
                        </div>

                        <div className="rounded-xl border p-4">
                            <div className="font-medium">The actual change</div>
                            <div className="mt-1 opacity-80">
                                What law/text is added, removed, or amended. This is where “what it really does” lives.
                            </div>
                        </div>

                        <div className="rounded-xl border p-4">
                            <div className="font-medium">Money & enforcement</div>
                            <div className="mt-1 opacity-80">
                                Who pays, how much, for how long — and which agency/office enforces it.
                            </div>
                        </div>

                        <div className="rounded-xl border p-4">
                            <div className="font-medium">Tradeoffs</div>
                            <div className="mt-1 opacity-80">What improves, what gets worse, what might shift costs elsewhere.</div>
                        </div>
                    </div>
                </section>

                {/* Why this matters */}
                <section className="mb-12 max-w-3xl">
                    <h2 className="mb-3 text-2xl font-semibold">Why this matters</h2>

                    <p className="mb-4 text-sm opacity-80">
                        Ballot measures can change laws, budgets, and rules in ways that affect daily life — sometimes for years. But the wording is
                        often dense, legal, or emotionally loaded. The goal here is simple: help you understand what changes, who it affects, and
                        what it costs — in plain language.
                    </p>

                    <p className="text-sm opacity-80">
                        You don’t have to be an expert. You just need a reliable way to translate the text into: what it does, what it changes, and
                        what tradeoffs it introduces.
                    </p>
                </section>

                {/* Evaluate */}
                <section id="evaluate" className="mb-12 max-w-3xl">
                    <h2 className="mb-3 text-2xl font-semibold">A calm evaluation checklist</h2>
                    <p className="mb-5 text-sm opacity-80">
                        You don’t need a perfect method — you need a consistent method. Use this set of questions to reduce confusion and
                        manipulation.
                    </p>

                    <ul className="space-y-3 text-sm">
                        <li className="rounded-xl border p-4">
                            <div className="font-medium">What problem is it trying to solve?</div>
                            <div className="mt-1 opacity-80">Be specific. What changes in the real world if it passes?</div>
                        </li>
                        <li className="rounded-xl border p-4">
                            <div className="font-medium">What exactly changes?</div>
                            <div className="mt-1 opacity-80">Look for the concrete mechanism: funding, rules, penalties, timelines.</div>
                        </li>
                        <li className="rounded-xl border p-4">
                            <div className="font-medium">Who benefits and who pays?</div>
                            <div className="mt-1 opacity-80">Short-term and long-term. Direct and indirect.</div>
                        </li>
                        <li className="rounded-xl border p-4">
                            <div className="font-medium">What are credible arguments on both sides?</div>
                            <div className="mt-1 opacity-80">Try to identify the strongest version of each side’s claim.</div>
                        </li>
                        <li className="rounded-xl border p-4">
                            <div className="font-medium">What are the risks if it passes? If it fails?</div>
                            <div className="mt-1 opacity-80">“No change” is also a choice with consequences.</div>
                        </li>
                    </ul>
                </section>

                {/* Translate */}
                <section id="translate" className="mb-12 max-w-3xl">
                    <h2 className="mb-3 text-2xl font-semibold">Translate a measure into plain language</h2>
                    <p className="mb-5 text-sm opacity-80">
                        Paste a ballot measure summary or full text. We’ll pull out the core change, who it affects, and the money questions to ask —
                        in simple language.
                    </p>

                    <div className="rounded-xl border bg-white p-4">
                        <MeasureTranslator />
                    </div>
                </section>
            </main>
        </>
    );
}


