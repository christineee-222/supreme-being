// resources/js/pages/Events/Show.tsx
import React from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import type { SharedData } from '@/types';

interface User {
  id: string;
  name: string;
}

interface EventRsvp {
  id: string;
  status: 'going' | 'interested' | 'not_going';
  user_id: string;
  user?: User;
  event_id?: string;
}

interface Event {
  id: string; // UUID string from backend
  slug: string;
  title: string;
  description: string | null;
  status: string | null;
  starts_at: string | null;
  rsvps_count?: number;
  rsvps?: EventRsvp[];
}

interface Props {
  event: Event;
  userRsvp: EventRsvp | null;
}

function formatDate(iso: string | null) {
  if (!iso) return 'TBD';

  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return iso;

  return d.toLocaleString(undefined, {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  });
}

function statusPill(status: string | null) {
  const s = (status ?? 'scheduled').toLowerCase();

  const base =
    'rounded-xl border border-black/10 bg-white px-2 py-1 text-xs font-semibold text-black/60 dark:border-white/10 dark:bg-[#161615] dark:text-white/60';

  switch (s) {
    case 'cancelled':
    case 'canceled':
      return <span className={base}>Cancelled</span>;
    case 'draft':
      return <span className={base}>Draft</span>;
    case 'scheduled':
    default:
      return <span className={base}>Scheduled</span>;
  }
}

