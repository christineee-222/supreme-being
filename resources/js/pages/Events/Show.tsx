import { Head } from '@inertiajs/react';

interface Event {
  id: number;
  title?: string;
  starts_at?: string;
}

interface EventRsvp {
  id: number;
  status: string;
}

interface Props {
  event: Event;
  userRsvp: EventRsvp | null;
}

export default function Show({ event, userRsvp }: Props) {
  return (
    <>
      <Head title="Event" />

      <div className="max-w-3xl mx-auto py-8 space-y-6">
        <h1 className="text-2xl font-bold">
          Event #{event.id}
        </h1>

        {userRsvp ? (
          <p className="text-sm text-green-600">
            Your RSVP status: {userRsvp.status}
          </p>
        ) : (
          <p className="text-sm text-gray-500">
            You have not RSVP’d yet.
          </p>
        )}
      </div>
    </>
  );
}
