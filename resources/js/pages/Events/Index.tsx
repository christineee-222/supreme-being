// resources/js/pages/Events/Index.tsx
import React from 'react';
import { Head, Link } from '@inertiajs/react';

interface EventSummary {
  id: string;
  slug: string;
  title: string;
  status: string | null;
  starts_at: string | null;
}

interface Props {
  events: EventSummary[];
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

export default function Index({ events }: Props) {
  return (
    <>
      <Head title="Events">
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
      </Head>

      <div className="min-h-screen bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
        <main className="mx-auto w-full max-w-6xl px-6 py-10">
          {/* Page header (simple, not a hero) */}
          <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h1 className="text-2xl font-semibold tracking-tight lg:text-3xl">Events</h1>
              <p className="mt-2 max-w-2xl text-sm text-black/70 dark:text-white/70">
                Gatherings and actions — simple lists, clear details, no feed-brain.
              </p>
            </div>

            <div className="flex gap-3">
              <Link
                href="/events/create"
                className="inline-flex items-center justify-center rounded-xl bg-[#1b1b18] px-5 py-2.5 text-sm font-semibold text-white hover:bg-black dark:bg-[#eeeeec] dark:text-[#1b1b18] dark:hover:bg-white"
              >
                Create
              </Link>
              <Link
                href="/"
                className="inline-flex items-center justify-center rounded-xl border border-black/10 bg-white px-5 py-2.5 text-sm font-semibold hover:bg-black/5 dark:border-white/10 dark:bg-[#161615] dark:hover:bg-white/5"
              >
                Home
              </Link>
            </div>
          </div>

          {/* List container */}
          <section className="mt-8 rounded-3xl border border-black/10 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#161615] lg:p-8">
            {events.length === 0 ? (
              <div className="rounded-2xl border border-black/10 bg-[#FDFDFC] p-4 text-sm text-black/60 dark:border-white/10 dark:bg-[#0f0f0f] dark:text-white/60">
                No events yet.
              </div>
            ) : (
              <div className="space-y-3">
                {events.map((event) => (
                  <div
                    key={event.id}
                    className="rounded-2xl border border-black/10 bg-[#FDFDFC] p-4 dark:border-white/10 dark:bg-[#0f0f0f]"
                  >
                    <div className="flex items-start justify-between gap-4">
                      <div className="min-w-0">
                        <Link
                          className="block truncate text-base font-semibold underline decoration-black/20 underline-offset-4 hover:decoration-black/40 dark:decoration-white/20 dark:hover:decoration-white/40"
                          href={`/events/${event.slug}`}
                        >
                          {event.title}
                        </Link>

                        <div className="mt-2 text-sm text-black/60 dark:text-white/60">
                          Starts at:{' '}
                          <span className="font-medium text-black/70 dark:text-white/70">
                            {formatDate(event.starts_at)}
                          </span>
                        </div>
                      </div>

                      <div className="shrink-0">{statusPill(event.status)}</div>
                    </div>

                    <div className="mt-4">
                      <Link
                        href={`/events/${event.slug}`}
                        className="text-sm font-semibold text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white"
                      >
                        Open →
                      </Link>
                    </div>
                  </div>
                ))}
              </div>
            )}

            <div className="mt-6 rounded-2xl border border-black/10 bg-white p-4 text-xs text-black/60 dark:border-white/10 dark:bg-[#161615] dark:text-white/60">
              MVP note: keep events calm and searchable — the goal is participation, not attention.
            </div>
          </section>
        </main>
      </div>
    </>
  );
}


