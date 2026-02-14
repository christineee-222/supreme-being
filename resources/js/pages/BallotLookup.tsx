import { useState } from 'react';
import { lookupBallot } from '@/lib/ballot';

export default function BallotLookup() {
  const [address, setAddress] = useState('');
  const [ballot, setBallot] = useState<any>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  async function handleLookup(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const data = await lookupBallot(address);
      setBallot(data);
    } catch {
      setError('Lookup failed');
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="max-w-xl mx-auto p-6">
      <h1 className="text-xl font-semibold mb-4">Ballot Lookup</h1>

      <form onSubmit={handleLookup} className="space-y-3">
        <input
          value={address}
          onChange={(e) => setAddress(e.target.value)}
          placeholder="Enter address"
          className="border p-2 w-full rounded"
        />

        <button
          type="submit"
          className="bg-blue-600 text-white px-4 py-2 rounded"
          disabled={loading}
        >
          {loading ? 'Looking up…' : 'Lookup'}
        </button>
      </form>

      {error && <p className="text-red-500 mt-4">{error}</p>}

      {ballot && (
        <pre className="mt-6 bg-gray-100 p-4 rounded overflow-auto text-sm">
          {JSON.stringify(ballot, null, 2)}
        </pre>
      )}
    </div>
  );
}
