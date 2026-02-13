import BallotExplorer from '../components/BallotExplorer';
import VotingPlanBuilder from '../components/VotingPlanBuilder';

export default function Elections101() {
    return (
        <main className="mx-auto max-w-5xl px-6 py-10">
            <header className="mb-10">
                <h1 className="text-3xl font-semibold">Elections 101</h1>
                <p className="mt-3 max-w-2xl text-base opacity-80">
                    Elections can feel overwhelming. This page is a calm walkthrough of what a ballot contains, what different
                    offices actually do, and how to make voting decisions with confidence.
                </p>

                <div className="mt-6 flex flex-wrap gap-3">
                    <a href="#ballot" className="rounded-lg border px-3 py-2 text-sm shadow-sm hover:shadow">
                        Understand a ballot
                    </a>
                    <a href="#plan" className="rounded-lg border px-3 py-2 text-sm shadow-sm hover:shadow">
                        Make a voting plan
                    </a>
                </div>
            </header>

            {/* Quick map */}
            <section className="mb-12 max-w-3xl">
                <h2 className="mb-2 text-2xl font-semibold">How elections work (quick map)</h2>
                <p className="mb-4 text-sm opacity-80">
                    Most elections follow a simple pattern. Once you see the flow, ballots usually feel much less intimidating.
                </p>

                <ol className="space-y-3">
                    <li className="rounded-xl border p-4">
                        <div className="font-medium">1) Eligibility & registration</div>
                        <div className="mt-1 text-sm opacity-80">Your address determines which ballot you receive.</div>
                    </li>
                    <li className="rounded-xl border p-4">
                        <div className="font-medium">2) Your ballot is created</div>
                        <div className="mt-1 text-sm opacity-80">Ballots are local—what you see depends on your district.</div>
                    </li>
                    <li className="rounded-xl border p-4">
                        <div className="font-medium">3) You evaluate choices</div>
                        <div className="mt-1 text-sm opacity-80">
                            You’ll usually decide on a mix of people and policies. You don’t need a perfect method—just a trusted
                            process.
                        </div>
                    </li>
                    <li className="rounded-xl border p-4">
                        <div className="font-medium">4) Voting happens</div>
                        <div className="mt-1 text-sm opacity-80">Choose the method that fits your life: early, mail, or day-of.</div>
                    </li>
                    <li className="rounded-xl border p-4">
                        <div className="font-medium">5) Results & what happens next</div>
                        <div className="mt-1 text-sm opacity-80">
                            Votes are counted, results are certified, and officials or policies take effect.
                        </div>
                    </li>
                </ol>
            </section>

            {/* New: What's on a US ballot */}
            <section className="mb-12 max-w-3xl">
                <h2 className="mb-3 text-2xl font-semibold">What you’ll usually see on a U.S. ballot</h2>

                <p className="mb-5 text-sm opacity-80">
                    Ballots vary by state and city, but most U.S. voters encounter a mix of candidates, judges, and policy
                    questions. Seeing the categories ahead of time can make election day feel much less overwhelming.
                </p>

                <ul className="space-y-3 text-sm">
                    <li className="rounded-xl border p-4">
                        <div className="font-medium">Federal offices</div>
                        <div className="mt-1 opacity-80">President (every 4 years), U.S. Senate, and House of Representatives.</div>
                    </li>

                    <li className="rounded-xl border p-4">
                        <div className="font-medium">State offices</div>
                        <div className="mt-1 opacity-80">
                            Governor, state legislature, attorney general, secretary of state, and other statewide roles.
                        </div>
                    </li>

                    <li className="rounded-xl border p-4">
                        <div className="font-medium">Local offices</div>
                        <div className="mt-1 opacity-80">
                            Mayor, city council, county officials, school board, sheriff, and sometimes judges.
                        </div>
                    </li>

                    <li className="rounded-xl border p-4">
                        <div className="font-medium">Ballot measures</div>
                        <div className="mt-1 opacity-80">
                            Policy proposals, funding questions, or constitutional amendments voters decide directly.
                        </div>
                    </li>
                </ul>
            </section>

            {/* Why this matters */}
            <section className="mb-12 max-w-3xl">
                <h2 className="mb-3 text-2xl font-semibold">
                    Why this matters
                </h2>

                <p className="mb-4 text-sm opacity-80">
                    Elections don’t just choose leaders — they shape everyday realities:
                    schools, transportation, healthcare access, taxes, housing policy,
                    public safety, and how communities grow. Understanding what appears
                    on your ballot helps you make decisions intentionally rather than
                    reacting to headlines, ads, or social media noise.
                </p>

                <p className="text-sm opacity-80">
                    The goal isn’t perfect certainty. It’s confidence that you’ve looked
                    at reliable information, considered tradeoffs, and made choices that
                    reflect your priorities.
                </p>
            </section>


            <div id="ballot" className="mt-12 max-w-3xl">
                <h2 className="mb-3 text-2xl font-semibold">Explore a sample ballot</h2>
                <p className="mb-6 max-w-2xl text-sm opacity-80">
                    This is a simplified example to help you recognize common sections. Your actual ballot depends on your address.
                </p>
            <BallotExplorer />
            </div>

            <div id="plan" className="mt-12 max-w-3xl">
                <h2 className="mb-3 text-2xl font-semibold">Make a voting plan</h2>
                <p className="mb-6 max-w-2xl text-sm opacity-80">
                    Decide how you’ll vote (mail, early, day-of), what you need, and what you’ll research before you go.
                </p>
            <VotingPlanBuilder />
            </div>

        </main>
    );
}


