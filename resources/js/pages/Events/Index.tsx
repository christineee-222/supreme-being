import React from 'react';
import { Link } from '@inertiajs/react';

interface EventSummary {
  id: string;
  status: string | null;
  starts_at: string | null;
}

interface Props {
  events: EventSummary[];
}

function formatDate(value: string | null) {
  if (!value) return 'TBD';
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return value; // fallback if parsing fails
  return d.toLocaleString();
}

export default function Index({ events }: Props) {
  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold">Events</h1>

      {events.length === 0 ? (
        <div className="rounded border p-3 text-sm text-gray-600">
          No events yet.
        </div>
      ) : (
        <div className="space-y-2">
          {events.map((event) => (
            <div key={event.id} className="rounded border p-3">
              <div className="font-semibold">
                <Link className="underline" href={`/events/${event.id}`}>
                  View event
                </Link>
              </div>

              <div className="text-sm text-gray-600">
                Status: {event.status ?? 'scheduled'}
              </div>

              <div className="text-sm text-gray-600">
                Starts at: {formatDate(event.starts_at)}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}


