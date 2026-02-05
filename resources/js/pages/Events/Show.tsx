import React from 'react';

interface User {
    id: number;
    name: string;
}

interface EventRsvp {
    id: number;
    status: 'going' | 'interested' | 'not_going';
    user_id: number;
    user?: User;
}

interface Event {
    id: number;
    status: string | null;
    starts_at: string | null;
    rsvps?: EventRsvp[];
}

interface Props {
    event: Event;
    userRsvp: EventRsvp | null;
}

export default function Show({ event, userRsvp }: Props) {
    return (
        <div className="space-y-4">
            <h1 className="text-2xl font-bold">
                Event #{event.id}
            </h1>

            <div>
                <strong>Status:</strong> {event.status ?? 'scheduled'}
            </div>

            <div>
                <strong>Starts at:</strong> {event.starts_at}
            </div>

            {userRsvp ? (
                <div className="p-3 border rounded">
                    <strong>Your RSVP:</strong> {userRsvp.status}
                </div>
            ) : (
                <div className="italic text-gray-600">
                    You have not RSVP’d yet.
                </div>
            )}
        </div>
    );
}