export default function Show({ event, userRsvp }: Props) {
  const { auth } = usePage<SharedData>().props;
  const [processing, setProcessing] = React.useState(false);

  const currentStatus = userRsvp?.status ?? null;

  const startsAtMs = event.starts_at ? new Date(event.starts_at).getTime() : null;

  // RSVP is closed if cancelled, or if we have a starts_at and it's in the past.
  const rsvpClosed =
    (event.status ?? '').toLowerCase() === 'cancelled' ||
    (startsAtMs !== null && !Number.isNaN(startsAtMs) && startsAtMs <= Date.now());

  const rsvpLabels: Record<string, string> = {
    going: 'Going',
    interested: 'Interested',
    not_going: "Can’t make it",
  };

  function rsvp(status: 'going' | 'interested' | 'not_going') {
    router.post(
      `/events/${event.slug}/rsvps`,
      { status },
      {
        preserveScroll: true,
        onStart: () => setProcessing(true),
        onFinish: () => setProcessing(false),
      }
    );
  }

  function clearRsvp() {
    router.delete(`/events/${event.slug}/rsvps`, {
      preserveScroll: true,
      onStart: () => setProcessing(true),
      onFinish: () => setProcessing(false),
    });
  }

  return (
    <>
      <Head title={event.title}>
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link
          href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
          rel="stylesheet"
        />
      </Head>

      <div className="min-h-screen bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
        <main className="mx-auto w-full max-w-6xl px-6 py-10">
          {/* Breadcrumb-ish top row */}
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex items-center gap-3">
              <Link
                href="/events"
                className="text-sm font-semibold text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white"
              >
                ← Events
              </Link>

              <span className="hidden text-xs text-black/40 dark:text-white/40 sm:block">•</span>

              <div className="hidden text-xs text-black/60 dark:text-white/60 sm:block">
                Action layer
              </div>
            </div>

            <div className="flex items-center gap-3">
              {statusPill(event.status)}
              <Link
                href="/"
                className="rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-medium shadow-sm hover:bg-black/5 dark:border-white/10 dark:bg-[#161615] dark:hover:bg-white/5"
              >
                Home
              </Link>
            </div>
          </div>

          {/* Main event card */}
          <section className="mt-6 rounded-3xl border border-black/10 bg-white p-8 shadow-sm dark:border-white/10 dark:bg-[#161615] lg:p-12">
            <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
              <div className="min-w-0">
                <h1 className="text-2xl font-semibold tracking-tight lg:text-4xl">{event.title}</h1>

                <div className="mt-3 flex flex-wrap items-center gap-3 text-sm text-black/60 dark:text-white/60">
                  <div className="rounded-2xl border border-black/10 bg-[#FDFDFC] px-3 py-2 dark:border-white/10 dark:bg-[#0f0f0f]">
                    <div className="text-xs font-semibold text-black/60 dark:text-white/60">Starts</div>
                    <div className="mt-1 font-medium text-black/70 dark:text-white/70">
                      {formatDate(event.starts_at)}
                    </div>
                  </div>

                  <div className="rounded-2xl border border-black/10 bg-[#FDFDFC] px-3 py-2 dark:border-white/10 dark:bg-[#0f0f0f]">
                    <div className="text-xs font-semibold text-black/60 dark:text-white/60">RSVPs</div>
                    <div className="mt-1 font-medium text-black/70 dark:text-white/70">
                      {event.rsvps_count ?? 0}
                    </div>
                  </div>
                </div>

                {event.description ? (
                  <p className="mt-6 whitespace-pre-wrap text-base leading-7 text-black/70 dark:text-white/70">
                    {event.description}
                  </p>
                ) : (
                  <p className="mt-6 text-base leading-7 text-black/60 dark:text-white/60">
                    No description yet.
                  </p>
                )}
              </div>

              {/* RSVP panel */}
              <div className="w-full lg:w-[360px]">
                <div className="rounded-3xl border border-black/10 bg-gradient-to-b from-[#fff7ed] to-white p-6 dark:border-white/10 dark:from-[#1b1208] dark:to-[#161615]">
                  <div className="text-sm font-semibold">RSVP</div>
                  <p className="mt-2 text-sm text-black/70 dark:text-white/70">
                    Tell others you’re coming. Participation is the point.
                  </p>

                  {!auth?.user ? (
                    <div className="mt-5 rounded-2xl border border-black/10 bg-white p-4 text-sm dark:border-white/10 dark:bg-[#0f0f0f]">
                      <div className="text-black/60 dark:text-white/60">
                        You’ll need an account to RSVP.
                      </div>
                      <div className="mt-3">
                        <Link
                          href={`/login?return_to=${encodeURIComponent(`/events/${event.slug}`)}`}
                          className="underline underline-offset-4 hover:text-black dark:hover:text-white"
                        >
                          Log in to RSVP
                        </Link>
                      </div>
                    </div>
                  ) : (
                    <div className="mt-5">
                      <div className="flex flex-wrap gap-2">
                        {(
                          [
                            ['going', 'Going'],
                            ['interested', 'Interested'],
                            ['not_going', "Can’t make it"],
                          ] as const
                        ).map(([value, label]) => {
                          const active = currentStatus === value;

                          return (
                            <button
                              key={value}
                              type="button"
                              onClick={() => rsvp(value)}
                              disabled={processing || rsvpClosed}
                              className={[
                                'rounded-xl border px-3 py-2 text-sm font-semibold shadow-sm transition-colors',
                                // when closed: greyed out, no hover, no pointer
                                rsvpClosed
                                  ? 'cursor-not-allowed border-black/10 bg-black/5 text-black/40 dark:border-white/10 dark:bg-white/5 dark:text-white/40'
                                  : 'border-black/10 hover:bg-black/5 dark:border-white/10 dark:hover:bg-white/5',
                                // state-specific background + text (soft tones) — only when not closed
                                !rsvpClosed && value === 'going' && active
                                  ? 'bg-emerald-100 text-emerald-800 border-emerald-300 hover:bg-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-700'
                                  : !rsvpClosed && value === 'not_going' && active
                                  ? 'bg-rose-100 text-rose-800 border-rose-300 hover:bg-rose-200 dark:bg-rose-900/40 dark:text-rose-300 dark:border-rose-700'
                                  : !rsvpClosed && value === 'interested' && active
                                  ? 'bg-amber-100 text-amber-800 border-amber-300 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-700'
                                  : !rsvpClosed
                                  ? 'bg-white text-black/70 dark:bg-[#161615] dark:text-white/70'
                                  : '',
                                processing ? 'opacity-60' : '',
                              ].join(' ')}
                            >
                              {label}
                            </button>
                          );
                        })}
                      </div>

                      {/* Closed message */}
                      {rsvpClosed && (
                        <div className="mt-3 text-center text-sm">
                          <Link
                            href="/events"
                            className="underline underline-offset-4 text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white"
                          >
                            Eyes to the horizon,
                          </Link>
                        </div>
                      )}

                      {currentStatus && !rsvpClosed && (
                        <button
                          type="button"
                          onClick={clearRsvp}
                          disabled={processing}
                          className={[
                            'mt-3 inline-flex items-center justify-center rounded-xl border px-3 py-2 text-xs font-semibold shadow-sm transition-colors',
                            'border-black/10 bg-white hover:bg-black/5',
                            'dark:border-white/10 dark:bg-[#161615] dark:hover:bg-white/5',
                            processing ? 'opacity-60' : '',
                          ].join(' ')}
                        >
                          Clear RSVP
                        </button>
                      )}
                    </div>
                  )}

                  <div className="mt-6 rounded-2xl border border-black/10 bg-white p-4 text-xs text-black/60 dark:border-white/10 dark:bg-[#0f0f0f] dark:text-white/60">
                    RSVP totals help people coordinate. Keep it honest.
                  </div>
                </div>
              </div>
            </div>
          </section>

          {/* Discussion (stub) */}
          <section className="mt-10 rounded-3xl border border-black/10 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#161615] lg:p-8">
            <div className="flex items-end justify-between gap-6">
              <div>
                <h2 className="text-2xl font-semibold tracking-tight">Discussion</h2>
                <p className="mt-2 text-sm text-black/70 dark:text-white/70">
                  Event threads are coming soon — a calm place for logistics, questions, and coordination.
                </p>
              </div>
              <div className="hidden text-xs text-black/60 dark:text-white/60 lg:block">
                Clarity → Coordination → Action
              </div>
            </div>

            <div className="mt-6 rounded-2xl border border-black/10 bg-[#FDFDFC] p-4 dark:border-white/10 dark:bg-[#0f0f0f]">
              {!auth?.user ? (
                <div className="text-sm text-black/60 dark:text-white/60">
                  <div className="font-semibold text-black/70 dark:text-white/70">
                    Log in to join (soon)
                  </div>
                  <div className="mt-1">
                    Discussion will be available after launch. For now, RSVP is the best signal.
                  </div>
                  <div className="mt-3">
                    <Link
                      className="underline underline-offset-4 hover:text-black dark:hover:text-white"
                      href="/login"
                    >
                      Log in
                    </Link>
                  </div>
                </div>
              ) : (
                <div className="text-sm text-black/60 dark:text-white/60">
                  <div className="font-semibold text-black/70 dark:text-white/70">
                    Posting is coming soon
                  </div>
                  <div className="mt-3">
                    <label className="text-xs font-semibold text-black/60 dark:text-white/60">
                      Add a comment
                    </label>
                    <textarea
                      disabled
                      rows={3}
                      placeholder="Comments will live here soon (logistics, questions, coordination)."
                      className="mt-2 w-full resize-none rounded-2xl border border-black/10 bg-white p-3 text-sm text-black/70 shadow-sm placeholder:text-black/40 disabled:cursor-not-allowed disabled:opacity-70 dark:border-white/10 dark:bg-[#161615] dark:text-white/70 dark:placeholder:text-white/40"
                    />
                    <div className="mt-3 flex items-center justify-between">
                      <div className="text-xs text-black/50 dark:text-white/50">
                        Be kind. Keep it practical. No dunking.
                      </div>
                      <button
                        type="button"
                        disabled
                        className="rounded-xl bg-[#1b1b18] px-4 py-2 text-sm font-semibold text-white opacity-60 dark:bg-[#eeeeec] dark:text-[#1b1b18]"
                      >
                        Post (soon)
                      </button>
                    </div>
                  </div>
                </div>
              )}
            </div>

            <div className="mt-6 rounded-2xl border border-black/10 bg-white p-4 text-xs text-black/60 dark:border-white/10 dark:bg-[#161615] dark:text-white/60">
              MVP approach: ship the UI placeholder now, then add comments once moderation + reporting rules are ready.
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
              <a className="underline underline-offset-4 hover:text-black dark:hover:text-white" href="/forums">
                Forums
              </a>
            </div>
            <div className="max-w-3xl">
              Events are the action layer: show up, coordinate, and make participation visible.
            </div>
          </footer>
        </main>
      </div>
    </>
  );
}



