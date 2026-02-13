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
              <div className="text-sm font-semibold">AssemblyRequired</div>
              <div className="text-xs text-black/60 dark:text-white/60">Clarity-first civic tools</div>
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
                Turn political noise into <span className="underline decoration-black/20 dark:decoration-white/20">clear choices</span>.
              </h1>
              <p className="mt-4 max-w-xl text-base leading-7 text-black/70 dark:text-white/70">
                Explore issues with sources, structured tradeoffs, and calm discussion. Built for curiosity and practical decision-making.
              </p>

              <div className="mt-6 flex flex-col gap-3 sm:flex-row">
                <a
                  href="#explore"
                  className="inline-flex items-center justify-center rounded-xl bg-[#1b1b18] px-5 py-2.5 text-sm font-semibold text-white hover:bg-black dark:bg-[#eeeeec] dark:text-[#1b1b18] dark:hover:bg-white"
                >
                  Explore topics
                </a>

                <a
                  href="#how"
                  className="inline-flex items-center justify-center rounded-xl border border-black/10 bg-white px-5 py-2.5 text-sm font-semibold hover:bg-black/5 dark:border-white/10 dark:bg-[#161615] dark:hover:bg-white/5"
                >
                  How it works
                </a>
              </div>

              <div className="mt-3">
                <Link
                  href="/topics/elections-101"
                  className="inline-flex items-center justify-center rounded-xl border border-black/10 bg-white px-5 py-2.5 text-sm font-semibold hover:bg-black/5 dark:border-white/10 dark:bg-[#161615] dark:hover:bg-white/5"
                >
                  Start with Elections 101
                </Link>
              </div>

              <div className="mt-6 grid grid-cols-3 gap-3 text-xs text-black/60 dark:text-white/60">
                <div className="rounded-2xl border border-black/10 bg-[#FDFDFC] p-3 dark:border-white/10 dark:bg-[#0f0f0f]">
                  <div className="text-sm font-semibold text-black dark:text-white">Sources</div>
                  <div className="mt-1">Cited, linkable, readable.</div>
                </div>
                <div className="rounded-2xl border border-black/10 bg-[#FDFDFC] p-3 dark:border-white/10 dark:bg-[#0f0f0f]">
                  <div className="text-sm font-semibold text-black dark:text-white">Tradeoffs</div>
                  <div className="mt-1">Pros/cons, costs, impacts.</div>
                </div>
                <div className="rounded-2xl border border-black/10 bg-[#FDFDFC] p-3 dark:border-white/10 dark:bg-[#0f0f0f]">
                  <div className="text-sm font-semibold text-black dark:text-white">Calm UX</div>
                  <div className="mt-1">Designed for clarity.</div>
                </div>
              </div>
            </div>

            {/* Right panel */}
            <div className="rounded-3xl border border-black/10 bg-gradient-to-b from-[#fff7ed] to-white p-6 dark:border-white/10 dark:from-[#1b1208] dark:to-[#161615]">
              <div className="text-sm font-semibold">Try a starter flow</div>
              <p className="mt-2 text-sm text-black/70 dark:text-white/70">
                Pick an issue → see the main claims → inspect the sources → decide what you believe.
              </p>

              <div className="mt-5 space-y-3">
                {[
                  { k: '1', t: 'Choose a topic', d: 'Start from a neutral overview.' },
                  { k: '2', t: 'Compare proposals', d: 'See tradeoffs side-by-side.' },
                  { k: '3', t: 'Save your notes', d: 'Build a personal decision trail.' },
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
                This is the MVP focus: <span className="font-semibold text-black dark:text-white">great UX</span> and
                <span className="font-semibold text-black dark:text-white"> trustworthy structure</span>, before mobile.
              </div>
            </div>
          </section>

          {/* How it works */}
          <section id="how" className="mt-10 grid gap-6 lg:grid-cols-3">
            {[
              {
                title: 'Curiosity-friendly',
                desc: 'Start broad, then drill down. No jargon walls. No shame loops.',
              },
              {
                title: 'Empowerment-focused',
                desc: 'The goal is confident action—vote, talk, volunteer, donate, or pause.',
              },
              {
                title: 'Designed for reliability',
                desc: 'Predictable UI patterns. Sources always visible. Nothing “disappears.”',
              },
            ].map((c) => (
              <div
                key={c.title}
                className="rounded-3xl border border-black/10 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#161615]"
              >
                <div className="text-lg font-semibold">{c.title}</div>
                <p className="mt-2 text-sm leading-6 text-black/70 dark:text-white/70">{c.desc}</p>
              </div>
            ))}
          </section>

          {/* Explore topics */}
          <section id="explore" className="mt-10">
            <div className="flex items-end justify-between gap-6">
              <div>
                <h2 className="text-2xl font-semibold tracking-tight">Explore topics</h2>
                <p className="mt-2 text-sm text-black/70 dark:text-white/70">
                  These links can be “coming soon” for MVP. The homepage can still feel complete.
                </p>
              </div>
              <div className="hidden text-xs text-black/60 dark:text-white/60 lg:block">
                Tip: we’ll implement 1 topic end-to-end first.
              </div>
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
              <span>© {new Date().getFullYear()} AssemblyRequired</span>
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
            </div>
            <div className="max-w-3xl">
              MVP goal: make it easy to understand an issue, see sources, compare tradeoffs, and keep your own notes—calmly.
            </div>
          </footer>
        </main>
      </div>
    </>
  );
}



