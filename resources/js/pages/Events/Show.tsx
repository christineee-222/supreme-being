import React from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
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
  status: string | null;
  starts_at: string | null;
  rsvps_count?: number;
  rsvps?: EventRsvp[];
}

interface Props {
  event: Event;
  userRsvp: EventRsvp | null;
}

export default function Show({ event, userRsvp }: Props) {
  const { auth } = usePage<SharedData>().props;

  const { setData, post, processing } = useForm<{
    status: 'going' | 'interested' | 'not_going';
  }>({
    status: 'going',
  });

  function rsvp(status: 'going' | 'interested' | 'not_going') {
    setData('status', status);

    post(`/events/${event.id}/rsvps`, {
      preserveScroll: true,
      // optional niceties:
      // preserveState: true,
      // onSuccess: () => {},
    });
  }

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold">Event #{event.id}</h1>

      <div>
        <strong>Status:</strong> {event.status ?? 'scheduled'}
      </div>

      <div>
        <strong>Starts at:</strong> {event.starts_at}
      </div>

      <div>
        <strong>RSVPs:</strong> {event.rsvps_count ?? 0}
      </div>

      <div className="pt-4">
        <h2 className="text-lg font-semibold">RSVP</h2>

        {!auth?.user ? (
          <div className="mt-2 rounded border p-3 text-sm">
            <div className="text-gray-600">You’ll need an account to RSVP.</div>
            <div className="mt-2">
              <Link className="underline" href="/login">
                Log in to RSVP
              </Link>
            </div>
          </div>
        ) : userRsvp ? (
          <div className="mt-2 rounded border p-3 text-sm">
            <strong>Your RSVP:</strong> {userRsvp.status}
          </div>
        ) : (
          <div className="mt-2 flex flex-wrap gap-2">
            <button
              type="button"
              onClick={() => rsvp('going')}
              disabled={processing}
              className="rounded border px-3 py-2 text-sm"
            >
              Going
            </button>

            <button
              type="button"
              onClick={() => rsvp('interested')}
              disabled={processing}
              className="rounded border px-3 py-2 text-sm"
            >
              Interested
            </button>

            <button
              type="button"
              onClick={() => rsvp('not_going')}
              disabled={processing}
              className="rounded border px-3 py-2 text-sm"
            >
              Can’t make it
            </button>
          </div>
        )}
      </div>
    </div>
  );
}




