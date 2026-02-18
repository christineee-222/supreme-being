import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login } from '@/routes';
import type { SharedData } from '@/types';

type Topic = {
  title: string;
  description: string;
  href: string;
};

export default function Welcome() {
  const { auth } = usePage<SharedData>().props;

  const topics: Topic[] = [
    { title: 'Elections 101', description: 'How local/state/federal roles actually work.', href: '/topics/elections-101' },
    { title: 'Ballot measures', description: 'What they change, who benefits, who pays.', href: '/topics/ballot-measures' },
    { title: 'Healthcare', description: 'Compare proposals and real tradeoffs.', href: '/topics/healthcare' },
    { title: 'Taxes & spending', description: 'Where money comes from and where it goes.', href: '/topics/taxes' },
    { title: 'Housing', description: 'Zoning, supply, affordability—without the shouting.', href: '/topics/housing' },
    { title: 'Climate & energy', description: 'Policies, timelines, costs, outcomes.', href: '/topics/climate' },
  ];

  return (
    <>
      <Head title="Welcome">
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
      </Head>

      <div className="min-h-screen bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
        {/* Top bar */}
        <header className="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-6">
          <div className="flex items-center gap-3">
            <div className="h-9 w-9 rounded-xl border border-black/10 bg-white shadow-sm dark:border-white/10 dark:bg-[#161615]" />
            <div className="leading-tight">
              <div className="text-sm font-semibold">Assembly Required</div>
              <div className="text-xs text-black/60 dark:text-white/60">A civic commons for voice + action</div>
            </div>
          </div>

          <nav className="flex items-center gap-3">
            {auth.user ? (
              <Link
                href={dashboard()}
                className="rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-medium shadow-sm hover:bg-black/5 dark:border-white/10 dark:bg-[#161615] dark:hover:bg-white/5"
              >
                Dashboard
              </Link>
            ) : (
              <Link
                href={login()}
                className="rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-medium shadow-sm hover:bg-black/5 dark:border-white/10 dark:bg-[#161615] dark:hover:bg-white/5"
              >
                Log in
              </Link>
            )}
          </nav>
        </header>

        {/* Hero */}
        <main className="mx-auto w-full max-w-6xl px-6 pb-16">
          <section className="grid gap-8 rounded-3xl border border-black/10 bg-white p-8 shadow-sm dark:border-white/10 dark:bg-[#161615] lg:grid-cols-2 lg:p-12">
            <div>
              <h1 className="text-3xl font-semibold tracking-tight lg:text-5xl">
                Turn civic noise into{' '}
                <span className="underline decoration-black/20 dark:decoration-white/20">clear choices</span> — then build
                the future together.
              </h1>
              <p className="mt-4 max-w-xl text-base leading-7 text-black/70 dark:text-white/70">
                Learn with sources and tradeoffs, share your voice in community polls, talk it through in forums, organize through events, and help
                grow a solidarity economy that sustains the work. If we want a revolution, some assembly is required.
              </p>

              <div className="mt-6 flex flex-col gap-3 sm:flex-row">
                <Link
                  href="/polls"
                  className="inline-flex items-center justify-center rounded-xl bg-[#1b1b18] px-5 py-2.5 text-sm font-semibold text-white hover:bg-black dark:bg-[#eeeeec] dark:text-[#1b1b18] dark:hover:bg-white"
                >
                  Join the conversation
                </Link>

                <Link
                  href="/events"
                  className="inline-flex items-center justify-center rounded-xl border border-black/10 bg-white px-5 py-2.5 text-sm font-semibold hover:bg-black/5 dark:border-white/10 dark:bg-[#161615] dark:hover:bg-white/5"
                >
                  Find an event
                </Link>

                <a
                  href="#explore"
                  className="inline-flex items-center justify-center rounded-xl border border-black/10 bg-white px-5 py-2.5 text-sm font-semibold hover:bg-black/5 dark:border-white/10 dark:bg-[#161615] dark:hover:bg-white/5"
                >
                  Explore topics
                </a>
              </div>

              <div className="mt-3">
                <Link
                  href="/topics/elections-101"
                  className="inline-flex items-center justify-center rounded-xl border border-black/10 bg-white px-5 py-2.5 text-sm font-semibold hover:bg-black/5 dark:border-white/10 dark:bg-[#161615] dark:hover:bg-white/5"
                >
                  Learn how your government works
                </Link>
              </div>

              <div className="mt-6 grid grid-cols-3 gap-3 text-xs text-black/60 dark:text-white/60">
                <div className="rounded-2xl border border-black/10 bg-[#FDFDFC] p-3 dark:border-white/10 dark:bg-[#0f0f0f]">
                  <div className="text-sm font-semibold text-black dark:text-white">Voice</div>
                  <div className="mt-1">Polls that shape the platform.</div>
                </div>
                <div className="rounded-2xl border border-black/10 bg-[#FDFDFC] p-3 dark:border-white/10 dark:bg-[#0f0f0f]">
                  <div className="text-sm font-semibold text-black dark:text-white">Action</div>
                  <div className="mt-1">Events + RSVP totals.</div>
                </div>
                <div className="rounded-2xl border border-black/10 bg-[#FDFDFC] p-3 dark:border-white/10 dark:bg-[#0f0f0f]">
                  <div className="text-sm font-semibold text-black dark:text-white">Clarity</div>
                  <div className="mt-1">Sources and tradeoffs, calmly.</div>
                </div>
              </div>
            </div>

            {/* Right panel */}
            <div className="rounded-3xl border border-black/10 bg-gradient-to-b from-[#fff7ed] to-white p-6 dark:border-white/10 dark:from-[#1b1208] dark:to-[#161615]">
              <div className="text-sm font-semibold">How it works (the loop)</div>
              <p className="mt-2 text-sm text-black/70 dark:text-white/70">
                Learn → vote/voice → gather → support → repeat. The point is to turn attention into participation.
              </p>

              <div className="mt-5 space-y-3">
                {[
                  { k: '1', t: 'Learn with clarity', d: 'Start from a calm overview with sources and tradeoffs.' },
                  { k: '2', t: 'Share your voice', d: 'Vote in polls, propose ideas, and give feedback.' },
                  { k: '3', t: 'Organize together', d: 'RSVP to events and build momentum in public.' },
                  { k: '4', t: 'Sustain the work', d: 'Mutual support that keeps community work viable.' },
                ].map((s) => (
                  <div
                    key={s.k}
                    className="flex gap-3 rounded-2xl border border-black/10 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-[#0f0f0f]"
                  >
                    <div className="flex h-8 w-8 items-center justify-center rounded-xl border border-black/10 bg-[#FDFDFC] text-sm font-semibold dark:border-white/10 dark:bg-[#161615]">
                      {s.k}
                    </div>
                    <div>
                      <div className="text-sm font-semibold">{s.t}</div>
                      <div className="mt-1 text-sm text-black/60 dark:text-white/60">{s.d}</div>
                    </div>
                  </div>
                ))}
              </div>

              <div className="mt-6 rounded-2xl border border-black/10 bg-white p-4 text-xs text-black/60 dark:border-white/10 dark:bg-[#0f0f0f] dark:text-white/60">
                Building in public. If you can,{' '}
                <Link className="underline underline-offset-4 hover:text-black dark:hover:text-white" href="/donate">
                  donate to keep the project going
                </Link>
                — but the goal is bigger: a platform that helps communities support each other.
              </div>
            </div>
          </section>

          {/* Participate */}
          <section className="mt-10">
            <div className="flex items-end justify-between gap-6">
              <div>
                <h2 className="text-2xl font-semibold tracking-tight">Participate</h2>
                <p className="mt-2 text-sm text-black/70 dark:text-white/70">
                  This isn’t a feed. It’s a civic commons: voice, action, and shared support—built with the people who
                  use it.
                </p>
              </div>
              <div className="hidden text-xs text-black/60 dark:text-white/60 lg:block">Voice → Action → Support → Impact</div>
            </div>

            <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
              {[
                {
                  title: 'Polls',
                  desc: 'Vote on features, propose ideas, run fun polls, and shape what we build next.',
                  href: '/polls',
                  cta: 'Browse polls →',
                },
                {
                  title: 'Forums',
                  desc: 'Go deeper than a vote: ask questions, share context, and discuss ideas calmly.',
                  href: '/forums',
                  cta: 'Enter forums →',
                },

                {
                  title: 'Events',
                  desc: 'Find gatherings, RSVP, and watch participation totals grow in real time.',
                  href: '/events',
                  cta: 'Explore events →',
                },
                {
                  title: 'Solidarity economy',
                  desc: 'Mutual support and transparent funding that keeps community work sustainable.',
                  href: '/support',
                  cta: 'How support works →',
                },
                {
                  title: 'Portraits',
                  desc: 'Help establish accountability and trust with transparent profiles and activity histories.',
                  href: '/portraits',
                  cta: 'Browse Officials →',
                },
              ].map((card) => (
                <Link
                  key={card.title}
                  href={card.href}
                  className="group rounded-3xl border border-black/10 bg-white p-6 shadow-sm hover:bg-black/5 dark:border-white/10 dark:bg-[#161615] dark:hover:bg-white/5"
                >
                  <div className="text-base font-semibold">{card.title}</div>
                  <p className="mt-2 text-sm text-black/70 dark:text-white/70">{card.desc}</p>
                  <div className="mt-4 text-sm font-semibold text-black/60 group-hover:text-black dark:text-white/60 dark:group-hover:text-white">
                    {card.cta}
                  </div>
                </Link>
              ))}
            </div>

            <div className="mt-6 rounded-2xl border border-black/10 bg-white p-4 text-xs text-black/60 dark:border-white/10 dark:bg-[#161615] dark:text-white/60">
              Want the “build together” vibe? Start with a poll, join an event, or propose an idea. This homepage is a map,
              not a pitch.
            </div>
          </section>

          {/* Explore topics */}
          <section id="explore" className="mt-10">
            <div className="flex items-end justify-between gap-6">
              <div>
                <h2 className="text-2xl font-semibold tracking-tight">Explore topics</h2>
                <p className="mt-2 text-sm text-black/70 dark:text-white/70">
                  Clarity-first explainers with sources, tradeoffs, and a calm path to confident decisions.
                </p>
              </div>
              <div className="hidden text-xs text-black/60 dark:text-white/60 lg:block">Tip: build 1 topic end-to-end first.</div>
            </div>

            <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              {topics.map((t) => (
                <a
                  key={t.title}
                  href={t.href}
                  className="group rounded-3xl border border-black/10 bg-white p-6 shadow-sm hover:bg-black/5 dark:border-white/10 dark:bg-[#161615] dark:hover:bg-white/5"
                >
                  <div className="text-base font-semibold">{t.title}</div>
                  <p className="mt-2 text-sm text-black/70 dark:text-white/70">{t.description}</p>
                  <div className="mt-4 text-sm font-semibold text-black/60 group-hover:text-black dark:text-white/60 dark:group-hover:text-white">
                    Open →
                  </div>
                </a>
              ))}
            </div>
          </section>

          {/* Footer */}
          <footer className="mt-12 flex flex-col gap-3 border-t border-black/10 pt-8 text-xs text-black/60 dark:border-white/10 dark:text-white/60">
            <div className="flex flex-wrap items-center gap-3">
              <span>© {new Date().getFullYear()} Assembly Required</span>
              <span className="opacity-40">•</span>
              <a className="underline underline-offset-4 hover:text-black dark:hover:text-white" href="/about">
                About
              </a>
              <a className="underline underline-offset-4 hover:text-black dark:hover:text-white" href="/principles">
                Principles
              </a>
              <a className="underline underline-offset-4 hover:text-black dark:hover:text-white" href="/changelog">
                Changelog
              </a>
              <a className="underline underline-offset-4 hover:text-black dark:hover:text-white" href="/forums">
                Forums
              </a>
              <a className="underline underline-offset-4 hover:text-black dark:hover:text-white" href="/support">
                Support
              </a>
            </div>
            <div className="max-w-3xl">
              MVP goal: turn civic curiosity into participation—learn with clarity, share your voice, organize through
              events, and sustain the work together.
            </div>
          </footer>
        </main>
      </div>
    </>
  );
}




