import { Head } from '@inertiajs/react';

type Props = {
  feature?: string;
  tagline?: string;
  description?: string;
};

export default function ComingSoon({ feature, tagline, description }: Props) {
  return (
    <>
      <Head title={`${feature ?? 'Feature'} – Coming Soon`} />

      <div className="min-h-screen bg-[#FDFDFC] px-6 py-16 text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
        <div className="mx-auto max-w-3xl">
          <div className="rounded-3xl border border-black/10 bg-white p-8 shadow-sm dark:border-white/10 dark:bg-[#161615]">
            <h1 className="text-3xl font-semibold tracking-tight">
              {feature ?? 'Feature'} — coming soon
            </h1>

            {tagline && (
              <p className="mt-3 text-lg text-black/70 dark:text-white/70">
                {tagline}
              </p>
            )}

            <p className="mt-6 text-sm leading-6 text-black/70 dark:text-white/70">
              {description ??
                "This part of Assembly Required is actively being built. The goal isn’t just another feature — it’s a space where community voice, civic understanding, and collective action can grow together."}
            </p>

            <div className="mt-8 rounded-2xl border border-black/10 bg-[#FDFDFC] p-4 text-xs text-black/60 dark:border-white/10 dark:bg-[#0f0f0f] dark:text-white/60">
              Built in public. Feedback, ideas, and curiosity are always welcome.
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
