export async function lookupBallot(address: string) {
  const res = await fetch('/api/v1/ballot/lookup', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    body: JSON.stringify({ address }),
  });

  if (!res.ok) {
    throw new Error('Ballot lookup failed');
  }

  return res.json();
}
